# Mart Items Debugging Guide

## Issue: Dropdowns Not Populating

If the Restaurant and Category dropdowns are not populating in the Mart Items create/edit pages, follow this debugging guide.

## Step 1: Check Browser Console

1. **Open the Mart Items create page** (`/mart-item/create`)
2. **Open browser developer tools** (F12)
3. **Go to Console tab**
4. **Look for these debug messages:**

### Expected Console Output:
```
🔍 Create page - Starting data fetch...
🔍 Create page - Found X total vendors
📋 Create page - Vendor data: [Vendor Name] vType: [vType Value]
📋 Create page - Mart Vendor: [Mart Vendor Name] ID: [ID] vType: mart
🔍 Create page - Total mart vendors found: X
🔍 Create page - Found X mart categories
📋 Create page - Category: [Category Name] ID: [ID] publish: true
🔍 Create page - Found X vendor attributes
📋 Create page - Attribute: [Attribute Name] ID: [ID]
```

### If No Data Found:
```
⚠️ Create page - No mart vendors found! Check if vendors have vType: "mart" or "Mart"
⚠️ Create page - No mart categories found! Check if mart_categories collection exists and has published categories
```

## Step 2: Check Firestore Collections

### Check Vendors Collection:
1. **Go to Firebase Console**
2. **Navigate to Firestore Database**
3. **Check the `vendors` collection**
4. **Look for documents with `vType` field set to:**
   - `"mart"` (lowercase)
   - `"Mart"` (capitalized)

### Check Mart Categories Collection:
1. **Check the `mart_categories` collection**
2. **Look for documents with `publish` field set to `true`**

## Step 3: Common Issues and Solutions

### Issue 1: No Mart Vendors Found
**Problem:** Vendors don't have `vType: "mart"` or `"Mart"`

**Solution:**
1. **Update vendor documents** in Firestore:
   ```javascript
   // In Firebase Console, update vendor documents:
   {
     "title": "Your Mart Name",
     "vType": "mart",  // or "Mart"
     // ... other fields
   }
   ```

2. **Or create test mart vendors:**
   ```javascript
   // Add this to a test script or Firebase Console
   db.collection('vendors').add({
     title: "Test Mart",
     vType: "mart",
     // ... other required fields
   });
   ```

### Issue 2: No Mart Categories Found
**Problem:** `mart_categories` collection doesn't exist or has no published categories

**Solution:**
1. **Create mart_categories collection** if it doesn't exist
2. **Add categories with publish: true:**
   ```javascript
   db.collection('mart_categories').add({
     title: "Electronics",
     publish: true,
     // ... other fields
   });
   ```

### Issue 3: Collection Names Mismatch
**Problem:** Wrong collection names being used

**Solution:**
- **Vendors:** Use `vendors` collection
- **Categories:** Use `mart_categories` collection (not `vendor_categories`)
- **Items:** Use `mart_items` collection (not `vendor_products`)

## Step 4: Test Data Setup

### Create Test Mart Vendor:
```javascript
// In Firebase Console or test script
db.collection('vendors').add({
  title: "Test Mart Store",
  vType: "mart",
  author: "test_user_id",
  // ... other required fields
});
```

### Create Test Mart Category:
```javascript
// In Firebase Console or test script
db.collection('mart_categories').add({
  title: "Electronics",
  publish: true,
  // ... other required fields
});
```

## Step 5: Verify Data Structure

### Vendor Document Structure:
```javascript
{
  id: "vendor_id",
  title: "Mart Store Name",
  vType: "mart",  // This is crucial!
  author: "user_id",
  // ... other fields
}
```

### Category Document Structure:
```javascript
{
  id: "category_id",
  title: "Category Name",
  publish: true,  // This is crucial!
  // ... other fields
}
```

## Step 6: Fallback Debugging

If the dropdowns still don't populate, the code now includes fallback debugging:

1. **If no mart vendors found:** All vendors will be shown with their vType in parentheses
2. **If no published categories found:** All categories will be shown with their publish status

This will help identify if:
- The collections exist but have wrong field values
- The collections don't exist at all
- There are permission issues

## Step 7: Check Network Tab

1. **Open browser developer tools**
2. **Go to Network tab**
3. **Refresh the page**
4. **Look for Firestore requests**
5. **Check if requests are successful or failing**

## Step 8: Common Error Messages

### "Permission denied":
- Check Firebase security rules
- Ensure user has read access to collections

### "Collection not found":
- Collection doesn't exist
- Wrong collection name

### "Field not found":
- Document exists but missing required fields
- Wrong field names

## Step 9: Quick Fix Commands

### If you need to quickly test with sample data:

```javascript
// Add a test mart vendor
db.collection('vendors').add({
  title: "Sample Mart",
  vType: "mart",
  author: "admin",
  createdAt: new Date()
});

// Add a test mart category
db.collection('mart_categories').add({
  title: "Sample Category",
  publish: true,
  createdAt: new Date()
});
```

## Step 10: Final Verification

After making changes:

1. **Clear browser cache**
2. **Refresh the page**
3. **Check console for debug messages**
4. **Verify dropdowns populate**
5. **Test creating a mart item**

## Support

If you're still having issues after following this guide:

1. **Check the console output** and share the debug messages
2. **Verify your Firestore collections** exist and have the correct data
3. **Ensure you're using the correct collection names** (`mart_categories`, not `vendor_categories`)
4. **Check that vendors have `vType: "mart"`** (case-sensitive)
5. **Verify categories have `publish: true`**
