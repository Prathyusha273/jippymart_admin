const { onDocumentCreated } = require("firebase-functions/v2/firestore");
const functions = require("firebase-functions");
const admin = require("firebase-admin");

const firestore = admin.firestore();

// 🔥 **Coupon Usage Limit Tracking Function**
// Triggered when a new order is created in restaurant_orders collection
exports.trackCouponUsage = onDocumentCreated("restaurant_orders/{orderId}", async (event) => {
  const orderId = event.params.orderId;
  const orderData = event.data.data();
  
  console.log(`[${new Date().toISOString()}] 🎫 Coupon usage tracking triggered for order: ${orderId}`);
  
  try {
    // Check if order has a coupon applied
    const couponId = orderData.couponId;
    const couponCode = orderData.couponCode;
    const userId = orderData.authorID;
    
    if (!couponId && !couponCode) {
      console.log(`📝 Order ${orderId} has no coupon applied, skipping usage tracking`);
      return { success: true, message: 'No coupon applied' };
    }
    
    if (!userId) {
      console.error(`❌ Order ${orderId} missing authorID, cannot track coupon usage`);
      return { success: false, error: 'Missing user ID' };
    }
    
    console.log(`🔍 Processing coupon usage for order ${orderId}: couponId=${couponId}, couponCode=${couponCode}, userId=${userId}`);
    
    // Find the coupon document
    let couponDoc;
    let couponData;
    
    if (couponId) {
      // Try to find by coupon ID first
      couponDoc = await firestore.collection('coupons').doc(couponId).get();
      if (couponDoc.exists) {
        couponData = couponDoc.data();
        console.log(`✅ Found coupon by ID: ${couponId}`);
      }
    }
    
    if (!couponDoc || !couponDoc.exists) {
      // Try to find by coupon code
      const couponQuery = await firestore.collection('coupons')
        .where('code', '==', couponCode)
        .limit(1)
        .get();
      
      if (!couponQuery.empty) {
        couponDoc = couponQuery.docs[0];
        couponData = couponDoc.data();
        console.log(`✅ Found coupon by code: ${couponCode}`);
      }
    }
    
    if (!couponDoc || !couponDoc.exists) {
      console.error(`❌ Coupon not found: couponId=${couponId}, couponCode=${couponCode}`);
      
      // Mark order as having invalid coupon
      await firestore.collection('restaurant_orders').doc(orderId).update({
        couponValidation: {
          isValid: false,
          reason: 'Coupon not found',
          validatedAt: admin.firestore.FieldValue.serverTimestamp()
        }
      });
      
      return { success: false, error: 'Coupon not found' };
    }
    
    // Validate coupon and track usage in a transaction
    const result = await firestore.runTransaction(async (transaction) => {
      // Re-read the coupon document to get latest data
      const currentCouponDoc = await transaction.get(couponDoc.ref);
      const currentCouponData = currentCouponDoc.data();
      
      console.log(`🔍 Validating coupon: ${currentCouponData.code}`);
      
      // Check if coupon is enabled
      if (!currentCouponData.isEnabled) {
        console.log(`❌ Coupon ${currentCouponData.code} is disabled`);
        return {
          isValid: false,
          reason: 'Coupon is disabled',
          couponCode: currentCouponData.code
        };
      }
      
      // Check if coupon has expired
      if (currentCouponData.expiresAt && currentCouponData.expiresAt.toDate() < new Date()) {
        console.log(`❌ Coupon ${currentCouponData.code} has expired`);
        return {
          isValid: false,
          reason: 'Coupon has expired',
          couponCode: currentCouponData.code
        };
      }
      
      // Check usage limit
      const usageLimit = currentCouponData.usageLimit || 0;
      const usedCount = currentCouponData.usedCount || 0;
      const usedBy = currentCouponData.usedBy || [];
      
      console.log(`📊 Coupon usage stats: ${usedCount}/${usageLimit}, usedBy: ${usedBy.length} users`);
      
      // If usage limit is set (not unlimited)
      if (usageLimit > 0) {
        // Check if limit has been reached
        if (usedCount >= usageLimit) {
          console.log(`❌ Coupon ${currentCouponData.code} usage limit reached: ${usedCount}/${usageLimit}`);
          return {
            isValid: false,
            reason: 'Coupon usage limit reached',
            couponCode: currentCouponData.code,
            usageLimit: usageLimit,
            usedCount: usedCount
          };
        }
        
        // Check if user has already used this coupon
        if (usedBy.includes(userId)) {
          console.log(`❌ User ${userId} has already used coupon ${currentCouponData.code}`);
          return {
            isValid: false,
            reason: 'User has already used this coupon',
            couponCode: currentCouponData.code,
            userId: userId
          };
        }
      }
      
      // Coupon is valid - update usage tracking
      const newUsedCount = usedCount + 1;
      const newUsedBy = [...usedBy, userId];
      
      // Update coupon document
      transaction.update(couponDoc.ref, {
        usedCount: newUsedCount,
        usedBy: newUsedBy,
        lastUsedAt: admin.firestore.FieldValue.serverTimestamp(),
        lastUsedBy: userId,
        lastUsedOrderId: orderId
      });
      
      console.log(`✅ Coupon ${currentCouponData.code} usage tracked successfully: ${newUsedCount}/${usageLimit}`);
      
      return {
        isValid: true,
        couponCode: currentCouponData.code,
        usageLimit: usageLimit,
        usedCount: newUsedCount,
        userId: userId
      };
    });
    
    // Update order with validation result
    await firestore.collection('restaurant_orders').doc(orderId).update({
      couponValidation: {
        isValid: result.isValid,
        reason: result.reason || 'Valid coupon',
        validatedAt: admin.firestore.FieldValue.serverTimestamp(),
        couponCode: result.couponCode,
        usageLimit: result.usageLimit,
        usedCount: result.usedCount,
        userId: result.userId
      }
    });
    
    // Log the coupon usage activity
    await firestore.collection('activity_logs').add({
      action: result.isValid ? 'coupon_used' : 'coupon_validation_failed',
      orderId: orderId,
      couponCode: result.couponCode,
      userId: userId,
      reason: result.reason,
      usageLimit: result.usageLimit,
      usedCount: result.usedCount,
      timestamp: admin.firestore.FieldValue.serverTimestamp(),
      metadata: {
        orderAmount: orderData.total || 0,
        discount: orderData.discount || 0,
        restaurantId: orderData.vendorID || '',
        orderStatus: orderData.status || ''
      }
    });
    
    if (result.isValid) {
      console.log(`✅ Coupon usage tracking completed successfully for order ${orderId}`);
      return { 
        success: true, 
        message: 'Coupon usage tracked successfully',
        validation: result
      };
    } else {
      console.log(`❌ Coupon validation failed for order ${orderId}: ${result.reason}`);
      return { 
        success: false, 
        error: result.reason,
        validation: result
      };
    }
    
  } catch (error) {
    console.error(`❌ Error in coupon usage tracking for order ${orderId}:`, error);
    
    // Update order with error status
    try {
      await firestore.collection('restaurant_orders').doc(orderId).update({
        couponValidation: {
          isValid: false,
          reason: 'Error during validation',
          error: error.message,
          validatedAt: admin.firestore.FieldValue.serverTimestamp()
        }
      });
    } catch (updateError) {
      console.error(`❌ Failed to update order with error status:`, updateError);
    }
    
    return { 
      success: false, 
      error: 'Internal error during coupon validation',
      details: error.message
    };
  }
});

