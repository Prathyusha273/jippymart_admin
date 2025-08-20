const { onDocumentWritten, onDocumentUpdated } = require("firebase-functions/v2/firestore");
const functions = require("firebase-functions");
const admin = require("firebase-admin");

const firestore = admin.firestore();

const RADIUS_STEPS = [1, 2, 3, 5, 10, 20]; // in km

// --- 1️⃣ Assign Order to Driver (FCFS Atomic Callable) ---
exports.assignOrderToDriverFCFS = functions.https.onCall(async (data, context) => {
  const { orderId, driverId } = data;
  const orderRef = firestore.collection('restaurant_orders').doc(orderId);

  console.log(`[${new Date().toISOString()}] assignOrderToDriverFCFS called for orderId=${orderId}, driverId=${driverId}`);

  const result = await firestore.runTransaction(async (transaction) => {
    const orderDoc = await transaction.get(orderRef);
    if (!orderDoc.exists) {
      console.log(`[${new Date().toISOString()}] Order not found: ${orderId}`);
      throw new functions.https.HttpsError('not-found', 'Order not found');
    }

    const orderData = orderDoc.data();
    console.log(`[${new Date().toISOString()}] Order status before transaction: ${orderData.status}, driverID: ${orderData.driverID}`);

    if (
      orderData.status === "Driver Pending" &&
      (!orderData.driverID || orderData.driverID === "")
    ) {
      transaction.update(orderRef, {
        driverID: driverId,
        status: "Driver Accepted"
      });
      console.log(`[${new Date().toISOString()}] Transaction: Assigning driverId=${driverId} to orderId=${orderId}`);
      return { success: true };
    } else if (orderData.driverID) {
      console.log(`[${new Date().toISOString()}] Order already assigned to driverID=${orderData.driverID}`);
      return { success: false, reason: "Order already assigned" };
    } else {
      console.log(`[${new Date().toISOString()}] Order not in Driver Pending status: ${orderData.status}`);
      return { success: false, reason: "Order not in Driver Pending status" };
    }
  });

  if (result.success) {
    console.log(`[${new Date().toISOString()}] Transaction committed for orderId=${orderId}, starting cleanup...`);
    await cleanUpFromOtherDrivers(orderId, driverId);
    console.log(`[${new Date().toISOString()}] Cleanup completed for orderId=${orderId}`);
  } else {
    console.log(`[${new Date().toISOString()}] Transaction result:`, result);
  }
  return result;
});

// --- 2️⃣ Callable: Driver Rejects Order ---
exports.rejectOrderByDriver = functions.https.onCall(async (data, context) => {
  const { orderId } = data;
  const driverId = context.auth?.uid;

  if (!driverId) {
    throw new functions.https.HttpsError('unauthenticated', 'Driver must be authenticated');
  }

  const orderRef = firestore.collection('restaurant_orders').doc(orderId);

  await orderRef.update({
    rejectedByDrivers: admin.firestore.FieldValue.arrayUnion(driverId)
  });

  await firestore.collection('users').doc(driverId).update({
    orderRequestData: admin.firestore.FieldValue.arrayRemove(orderId)
  });

  console.log(`Driver ${driverId} rejected order ${orderId}`);
  return { success: true };
});

