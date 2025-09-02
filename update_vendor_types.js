/**
 * JavaScript script to update vendor types in Firebase database
 * Run this in the browser console on your admin panel
 * Changes vendors with empty or "not set" vType to "restaurant"
 * Keeps "mart" vendors unchanged
 */

console.log('🔍 Starting vendor type update process...');

// Get Firebase database reference
var database = firebase.firestore();
var usersCollection = database.collection('users');
var vendorsCollection = database.collection('vendors');

// Statistics
var updatedCount = 0;
var skippedCount = 0;
var errorCount = 0;
var totalProcessed = 0;

// Function to update vendor types
async function updateVendorTypes() {
    try {
        // Get all vendor users
        const vendorUsersSnapshot = await usersCollection.where('role', '==', 'vendor').get();
        
        console.log(`📊 Found ${vendorUsersSnapshot.docs.length} vendor users`);
        
        for (const userDoc of vendorUsersSnapshot.docs) {
            const userData = userDoc.data();
            const userId = userDoc.id;
            const vendorName = (userData.firstName || 'Unknown') + ' ' + (userData.lastName || '');
            
            console.log(`Processing vendor: ${vendorName} (ID: ${userId})`);
            
            // Check current vType
            const currentVType = userData.vType || '';
            console.log(`  Current vType: '${currentVType}'`);
            
            // Check if vType is empty, null, or "not set"
            if (!currentVType || currentVType.trim() === '' || currentVType.toLowerCase().trim() === 'not set') {
                console.log(`  → Updating to 'restaurant'`);
                
                try {
                    // Update the user document
                    await usersCollection.doc(userId).update({
                        vType: 'restaurant'
                    });
                    
                    // Also check if there's a corresponding vendor document
                    const vendorDocsSnapshot = await vendorsCollection.where('author', '==', userId).get();
                    
                    for (const vendorDoc of vendorDocsSnapshot.docs) {
                        const vendorData = vendorDoc.data();
                        const vendorVType = vendorData.vType || '';
                        
                        if (!vendorVType || vendorVType.trim() === '' || vendorVType.toLowerCase().trim() === 'not set') {
                            console.log(`  → Also updating vendor document vType to 'restaurant'`);
                            await vendorsCollection.doc(vendorDoc.id).update({
                                vType: 'restaurant'
                            });
                        }
                    }
                    
                    updatedCount++;
                    console.log(`  ✅ Updated successfully`);
                    
                } catch (error) {
                    console.error(`  ❌ Error updating: ${error.message}`);
                    errorCount++;
                }
            } else {
                console.log(`  → Skipping (already has valid vType: '${currentVType}')`);
                skippedCount++;
            }
            
            totalProcessed++;
            console.log('');
        }
        
        // Print summary
        console.log('📊 Update Summary:');
        console.log(`  ✅ Updated: ${updatedCount} vendors`);
        console.log(`  ⏭️  Skipped: ${skippedCount} vendors`);
        console.log(`  ❌ Errors: ${errorCount} vendors`);
        console.log(`  📝 Total processed: ${totalProcessed} vendors`);
        
        console.log('\n🎉 Vendor type update process completed!');
        console.log('💡 Note: You may need to refresh the vendors page to see the changes.');
        
    } catch (error) {
        console.error('❌ Fatal error:', error);
        console.error('Stack trace:', error.stack);
    }
}

// Function to show progress
function showProgress() {
    console.log('🔄 Starting update...');
    updateVendorTypes().then(() => {
        console.log('✅ Update process finished');
    }).catch((error) => {
        console.error('❌ Update process failed:', error);
    });
}

// Start the update process
showProgress();

// You can also call updateVendorTypes() directly if you want to run it manually
