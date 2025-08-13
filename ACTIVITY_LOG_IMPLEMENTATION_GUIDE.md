 # Activity Log Implementation Guide

## Overview
This guide provides step-by-step instructions for implementing and using the Activity Log system in the JippyMart Admin Panel.

## What's Been Implemented

### 1. Backend Components

#### ActivityLogger Service (`app/Services/ActivityLogger.php`)
- Handles all Firestore logging operations
- Supports logging with user information, IP address, and user agent
- Provides methods to retrieve logs by module or all logs
- Automatic error handling and logging

#### ActivityLogController (`app/Http/Controllers/ActivityLogController.php`)
- RESTful API endpoints for activity logging
- Methods for retrieving logs with filtering and pagination
- Handles authentication and validation

#### Routes (`routes/web.php`)
- `/activity-logs` - Main activity logs page
- `/api/activity-logs/log` - POST endpoint for logging activities
- `/api/activity-logs/module/{module}` - GET logs by module
- `/api/activity-logs/all` - GET all logs
- `/api/activity-logs/cuisines` - GET cuisines-specific logs

### 2. Frontend Components

#### Activity Logs Page (`resources/views/activity_logs/index.blade.php`)
- Real-time Firebase integration
- Live updates without page refresh
- Module filtering
- Responsive table design
- Loading states and error handling

#### Menu Integration (`resources/views/layouts/menu.blade.php`)
- Added "Activity Logs" menu item
- Accessible from main navigation

#### Cuisines Module Integration
- **Create Page**: Logs cuisine creation
- **Edit Page**: Logs cuisine updates
- **Index Page**: Logs cuisine deletions
- **Helper Functions**: Reusable logging functions

#### JavaScript Helper (`public/js/activity-logger.js`)
- Reusable logging functions
- CSRF token handling
- Error handling and logging

## Firestore Structure

### Collection: `activity_logs`

Each document contains:
```json
{
  "user_id": "123",
  "user_type": "admin",
  "role": "super_admin",
  "module": "cuisines",
  "action": "created",
  "description": "Added new cuisine: Italian",
  "ip_address": "192.168.0.10",
  "user_agent": "Chrome on Windows",
  "created_at": "2025-08-11T06:25:00Z"
}
```

## Usage Examples

### 1. Basic Activity Logging
```javascript
// Log a simple activity
logActivity('cuisines', 'created', 'Created new cuisine: Italian');

// Log with additional data
logActivityWithData('orders', 'updated', 'Updated order status', {
    order_id: '12345',
    old_status: 'pending',
    new_status: 'confirmed'
});
```

### 2. Page View Logging
```javascript
// Log when page is viewed
logPageView('cuisines');
```

### 3. Form Submission Logging
```javascript
// Log form submissions
logFormSubmission('cuisines', 'cuisine-form', 'submitted');
```

### 4. Button Click Logging
```javascript
// Log button clicks
logButtonClick('cuisines', 'Save Cuisine', 'clicked');
```

## Integration Steps for New Modules

### Step 1: Include the Activity Logger
Add to your Blade template:
```html
<script src="{{ asset('js/activity-logger.js') }}"></script>
```

### Step 2: Add CSRF Token Meta Tag
Ensure your layout includes:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Step 3: Log Activities in JavaScript
```javascript
// For create operations
database.collection('your_collection').add(data).then(function(result) {
    logActivity('your_module', 'created', 'Created new item: ' + itemName);
    // ... rest of your code
});

// For update operations
database.collection('your_collection').doc(id).update(data).then(function(result) {
    logActivity('your_module', 'updated', 'Updated item: ' + itemName);
    // ... rest of your code
});

// For delete operations
database.collection('your_collection').doc(id).delete().then(function(result) {
    logActivity('your_module', 'deleted', 'Deleted item: ' + itemName);
    // ... rest of your code
});
```

### Step 4: Add Module to Filter Dropdown
Update `resources/views/activity_logs/index.blade.php`:
```html
<option value="your_module">Your Module</option>
```

## Configuration

### Firebase Configuration
Update the Firebase config in `resources/views/activity_logs/index.blade.php`:
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

### Environment Variables
Ensure these are set in your `.env` file:
```
FIRESTORE_PROJECT_ID=your-project-id
```

## Testing the Implementation

### 1. Test Cuisines Module
1. Navigate to Cuisines page
2. Create a new cuisine
3. Edit an existing cuisine
4. Delete a cuisine
5. Check Activity Logs page for entries

### 2. Test Real-time Updates
1. Open Activity Logs page
2. Open another tab and perform actions
3. Verify logs appear in real-time

### 3. Test Filtering
1. Use module filter dropdown
2. Verify only relevant logs are shown

## Troubleshooting

### Common Issues

1. **CSRF Token Error**
   - Ensure `<meta name="csrf-token">` is in your layout
   - Check that the token is being passed correctly

2. **Firebase Connection Error**
   - Verify Firebase configuration
   - Check network connectivity
   - Ensure Firestore rules allow read/write

3. **Activity Not Logging**
   - Check browser console for errors
   - Verify API endpoint is accessible
   - Check server logs for errors

4. **Real-time Updates Not Working**
   - Verify Firebase SDK is loaded
   - Check Firestore listener setup
   - Ensure proper error handling

### Debug Mode
Enable debug logging by adding to your JavaScript:
```javascript
// Enable debug mode
window.ACTIVITY_LOG_DEBUG = true;
```

## Security Considerations

1. **Authentication**: All endpoints require authentication
2. **Authorization**: Consider adding role-based access to logs
3. **Data Privacy**: Be mindful of logging sensitive information
4. **Rate Limiting**: Consider implementing rate limiting for log endpoints

## Performance Considerations

1. **Pagination**: Use pagination for large log datasets
2. **Indexing**: Ensure Firestore indexes are set up for queries
3. **Caching**: Consider caching frequently accessed logs
4. **Cleanup**: Implement log retention policies

## Future Enhancements

1. **Export Functionality**: Add CSV/PDF export
2. **Advanced Filtering**: Date range, user filtering
3. **Dashboard Widgets**: Activity summary widgets
4. **Email Notifications**: Alert on specific activities
5. **Audit Trail**: Enhanced audit trail features

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review server logs
3. Check browser console for errors
4. Verify Firebase configuration