// --- 3️⃣ Order Dispatcher (Broadcast to Nearby Drivers) ---
exports.dispatch = onDocumentWritten("restaurant_orders/{orderID}", async (event) => {
  const change = event;
  const orderData = change.data.after?.data();
  const beforeData = change.data.before?.data();

  if (!orderData) return;

  if (beforeData && orderData) {
    const keysChanged = Object.keys(orderData).filter(
      key => JSON.stringify(orderData[key]) !== JSON.stringify(beforeData[key])
    );
    if (keysChanged.length === 1 && keysChanged.includes('orderAutoCancelAt')) {
      console.log("Auto cancel timestamp changed — skipping dispatch.");
      return;
    }
  }

  if (["Order Cancelled", "Order Placed"].includes(orderData.status)) return;
  if (orderData.takeAway === true) return;

  if (orderData.status === "Order Accepted" || orderData.status === "Driver Pending" || orderData.status === "Driver Rejected") {
    const rejectedByDrivers = orderData.rejectedByDrivers || [];
    const orderId = change.data.after.id;

    const driverNearByData = await getDriverNearByData();
    let minimumDepositToRideAccept = parseInt(driverNearByData?.minimumDepositToRideAccept || 0);
    let orderAcceptRejectDuration = parseInt(driverNearByData?.driverOrderAcceptRejectDuration || 0);
    let orderAutoCancelDuration = parseInt(driverNearByData?.orderAutoCancelDuration || 0);

    const vendor = orderData.vendor;
    if (!vendor?.latitude || !vendor?.longitude || !orderData.address?.location) {
      console.log("Vendor or address missing.");
      return;
    }

    const zone_id = await getUserZoneId(orderData.address.location.longitude, orderData.address.location.latitude);

    for (let radius of RADIUS_STEPS) {
      const snapshot = await firestore
        .collection("users")
        .where('role', '==', "driver")
        .where('isActive', '==', true)
        .where('wallet_amount', '>=', minimumDepositToRideAccept)
        .get();

      let foundDrivers = [];

      snapshot.docs.forEach(doc => {
        const driver = doc.data();
        if (
          driver.fcmToken &&
          driver.zoneId === zone_id &&
          driver.location &&
          !rejectedByDrivers.includes(doc.id)
        ) {
          const distance = distanceRadius(
            driver.location.latitude,
            driver.location.longitude,
            vendor.latitude,
            vendor.longitude
          );
          if (distance <= radius && !(driver.orderRequestData || []).includes(orderId)) {
            foundDrivers.push({ id: doc.id, fcmToken: driver.fcmToken });
          }
        }
      });

      if (foundDrivers.length > 0) {
        const batch = firestore.batch();
        for (const driver of foundDrivers) {
          const ref = firestore.collection('users').doc(driver.id);
          batch.update(ref, {
            orderRequestData: admin.firestore.FieldValue.arrayUnion(orderId)
          });

          const notificationBody = `You have a new order within ${radius} km! Accept in ${Math.floor(orderAcceptRejectDuration / 60)}:${(orderAcceptRejectDuration % 60 || '00')}`;
          const message = {
            notification: {
              title: 'New order received',
              body: notificationBody
            },
            token: driver.fcmToken
          };

          admin.messaging().send(message).catch(err => console.log("❌ FCM Error:", err));
        }

        await batch.commit();
        await change.data.after.ref.set({ status: "Driver Pending" }, { merge: true });
        console.log(`Order ${orderId} broadcast to ${foundDrivers.length} drivers within ${radius} km.`);
        return;
      }
    }

    const futureTime = admin.firestore.Timestamp.fromDate(new Date(Date.now() + orderAutoCancelDuration * 60000));
    await change.data.after.ref.set({ orderAutoCancelAt: futureTime }, { merge: true });
    console.log(`No drivers found for order ${orderId}. Auto cancel scheduled.`);
  }

  if (orderData.status === "Driver Accepted" && (!beforeData || beforeData.status !== "Driver Accepted")) {
    await change.data.after.ref.set({ status: "Order Shipped" }, { merge: true });
    console.log(`Order #${change.params.orderID} shipped.`);
  }

  return;
});

