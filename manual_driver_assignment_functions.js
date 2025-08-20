const { onDocumentWritten, onDocumentUpdated } = require("firebase-functions/v2/firestore");
const functions = require("firebase-functions");
const admin = require("firebase-admin");

const firestore = admin.firestore();

// --- 🎯 Enhanced Manual Driver Assignment Function ---
exports.manualAssignDriverToOrder = functions.https.onCall(async (data, context) => {
  const { orderId, driverId, assignedBy, reason } = data;
  
  // Validate admin authentication
  if (!context.auth) {
    throw new functions.https.HttpsError('unauthenticated', 'Admin must be authenticated');
  }

  console.log(`[${new Date().toISOString()}] Manual assignment: orderId=${orderId}, driverId=${driverId}, assignedBy=${assignedBy}`);

  try {
    // Get order and driver data
    const [orderDoc, driverDoc] = await Promise.all([
      firestore.collection('restaurant_orders').doc(orderId).get(),
      firestore.collection('users').doc(driverId).get()
    ]);

    if (!orderDoc.exists) {
      throw new functions.https.HttpsError('not-found', 'Order not found');
    }

    if (!driverDoc.exists) {
      throw new functions.https.HttpsError('not-found', 'Driver not found');
    }

    const orderData = orderDoc.data();
    const driverData = driverDoc.data();

    // Validate driver role
    if (driverData.role !== 'driver') {
      throw new functions.https.HttpsError('invalid-argument', 'Selected user is not a driver');
    }

    // Validate driver is active
    if (!driverData.isActive) {
      throw new functions.https.HttpsError('invalid-argument', 'Driver is not active');
    }

    // Check if order is eligible for manual assignment
    const eligibleStatuses = ['Order Accepted', 'Driver Pending', 'Driver Rejected'];
    if (!eligibleStatuses.includes(orderData.status)) {
      throw new functions.https.HttpsError('failed-precondition', `Order status '${orderData.status}' is not eligible for manual assignment`);
    }

    // Check if order is takeaway
    if (orderData.takeAway) {
      throw new functions.https.HttpsError('failed-precondition', 'Takeaway orders do not require driver assignment');
    }

    // Use transaction for atomic update
    const result = await firestore.runTransaction(async (transaction) => {
      const currentOrderDoc = await transaction.get(firestore.collection('restaurant_orders').doc(orderId));
      const currentOrderData = currentOrderDoc.data();

      // Double-check status hasn't changed
      if (!eligibleStatuses.includes(currentOrderData.status)) {
        throw new functions.https.HttpsError('failed-precondition', 'Order status changed during assignment');
      }

      // Update order with driver information
      transaction.update(firestore.collection('restaurant_orders').doc(orderId), {
        driverID: driverId,
        driver: {
          id: driverId,
          firstName: driverData.firstName || '',
          lastName: driverData.lastName || '',
          email: driverData.email || '',
          phoneNumber: driverData.phoneNumber || '',
          carName: driverData.carName || '',
          carNumber: driverData.carNumber || '',
          zoneId: driverData.zoneId || ''
        },
        status: 'Driver Pending',
        manualAssignment: {
          assignedBy: assignedBy || context.auth.uid,
          assignedAt: admin.firestore.FieldValue.serverTimestamp(),
          reason: reason || 'Manual assignment by admin',
          adminId: context.auth.uid
        }
      });

      return { success: true };
    });

    // Clean up order from other drivers
    await cleanUpFromOtherDrivers(orderId, driverId);

    // Send notification to assigned driver
    if (driverData.fcmToken) {
      const message = {
        notification: {
          title: 'Order Assigned',
          body: `You have been manually assigned to order #${orderId}`
        },
        data: {
          orderId: orderId,
          type: 'manual_assignment'
        },
        token: driverData.fcmToken
      };

      admin.messaging().send(message).catch(err => 
        console.log(`❌ FCM Error for driver ${driverId}:`, err)
      );
    }

    // Log the manual assignment
    await firestore.collection('admin_activity_logs').add({
      action: 'manual_driver_assignment',
      orderId: orderId,
      driverId: driverId,
      adminId: context.auth.uid,
      reason: reason || 'Manual assignment by admin',
      timestamp: admin.firestore.FieldValue.serverTimestamp(),
      orderStatus: orderData.status,
      driverName: `${driverData.firstName} ${driverData.lastName}`
    });

    console.log(`✅ Manual assignment successful: orderId=${orderId}, driverId=${driverId}`);
    return { success: true, message: 'Driver assigned successfully' };

  } catch (error) {
    console.error(`❌ Manual assignment failed: orderId=${orderId}, driverId=${driverId}`, error);
    throw new functions.https.HttpsError('internal', 'Failed to assign driver to order');
  }
});