// 🔥 **Manual Coupon Usage Reset Function**
// Allows admins to reset coupon usage for testing or corrections
exports.resetCouponUsage = functions.https.onCall(async (data, context) => {
  const { couponId, couponCode, resetType = 'all' } = data;
  
  // Validate admin authentication
  if (!context.auth) {
    throw new functions.https.HttpsError('unauthenticated', 'Admin must be authenticated');
  }
  
  console.log(`[${new Date().toISOString()}] 🔄 Manual coupon usage reset: couponId=${couponId}, couponCode=${couponCode}, resetType=${resetType}`);
  
  try {
    // Find the coupon document
    let couponDoc;
    let couponData;
    
    if (couponId) {
      couponDoc = await firestore.collection('coupons').doc(couponId).get();
      if (couponDoc.exists) {
        couponData = couponDoc.data();
      }
    }
    
    if (!couponDoc || !couponDoc.exists) {
      const couponQuery = await firestore.collection('coupons')
        .where('code', '==', couponCode)
        .limit(1)
        .get();
      
      if (!couponQuery.empty) {
        couponDoc = couponQuery.docs[0];
        couponData = couponDoc.data();
      }
    }
    
    if (!couponDoc || !couponDoc.exists) {
      throw new functions.https.HttpsError('not-found', 'Coupon not found');
    }
    
    // Reset coupon usage based on type
    const updateData = {};
    
    if (resetType === 'all') {
      updateData.usedCount = 0;
      updateData.usedBy = [];
      updateData.lastUsedAt = admin.firestore.FieldValue.delete();
      updateData.lastUsedBy = admin.firestore.FieldValue.delete();
      updateData.lastUsedOrderId = admin.firestore.FieldValue.delete();
    } else if (resetType === 'count') {
      updateData.usedCount = 0;
    } else if (resetType === 'users') {
      updateData.usedBy = [];
    }
    
    updateData.resetBy = context.auth.uid;
    updateData.resetAt = admin.firestore.FieldValue.serverTimestamp();
    updateData.resetType = resetType;
    
    await firestore.collection('coupons').doc(couponDoc.id).update(updateData);
    
    // Log the reset activity
    await firestore.collection('activity_logs').add({
      action: 'coupon_usage_reset',
      couponId: couponDoc.id,
      couponCode: couponData.code,
      adminId: context.auth.uid,
      resetType: resetType,
      timestamp: admin.firestore.FieldValue.serverTimestamp(),
      previousUsage: {
        usedCount: couponData.usedCount || 0,
        usedBy: couponData.usedBy || []
      }
    });
    
    console.log(`✅ Coupon usage reset successful: ${couponData.code}, resetType=${resetType}`);
    
    return { 
      success: true, 
      message: `Coupon usage reset successfully (${resetType})`,
      couponCode: couponData.code,
      resetType: resetType
    };
    
  } catch (error) {
    console.error(`❌ Error in manual coupon usage reset:`, error);
    throw new functions.https.HttpsError('internal', 'Failed to reset coupon usage');
  }
});