// --- 4️⃣ Clean-up Trigger: Remove order from other drivers ---
exports.cleanUpOrderRequestData = onDocumentUpdated("restaurant_orders/{orderId}", async (event) => {
  const before = event.data.before.data();
  const after = event.data.after.data();
  const orderId = event.params.orderId;

  const terminalStatuses = ["Order Completed", "Order Cancelled", "Driver Accepted"];

  if (before.status !== after.status && terminalStatuses.includes(after.status)) {
    const assignedDriverId = after.driverID;
    console.log(`[${new Date().toISOString()}] cleanUpOrderRequestData triggered for orderId=${orderId}, new status=${after.status}, assignedDriverId=${assignedDriverId}`);

    const driversSnap = await firestore.collection("users")
      .where('orderRequestData', 'array-contains', orderId)
      .get();

    const batch = firestore.batch();
    driversSnap.forEach(doc => {
      if (!assignedDriverId || doc.id !== assignedDriverId) {
        batch.update(doc.ref, {
          orderRequestData: admin.firestore.FieldValue.arrayRemove(orderId)
        });
        console.log(`[${new Date().toISOString()}] Trigger: Removing orderId=${orderId} from driverId=${doc.id}`);
      }
    });

    try {
      await batch.commit();
      console.log(`[${new Date().toISOString()}] Trigger: Batch commit successful for orderId=${orderId}`);
    } catch (err) {
      console.error(`[${new Date().toISOString()}] Trigger: Batch commit failed for orderId=${orderId}:`, err);
    }
    console.log(`[${new Date().toISOString()}] Order ${orderId} cleaned from drivers (status: ${after.status}).`);
  }
});

// --- 🔧 Helper Functions ---
const distanceRadius = (lat1, lon1, lat2, lon2) => {
  if ((lat1 === lat2) && (lon1 === lon2)) return 0;
  const radlat1 = Math.PI * lat1 / 180;
  const radlat2 = Math.PI * lat2 / 180;
  const theta = lon1 - lon2;
  const radtheta = Math.PI * theta / 180;
  let dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
  if (dist > 1) dist = 1;
  dist = Math.acos(dist);
  dist = dist * 180 / Math.PI * 60 * 1.1515 * 1.60934;
  return dist;
};

async function getDriverNearByData() {
  const snapshot = await firestore.collection("settings").doc("DriverNearBy").get();
  return snapshot.data();
}

async function getUserZoneId(lng, lat) {
  const snapshots = await firestore.collection("zone").where("publish", "==", true).get();
  for (const doc of snapshots.docs) {
    const zone = doc.data();
    const x = zone.area.map(p => p.longitude);
    const y = zone.area.map(p => p.latitude);
    if (is_in_polygon(x.length - 1, x, y, lng, lat)) {
      return doc.id;
    }
  }
  return null;
}

function is_in_polygon(n, x, y, px, py) {
  let inside = false;
  for (let i = 0, j = n - 1; i < n; j = i++) {
    if (((y[i] > py) !== (y[j] > py)) &&
      (px < (x[j] - x[i]) * (py - y[i]) / (y[j] - y[i]) + x[i])) {
      inside = !inside;
    }
  }
  return inside;
}

// --- 🔧 Helper: Clean order from other drivers immediately ---
async function cleanUpFromOtherDrivers(orderId, acceptedDriverId) {
  const driversSnap = await firestore.collection("users")
    .where('orderRequestData', 'array-contains', orderId).get();

  const driverIds = driversSnap.docs.map(doc => doc.id);
  console.log(`[${new Date().toISOString()}] cleanUpFromOtherDrivers for orderId=${orderId}, acceptedDriverId=${acceptedDriverId}, drivers to clean: ${JSON.stringify(driverIds)}`);

  const batch = firestore.batch();
  driversSnap.forEach(doc => {
    if (doc.id !== acceptedDriverId) {
      batch.update(doc.ref, {
        orderRequestData: admin.firestore.FieldValue.arrayRemove(orderId)
      });
      console.log(`[${new Date().toISOString()}] Removing orderId=${orderId} from driverId=${doc.id}`);
    }
  });

  try {
    await batch.commit();
    console.log(`[${new Date().toISOString()}] Batch commit successful for orderId=${orderId}`);
  } catch (err) {
    console.error(`[${new Date().toISOString()}] Batch commit failed for orderId=${orderId}:`, err);
  }
}

// --- 🔧 Script: Set orderAutoCancelDuration to 5 minutes ---
if (require.main === module) {
  (async () => {
    try {
      await firestore.collection('settings').doc('DriverNearBy').set({
        orderAutoCancelDuration: 5
      }, { merge: true });
      console.log('orderAutoCancelDuration set to 5 minutes');
      process.exit(0);
    } catch (err) {
      console.error('Failed to set orderAutoCancelDuration:', err);
      process.exit(1);
    }
  })();
}