// --- 🎯 Manual Driver Removal Function ---
exports.manualRemoveDriverFromOrder = functions.https.onCall(async (data, context) => {
  const { orderId, reason } = data;
  
  if (!context.auth) {
    throw new functions.https.HttpsError('unauthenticated', 'Admin must be authenticated');
  }

  console.log(`[${new Date().toISOString()}] Manual driver removal: orderId=${orderId}`);

  try {
    const orderDoc = await firestore.collection('restaurant_orders').doc(orderId).get();
    
    if (!orderDoc.exists) {
      throw new functions.https.HttpsError('not-found', 'Order not found');
    }

    const orderData = orderDoc.data();
    const previousDriverId = orderData.driverID;

    // Use transaction for atomic update
    await firestore.runTransaction(async (transaction) => {
      const currentOrderDoc = await transaction.get(firestore.collection('restaurant_orders').doc(orderId));
      const currentOrderData = currentOrderDoc.data();

      // Only allow removal if order is in appropriate status
      const removableStatuses = ['Driver Pending', 'Driver Rejected'];
      if (!removableStatuses.includes(currentOrderData.status)) {
        throw new functions.https.HttpsError('failed-precondition', 'Cannot remove driver from order in current status');
      }

      transaction.update(firestore.collection('restaurant_orders').doc(orderId), {
        driverID: '',
        driver: null,
        status: 'Order Accepted',
        manualAssignment: admin.firestore.FieldValue.delete(),
        driverRemoval: {
          removedBy: context.auth.uid,
          removedAt: admin.firestore.FieldValue.serverTimestamp(),
          reason: reason || 'Driver removed by admin'
        }
      });
    });

    // Log the driver removal
    await firestore.collection('admin_activity_logs').add({
      action: 'manual_driver_removal',
      orderId: orderId,
      previousDriverId: previousDriverId,
      adminId: context.auth.uid,
      reason: reason || 'Driver removed by admin',
      timestamp: admin.firestore.FieldValue.serverTimestamp(),
      orderStatus: 'Order Accepted'
    });

    console.log(`✅ Manual driver removal successful: orderId=${orderId}`);
    return { success: true, message: 'Driver removed successfully' };

  } catch (error) {
    console.error(`❌ Manual driver removal failed: orderId=${orderId}`, error);
    throw new functions.https.HttpsError('internal', 'Failed to remove driver from order');
  }
});

// --- 🎯 Get Available Drivers for Manual Assignment ---
exports.getAvailableDriversForOrder = functions.https.onCall(async (data, context) => {
  const { orderId, zoneId } = data;
  
  if (!context.auth) {
    throw new functions.https.HttpsError('unauthenticated', 'Admin must be authenticated');
  }

  console.log(`[${new Date().toISOString()}] Getting available drivers: orderId=${orderId}, zoneId=${zoneId}`);

  try {
    // Get order details
    const orderDoc = await firestore.collection('restaurant_orders').doc(orderId).get();
    if (!orderDoc.exists) {
      throw new functions.https.HttpsError('not-found', 'Order not found');
    }

    const orderData = orderDoc.data();
    
    // Build query for available drivers
    let driversQuery = firestore.collection('users')
      .where('role', '==', 'driver')
      .where('isActive', '==', true);

    // Add zone filter if provided
    if (zoneId) {
      driversQuery = driversQuery.where('zoneId', '==', zoneId);
    }

    const driversSnapshot = await driversQuery.get();
    
    const availableDrivers = [];
    const rejectedDrivers = orderData.rejectedByDrivers || [];

    driversSnapshot.docs.forEach(doc => {
      const driverData = doc.data();
      
      // Skip drivers who have already rejected this order
      if (rejectedDrivers.includes(doc.id)) {
        return;
      }

      // Skip drivers who already have this order in their request data
      if (driverData.orderRequestData && driverData.orderRequestData.includes(orderId)) {
        return;
      }

      availableDrivers.push({
        id: doc.id,
        firstName: driverData.firstName || '',
        lastName: driverData.lastName || '',
        email: driverData.email || '',
        phoneNumber: driverData.phoneNumber || '',
        carName: driverData.carName || '',
        carNumber: driverData.carNumber || '',
        zoneId: driverData.zoneId || '',
        wallet_amount: driverData.wallet_amount || 0,
        location: driverData.location || null,
        isOnline: driverData.isOnline || false,
        lastSeen: driverData.lastSeen || null
      });
    });

    // Sort by relevance (online status, wallet amount, etc.)
    availableDrivers.sort((a, b) => {
      // First priority: online status
      if (a.isOnline && !b.isOnline) return -1;
      if (!a.isOnline && b.isOnline) return 1;
      
      // Second priority: wallet amount
      if (a.wallet_amount > b.wallet_amount) return -1;
      if (a.wallet_amount < b.wallet_amount) return 1;
      
      // Third priority: last seen (more recent first)
      if (a.lastSeen && b.lastSeen) {
        return b.lastSeen.toDate() - a.lastSeen.toDate();
      }
      
      return 0;
    });

    console.log(`✅ Found ${availableDrivers.length} available drivers for orderId=${orderId}`);
    return { 
      success: true, 
      drivers: availableDrivers,
      total: availableDrivers.length
    };

  } catch (error) {
    console.error(`❌ Failed to get available drivers: orderId=${orderId}`, error);
    throw new functions.https.HttpsError('internal', 'Failed to get available drivers');
  }
});

// --- 🔧 Helper: Clean order from other drivers ---
async function cleanUpFromOtherDrivers(orderId, acceptedDriverId) {
  const driversSnap = await firestore.collection("users")
    .where('orderRequestData', 'array-contains', orderId)
    .get();

  const batch = firestore.batch();
  driversSnap.forEach(doc => {
    if (doc.id !== acceptedDriverId) {
      batch.update(doc.ref, {
        orderRequestData: admin.firestore.FieldValue.arrayRemove(orderId)
      });
    }
  });

  try {
    await batch.commit();
    console.log(`✅ Cleaned up order ${orderId} from other drivers`);
  } catch (err) {
    console.error(`❌ Failed to clean up order ${orderId}:`, err);
  }
}

module.exports = {
  manualAssignDriverToOrder,
  manualRemoveDriverFromOrder,
  getAvailableDriversForOrder
};



