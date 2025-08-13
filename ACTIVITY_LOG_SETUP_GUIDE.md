# Activity Log System Setup Guide

## 🔧 **1. Firebase Configuration**

### Step 1: Create Firebase Project
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or select existing project
3. Enable Firestore Database
4. Set up security rules for `activity_logs` collection

### Step 2: Generate Service Account Key
1. Go to Project Settings > Service Accounts
2. Click "Generate new private key"
3. Download the JSON file
4. Place it in `storage/app/firebase/serviceAccount.json`

### Step 3: Environment Variables
Add these to your `.env` file:

```env
# Firebase Configuration
FIRESTORE_PROJECT_ID=your-project-id
FIRESTORE_DATABASE_ID=(default)
FIRESTORE_COLLECTION=activity_logs
FIRESTORE_TIMEOUT=30
FIRESTORE_RETRY_INITIAL_DELAY=1.0
FIRESTORE_RETRY_MAX_DELAY=60.0
FIRESTORE_RETRY_MULTIPLIER=2.0
```

## 📦 **2. Composer Dependencies**

Install required packages:

```bash
composer require google/cloud-firestore
composer require kreait/laravel-firebase
```

## 🔐 **3. Firestore Security Rules**

Add these rules to your Firestore database:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    // Activity logs collection
    match /activity_logs/{document} {
      allow read: if request.auth != null && 
        (request.auth.token.admin == true || 
         request.auth.token.role in ['super_admin', 'admin']);
      allow write: if request.auth != null;
    }
  }
}
```

## 🧪 **4. Testing the Implementation**

### Test with Cuisines Module:
1. Navigate to `/cuisines`
2. Create a new cuisine
3. Edit an existing cuisine
4. Delete a cuisine
5. Check `/activity-logs` page for real-time updates

### Expected Log Entries:
```json
{
  "user_id": "123",
  "user_type": "admin",
  "role": "super_admin",
  "module": "cuisines",
  "action": "created",
  "description": "Created new cuisine: Italian",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2025-01-15T10:30:00Z"
}
```

## 🔄 **5. Next Module Integration**

### Template for New Modules:
```javascript
// In your module's JavaScript file
function logModuleActivity(action, description) {
    logActivity('module_name', action, description);
}

// Example usage:
logModuleActivity('created', 'Created new item: ' + itemName);
logModuleActivity('updated', 'Updated item: ' + itemName);
logModuleActivity('deleted', 'Deleted item: ' + itemName);
```

## 🛡️ **6. Role-Based Permissions**

### Add to Role Controller:
```php
// In RoleController or Permission seeder
$activityLogPermissions = [
    'activity-logs.view' => 'View Activity Logs',
    'activity-logs.export' => 'Export Activity Logs',
    'activity-logs.filter' => 'Filter Activity Logs',
];
```

### Update Menu with Permissions:
```php
@if(in_array('activity-logs.view', $role_has_permission))
<li><a class="waves-effect waves-dark" href="{!! url('activity-logs') !!}">
    <i class="mdi mdi-history"></i>
    <span class="hide-menu">Activity Logs</span>
</a></li>
@endif
```

## 📊 **7. Monitoring & Analytics**

### Firestore Usage Monitoring:
- Monitor read/write operations
- Set up billing alerts
- Track collection size growth

### Performance Optimization:
- Implement pagination for large datasets
- Use indexes for common queries
- Consider data retention policies

## 🚀 **8. Production Deployment**

### Pre-deployment Checklist:
- [ ] Firebase project configured
- [ ] Service account key secured
- [ ] Environment variables set
- [ ] Security rules implemented
- [ ] Error logging configured
- [ ] Performance monitoring enabled

### Environment-Specific Configs:
```env
# Development
FIRESTORE_PROJECT_ID=dev-project-id
FIRESTORE_COLLECTION=activity_logs_dev

# Production
FIRESTORE_PROJECT_ID=prod-project-id
FIRESTORE_COLLECTION=activity_logs
```

## 🔍 **9. Troubleshooting**

### Common Issues:
1. **Firebase connection failed**: Check service account key path
2. **Permission denied**: Verify Firestore security rules
3. **CSRF token missing**: Ensure meta tag in layout
4. **Real-time updates not working**: Check Firebase config in activity logs page

### Debug Commands:
```bash
# Test Firestore connection
php artisan tinker
>>> app(\App\Services\ActivityLogger::class)->log(auth()->user(), 'test', 'test', 'Test log');
```

## 📈 **10. Future Enhancements**

### Planned Features:
- [ ] Export functionality (CSV/PDF)
- [ ] Advanced filtering options
- [ ] Email notifications for critical actions
- [ ] Dashboard analytics
- [ ] Data retention policies
- [ ] Audit trail reports

---

**Status**: ✅ Ready for testing with Cuisines module
**Next Step**: Configure Firebase and test the implementation
