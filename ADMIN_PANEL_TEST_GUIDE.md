# Admin Panel Activity Log Test Guide

## Overview
This guide provides step-by-step instructions to test the newly implemented Activity Log system in your admin panel, starting with the Cuisines module.

## Quick Access Information

### 🔗 **Route to Activity Logs Page**
- **URL**: `http://your-domain.com/activity-logs`
- **Route Name**: `activity-logs`
- **Controller**: `ActivityLogController@index`
- **View**: `resources/views/activity_logs/index.blade.php`

### 📍 **Menu Location**
- **Sidebar Menu**: "Activity Logs" (with history icon)
- **Permission Required**: `activity-logs.view`
- **Menu File**: `resources/views/layouts/menu.blade.php`

## Pre-Test Setup Checklist

### 1. Firebase Configuration
Before testing, ensure Firebase is properly configured:

```bash
# Check if Firebase config exists
ls storage/app/firebase/serviceAccount.json

# Verify .env has Firebase settings
cat .env | grep FIRESTORE
```

**Required .env variables:**
```env
FIRESTORE_PROJECT_ID=your-project-id
FIRESTORE_DATABASE_ID=(default)
FIRESTORE_COLLECTION=activity_logs
```

### 2. Update Firebase Config in Activity Logs Page
Edit `resources/views/activity_logs/index.blade.php` and update the Firebase config:

```javascript
const firebaseConfig = {
    apiKey: "your-api-key",
    authDomain: "your-project.firebaseapp.com",
    projectId: "your-project-id",
    storageBucket: "your-project.appspot.com",
    messagingSenderId: "123456789",
    appId: "your-app-id"
};
```

### 3. Verify Routes
Check if routes are properly registered:

```bash
php artisan route:list | grep activity
```

Expected output:
```
GET    /activity-logs                    activity-logs
POST   /api/activity-logs/log           api.activity-logs.log
GET    /api/activity-logs/module/{module} api.activity-logs.module
GET    /api/activity-logs/all           api.activity-logs.all
GET    /api/activity-logs/cuisines      api.activity-logs.cuisines
```

## Step-by-Step Test Protocol

### Phase 1: Basic Access Test

#### Test 1.1: Menu Visibility
1. **Login** to admin panel with a user that has `activity-logs.view` permission
2. **Navigate** to the sidebar menu
3. **Look for** "Activity Logs" menu item with history icon
4. **Expected Result**: Menu item should be visible and clickable

#### Test 1.2: Page Access
1. **Click** on "Activity Logs" menu item
2. **Expected Result**: Page should load without errors
3. **Check URL**: Should be `/activity-logs`
4. **Check Page Title**: Should show "Activity Logs"

#### Test 1.3: Page Elements
Verify the following elements are present:
- ✅ Module filter dropdown
- ✅ Logs count display
- ✅ Loading indicator
- ✅ Table with columns: User, Type, Role, Module, Action, Description, IP, Timestamp
- ✅ "No logs found" message (if no logs exist)

### Phase 2: Cuisines Module Testing

#### Test 2.1: Create Cuisine Log
1. **Navigate** to Cuisines module (`/cuisines`)
2. **Click** "Add New Cuisine" button
3. **Fill** the form with test data:
   - Title: "Test Cuisine - Activity Log"
   - Description: "Testing activity logging"
   - Upload an image
4. **Click** "Save" button
5. **Expected Result**: 
   - Cuisine should be created successfully
   - Redirect to cuisines list page
6. **Navigate** to Activity Logs page
7. **Check** for new log entry:
   - Module: "cuisines"
   - Action: "created"
   - Description: "Created new cuisine: Test Cuisine - Activity Log"

#### Test 2.2: Update Cuisine Log
1. **Navigate** to Cuisines module
2. **Find** the cuisine created in Test 2.1
3. **Click** "Edit" button
4. **Modify** the title to "Updated Test Cuisine"
5. **Click** "Update" button
6. **Expected Result**: 
   - Cuisine should be updated successfully
   - Redirect to cuisines list page
7. **Navigate** to Activity Logs page
8. **Check** for new log entry:
   - Module: "cuisines"
   - Action: "updated"
   - Description: "Updated cuisine: Updated Test Cuisine"

#### Test 2.3: Delete Cuisine Log
1. **Navigate** to Cuisines module
2. **Find** the cuisine from previous tests
3. **Click** "Delete" button
4. **Confirm** deletion
5. **Expected Result**: 
   - Cuisine should be deleted successfully
   - Redirect to cuisines list page
