# Module Integration Template for Activity Logs

## 📋 **Integration Checklist for New Modules**

### **Step 1: Identify Module Files**
- [ ] Controller file (e.g., `UserController.php`)
- [ ] Index view (e.g., `resources/views/users/index.blade.php`)
- [ ] Create view (e.g., `resources/views/users/create.blade.php`)
- [ ] Edit view (e.g., `resources/views/users/edit.blade.php`)

### **Step 2: Add JavaScript Helper**
Include the activity logger in your module's layout or views:

```html
<!-- Add this to your module's Blade files -->
<script src="{{ asset('js/activity-logger.js') }}"></script>
```

### **Step 3: Integrate Logging Calls**

#### **For Create Operations:**
```javascript
// After successful creation
logActivity('module_name', 'created', 'Created new item: ' + itemName);
```

#### **For Update Operations:**
```javascript
// After successful update
logActivity('module_name', 'updated', 'Updated item: ' + itemName);
```

#### **For Delete Operations:**
```javascript
// After successful deletion
logActivity('module_name', 'deleted', 'Deleted item: ' + itemName);
```

#### **For View Operations:**
```javascript
// When page loads
logActivity('module_name', 'viewed', 'Viewed module page');
```

### **Step 4: Example Integration**

#### **Users Module Example:**

**In `resources/views/users/create.blade.php`:**
```javascript
// After form submission success
$.ajax({
    url: '/users/store',
    method: 'POST',
    data: formData,
    success: function(response) {
        if (response.success) {
            // Log the activity
            logActivity('users', 'created', 'Created new user: ' + response.user.name);
            
            // Redirect or show success message
            window.location.href = '/users';
        }
    }
});
```

**In `resources/views/users/edit.blade.php`:**
```javascript
// After form submission success
$.ajax({
    url: '/users/update/' + userId,
    method: 'POST',
    data: formData,
    success: function(response) {
        if (response.success) {
            // Log the activity
            logActivity('users', 'updated', 'Updated user: ' + response.user.name);
            
            // Redirect or show success message
            window.location.href = '/users';
        }
    }
});
```

**In `resources/views/users/index.blade.php`:**
```javascript
// For delete operations
$(document).on("click", ".delete-user", function() {
    var userId = $(this).data('id');
    var userName = $(this).data('name');
    
    if (confirm('Are you sure you want to delete this user?')) {
        $.ajax({
            url: '/users/delete/' + userId,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    // Log the activity
                    logActivity('users', 'deleted', 'Deleted user: ' + userName);
                    
                    // Refresh page or remove row
                    location.reload();
                }
            }
        });
    }
});

// For page view logging
$(document).ready(function() {
    logActivity('users', 'viewed', 'Viewed users list');
});
```

### **Step 5: Module-Specific Considerations**

#### **For Firebase-based Modules (like Cuisines):**
```javascript
// After Firebase operation success
database.collection('collection_name').doc(id).set(data).then(function(result) {
    // Log the activity
    logActivity('module_name', 'created', 'Created new item: ' + title);
    
    // Continue with existing logic
    window.location.href = '/module';
});
```

#### **For Traditional Laravel Modules:**
```javascript
// After AJAX success
$.ajax({
    url: '/module/store',
    method: 'POST',
    data: formData,
    success: function(response) {
        if (response.success) {
            // Log the activity
            logActivity('module_name', 'created', 'Created new item: ' + response.data.name);
            
            // Continue with existing logic
            window.location.href = '/module';
        }
    }
});
```

### **Step 6: Testing Integration**

#### **Test Checklist:**
1. [ ] Create a new item - check logs
2. [ ] Edit an existing item - check logs
3. [ ] Delete an item - check logs
4. [ ] View the module page - check logs
5. [ ] Verify logs appear in `/activity-logs` page
6. [ ] Test real-time updates

#### **Expected Log Entries:**
```json
{
  "user_id": "123",
  "user_type": "admin",
  "role": "super_admin",
  "module": "users",
  "action": "created",
  "description": "Created new user: John Doe",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2025-01-15T10:30:00Z"
}
```

### **Step 7: Common Module Names**

Use these standardized module names:
- `users` - User management
- `vendors` - Vendor management
- `restaurants` - Restaurant management
- `drivers` - Driver management
- `orders` - Order management
- `foods` - Food items
- `categories` - Categories
- `cuisines` - Cuisines
- `coupons` - Coupons
- `payments` - Payment management
- `settings` - System settings
- `reports` - Reports
- `notifications` - Notifications

### **Step 8: Advanced Integration**

#### **Bulk Operations:**
```javascript
// For bulk delete
logActivity('users', 'bulk_deleted', 'Deleted ' + count + ' users');
```

#### **Import/Export:**
```javascript
// For import operations
logActivity('users', 'imported', 'Imported ' + count + ' users from file');
```

#### **Status Changes:**
```javascript
// For status updates
logActivity('users', 'status_changed', 'Changed user status to: ' + newStatus);
```

---

## 🚀 **Quick Integration Commands**

### **Find all CRUD operations in a module:**
```bash
grep -r "success\|then\|reload" resources/views/module_name/
```

### **Add logging to all operations:**
```bash
# Use search and replace in your editor
# Find: success: function(response) {
# Replace: success: function(response) {\n            logActivity('module_name', 'action', 'description');
```

### **Test the integration:**
1. Run the test script: `php test_activity_logs.php`
2. Visit `/activity-logs` to see real-time logs
3. Perform actions in your module
4. Verify logs appear immediately

---

**Template Status**: ✅ Ready for use
**Next Module**: Choose any module from the list above