// 🔥 **Get Coupon Usage Statistics Function**
// Returns detailed usage statistics for a coupon
exports.getCouponUsageStats = functions.https.onCall(async (data, context) => {
  const { couponId, couponCode } = data;
  
  // Validate admin authentication
  if (!context.auth) {
    throw new functions.https.HttpsError('unauthenticated', 'Admin must be authenticated');
  }
  
  console.log(`[${new Date().toISOString()}] 📊 Getting coupon usage stats: couponId=${couponId}, couponCode=${couponCode}`);
  
  try {
    // Find the coupon document
    let couponDoc;
    let couponData;
    
    if (couponId) {
      couponDoc = await firestore.collection('coupons').doc(couponId).get();
      if (couponDoc.exists) {
        couponData = couponDoc.data();
      }
    }
    
    if (!couponDoc || !couponDoc.exists) {
      const couponQuery = await firestore.collection('coupons')
        .where('code', '==', couponCode)
        .limit(1)
        .get();
      
      if (!couponQuery.empty) {
        couponDoc = couponQuery.docs[0];
        couponData = couponDoc.data();
      }
    }
    
    if (!couponDoc || !couponDoc.exists) {
      throw new functions.https.HttpsError('not-found', 'Coupon not found');
    }
    
    // Get usage statistics
    const usedCount = couponData.usedCount || 0;
    const usedBy = couponData.usedBy || [];
    const usageLimit = couponData.usageLimit || 0;
    const remainingUses = usageLimit > 0 ? usageLimit - usedCount : 'Unlimited';
    const isExpired = couponData.expiresAt && couponData.expiresAt.toDate() < new Date();
    const isEnabled = couponData.isEnabled || false;
    
    // Get recent orders that used this coupon
    const recentOrdersQuery = await firestore.collection('restaurant_orders')
      .where('couponId', '==', couponDoc.id)
      .orderBy('createdAt', 'desc')
      .limit(10)
      .get();
    
    const recentOrders = recentOrdersQuery.docs.map(doc => {
      const orderData = doc.data();
      return {
        orderId: doc.id,
        userId: orderData.authorID,
        amount: orderData.total || 0,
        discount: orderData.discount || 0,
        status: orderData.status,
        createdAt: orderData.createdAt,
        couponValidation: orderData.couponValidation
      };
    });
    
    // Get user details for users who used this coupon
    const userDetails = [];
    if (usedBy.length > 0) {
      const usersQuery = await firestore.collection('users')
        .where(admin.firestore.FieldPath.documentId(), 'in', usedBy.slice(0, 10)) // Limit to first 10 users
        .get();
      
      usersQuery.docs.forEach(doc => {
        const userData = doc.data();
        userDetails.push({
          userId: doc.id,
          firstName: userData.firstName || '',
          lastName: userData.lastName || '',
          email: userData.email || '',
          phoneNumber: userData.phoneNumber || ''
        });
      });
    }
    
    const stats = {
      couponId: couponDoc.id,
      couponCode: couponData.code,
      usageLimit: usageLimit,
      usedCount: usedCount,
      remainingUses: remainingUses,
      usedByCount: usedBy.length,
      isExpired: isExpired,
      isEnabled: isEnabled,
      expiresAt: couponData.expiresAt,
      lastUsedAt: couponData.lastUsedAt,
      lastUsedBy: couponData.lastUsedBy,
      lastUsedOrderId: couponData.lastUsedOrderId,
      recentOrders: recentOrders,
      userDetails: userDetails,
      resetInfo: {
        resetBy: couponData.resetBy,
        resetAt: couponData.resetAt,
        resetType: couponData.resetType
      }
    };
    
    console.log(`✅ Coupon usage stats retrieved: ${couponData.code}`);
    
    return { 
      success: true, 
      stats: stats
    };
    
  } catch (error) {
    console.error(`❌ Error getting coupon usage stats:`, error);
    throw new functions.https.HttpsError('internal', 'Failed to get coupon usage statistics');
  }
});

module.exports = {
  trackCouponUsage,
  resetCouponUsage,
  getCouponUsageStats
};