6. **Navigate** to Activity Logs page
7. **Check** for new log entry:
   - Module: "cuisines"
   - Action: "deleted"
   - Description: "Deleted cuisine: [Cuisine Title]"

### Phase 3: Real-Time Updates Test

#### Test 3.1: Live Updates
1. **Open** Activity Logs page in one browser tab
2. **Open** Cuisines module in another browser tab
3. **Perform** a create/update/delete action in Cuisines tab
4. **Watch** Activity Logs tab
5. **Expected Result**: New log should appear automatically without page refresh

#### Test 3.2: Module Filtering
1. **On** Activity Logs page, select "cuisines" from module dropdown
2. **Expected Result**: Only cuisines-related logs should be displayed
3. **Select** "All Modules" from dropdown
4. **Expected Result**: All logs should be displayed

### Phase 4: Data Verification

#### Test 4.1: Log Data Accuracy
For each log entry, verify these fields are correctly populated:

**User Information:**
- User ID: Should match logged-in user's ID
- User Type: Should be "admin" for admin panel users
- Role: Should match user's actual role (super_admin, manager, etc.)

**Action Information:**
- Module: Should be "cuisines" for cuisine operations
- Action: Should be "created", "updated", or "deleted"
- Description: Should be descriptive and accurate

**Technical Information:**
- IP Address: Should be your current IP address
- User Agent: Should contain browser information
- Timestamp: Should be current time when action was performed

#### Test 4.2: Timestamp Ordering
1. **Verify** logs are ordered by timestamp (newest first)
2. **Check** that newly created logs appear at the top

## Troubleshooting Guide

### Common Issues and Solutions

#### Issue 1: Activity Logs Menu Not Visible
**Symptoms**: Menu item not showing in sidebar
**Solutions**:
1. Check user permissions: `activity-logs.view`
2. Verify menu file: `resources/views/layouts/menu.blade.php`
3. Clear cache: `php artisan cache:clear`

#### Issue 2: Page Loads But No Logs Display
**Symptoms**: Page loads but shows "No logs found"
**Solutions**:
1. Check Firebase configuration in `activity_logs/index.blade.php`
2. Verify Firestore collection exists
3. Check browser console for JavaScript errors
4. Verify service account key is properly placed

#### Issue 3: Logs Not Being Created
**Symptoms**: Actions performed but no logs appear
**Solutions**:
1. Check browser console for AJAX errors
2. Verify CSRF token is present in page
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify ActivityLogger service is working

#### Issue 4: Real-Time Updates Not Working
**Symptoms**: Logs appear only after page refresh
**Solutions**:
1. Check Firebase configuration
2. Verify Firestore security rules allow read access
3. Check browser console for Firebase connection errors

### Debug Commands

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test ActivityLogger service
php test_activity_logs.php

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check route registration
php artisan route:list | grep activity
```

## Expected Test Results

### Successful Implementation Should Show:

1. **Menu Integration**: Activity Logs menu item visible and functional
2. **Page Access**: Activity Logs page loads without errors
3. **Real-Time Logging**: All cuisine CRUD operations create logs
4. **Live Updates**: New logs appear automatically without refresh
5. **Data Accuracy**: All log fields contain correct information
6. **Filtering**: Module filter works correctly
7. **Ordering**: Logs ordered by timestamp (newest first)

### Sample Log Entry Structure:
```json
{
  "user_id": "1",
  "user_type": "admin",
  "role": "super_admin",
  "module": "cuisines",
  "action": "created",
  "description": "Created new cuisine: Italian Cuisine",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "created_at": "2024-01-15T10:30:00Z"
}
```

## Next Steps After Testing

1. **If All Tests Pass**: Proceed to expand logging to other modules
2. **If Issues Found**: Follow troubleshooting guide and fix issues
3. **Performance Testing**: Test with large number of logs
4. **Security Testing**: Verify proper access controls
5. **User Training**: Document usage for admin users

## Support Information

- **Configuration Files**: `config/firestore.php`, `.env`
- **Service Class**: `app/Services/ActivityLogger.php`
- **Controller**: `app/Http/Controllers/ActivityLogController.php`
- **Views**: `resources/views/activity_logs/index.blade.php`
- **JavaScript**: `public/js/activity-logger.js`
- **Routes**: `routes/web.php`

---

**Note**: This guide assumes you have completed the Firebase setup as outlined in `FIREBASE_SETUP_INSTRUCTIONS.md`. If you haven't, please complete that setup first.
