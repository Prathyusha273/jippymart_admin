Complete Admin Impersonates Restaurant - Implementation Documentation
Project Overview
Goal: Allow admin users to impersonate restaurant owners and automatically log into the restaurant panel without needing restaurant credentials.
Architecture: Multi-panel delivery platform with separate authentication systems:
Admin Panel: admin.jippymart.in (Laravel + Firebase)
Restaurant Panel: restaurant.jippymart.in (Laravel + Firebase)
Customer Panel: jippymart.in (Laravel + Firebase)
✅ What Has Been Implemented (Admin Panel)
1. Backend Services
   FirebaseImpersonationService (app/Services/FirebaseImpersonationService.php)
   Purpose: Generates Firebase Custom Tokens for restaurant UIDs
   Key Methods:
   generateImpersonationToken() - Creates secure custom tokens with 5-minute expiration
   validateImpersonationToken() - Validates token authenticity
   logImpersonation() - Security audit logging to Firestore
   getRestaurantInfo() - Retrieves restaurant and owner information
   ImpersonationController (app/Http/Controllers/ImpersonationController.php)
   Purpose: Handles impersonation API requests
   Key Methods:
   generateToken() - Creates impersonation tokens with validation
   getRestaurantInfo() - Retrieves restaurant details
   validateToken() - Validates tokens
   Security: Rate limiting, origin validation, comprehensive logging
   ImpersonationSecurityMiddleware (app/Http/Middleware/ImpersonationSecurityMiddleware.php)
   Purpose: Security and rate limiting
   Features:
   Rate limiting (10 attempts per hour per admin)
   Request origin validation
   Comprehensive audit logging
   IP and user agent tracking
2. Frontend Components
   Admin Panel Impersonation Button (resources/views/restaurants/index.blade.php)
   Location: Added to restaurant actions in the restaurant list table
   Features:
   👤 Icon button next to each restaurant
   Confirmation dialog before impersonation
   Loading states with spinner
   Success/error notifications
   Automatic redirect to restaurant panel
   JavaScript Integration
   File: resources/views/restaurants/index.blade.php (lines 1253-1321)
   Features:
   AJAX call to generate impersonation token
   Error handling with user-friendly messages
   Loading state management
   Automatic redirect with window.open()
3. Database & Permissions
   Permission System
   Seeder: database/seeders/ImpersonationPermissionsSeeder.php
   Permission Added: restaurants.impersonate
   Role Assignment: Added to admin role (role_id = 1)
   Status: ✅ Successfully executed
4. Routes Configuration
   Admin Panel Routes (routes/web.php)
   Middleware Registration (app/Http/Kernel.php)
5. Security Features
   Token Security
   ✅ Expiration: 5 minutes (configurable)
   ✅ Claims: Include admin ID, restaurant ID, expiration timestamp
   ✅ Caching: Prevents token reuse with Laravel Cache
   ✅ Validation: Server-side token verification
   Rate Limiting
   ✅ Limit: 10 impersonation attempts per hour per admin
   ✅ Storage: Laravel Cache with exponential backoff
   ✅ Scope: Per admin user ID
   Audit Logging
   ✅ Location: Firestore collection admin_impersonation_logs
   ✅ Data: Admin ID, restaurant ID, timestamp, IP, user agent
   ✅ Retention: Configurable
   Origin Validation
   ✅ Allowed Origins: admin.jippymart.in, localhost (dev)
   ✅ Validation: HTTP headers (Origin, Referer, Host)
   ✅ Environment: Disabled in local development
   🔄 Current Flow (Working)
   Step 1: Admin Initiates Impersonation
   Admin clicks impersonation button (👤 icon) in restaurant list
   Confirmation dialog appears: "Are you sure you want to login as [Restaurant Name]?"
   Admin confirms the action
   Step 2: Token Generation
   System generates Firebase Custom Token for restaurant owner's UID
   Token includes custom claims:
   admin_impersonation: true
   impersonated_by: [admin_user_id]
   restaurant_id: [restaurant_id]
   restaurant_name: [restaurant_name]
   expires_at: [timestamp]
   Step 3: Redirect to Restaurant Panel
   Admin is redirected to: https://restaurant.jippymart.in/login?impersonation_token=...&restaurant_uid=...&auto_login=true
   New tab opens to restaurant panel
   Step 4: Restaurant Panel Processing ❌ NOT IMPLEMENTED YET
   Restaurant panel receives custom token via URL
   Auto-login script should detect parameters
   Script should use Firebase signInWithCustomToken()
   User should be automatically logged in
   Redirect to /dashboard
   ❌ What's Missing (Restaurant Panel)
   Current Issue:
   The restaurant panel receives the impersonation token correctly, but doesn't have the auto-login script to process it.
   What Happens Now:
   ✅ Admin generates token
   ✅ Redirects to restaurant panel with token
   ❌ Restaurant panel shows normal login form (ignores the token)
   ❌ User has to manually log in
   What Needs to Be Done (Restaurant Panel)
<script>
// Auto-login script for Admin Impersonation
(function() {
    console.log('🔍 Auto-login script started');
    
    // Check URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const impersonationToken = urlParams.get('impersonation_token');
    const restaurantUid = urlParams.get('restaurant_uid');
    const autoLogin = urlParams.get('auto_login');
    
    console.log('🔍 Parameters:', {
        token: !!impersonationToken,
        uid: !!restaurantUid,
        autoLogin: autoLogin
    });
    
    // Only proceed if we have all required parameters
    if (impersonationToken && restaurantUid && autoLogin === 'true') {
        console.log('🔐 Starting auto-login process...');
        
        // Show loading immediately
        showLoading();
        
        // Wait for Firebase to be ready
        setTimeout(function() {
            if (typeof firebase !== 'undefined' && firebase.auth) {
                startAutoLogin();
            } else {
                console.error('❌ Firebase not available');
                showError('Firebase not loaded. Please refresh the page.');
            }
        }, 1000);
    } else {
        console.log('ℹ️ No impersonation parameters, showing normal login');
    }
    
    function startAutoLogin() {
        console.log('🚀 Starting auto-login...');
        
        const auth = firebase.auth();
        
        // Sign in with custom token
        auth.signInWithCustomToken(impersonationToken)
            .then(function(userCredential) {
                console.log('✅ Login successful!');
                console.log('User UID:', userCredential.user.uid);
                console.log('Expected UID:', restaurantUid);
                
                // Verify UID matches
                if (userCredential.user.uid !== restaurantUid) {
                    throw new Error('UID mismatch - security violation');
                }
                
                // Store impersonation info
                localStorage.setItem('restaurant_impersonation', JSON.stringify({
                    isImpersonated: true,
                    restaurantUid: restaurantUid,
                    impersonatedAt: new Date().toISOString()
                }));
                
                console.log('🔄 Redirecting to dashboard...');
                
                // Redirect to dashboard
                setTimeout(function() {
                    window.location.href = '/dashboard';
                }, 1000);
            })
            .catch(function(error) {
                console.error('❌ Login failed:', error);
                showError('Auto-login failed: ' + error.message);
                
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            });
    }
    
    function showLoading() {
        const loading = document.createElement('div');
        loading.id = 'auto-login-loading';
        loading.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; justify-content: center; align-items: center;">
                <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
                    <div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                    <h3>Logging you in...</h3>
                    <p>Please wait while we authenticate you.</p>
                </div>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `;
        document.body.appendChild(loading);
    }
    
    function showError(message) {
        const error = document.createElement('div');
        error.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; z-index: 9999; max-width: 400px;">
                <strong>Auto-login Failed:</strong><br>
                ${message}
                <button onclick="this.parentElement.parentElement.remove()" style="float: right; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
            </div>
        `;
        document.body.appendChild(error);
    }
})();
</script>
1. Add Auto-Login Script to Login Page
   File to Modify: [RESTAURANT_PANEL_ROOT]/resources/views/auth/login.blade.php
   Script to Add (Before closing </body> tag):
2. Ensure Firebase SDK is Loaded
<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-auth-compat.js"></script>

<script>
// Firebase configuration (should match admin panel)
const firebaseConfig = {
    apiKey: "AIzaSyAf_lICoxPh8qKE1QnVkmQYTFJXKkYmRXU",
    authDomain: "jippymart-27c08.firebaseapp.com",
    databaseURL: "https://jippymart-27c08-default-rtdb.firebaseio.com",
    projectId: "jippymart-27c08",
    storageBucket: "jippymart-27c08.firebasestorage.app",
    messagingSenderId: "592427852800",
    appId: "1:592427852800:web:f74df8ceb2a4b597d1a4e5",
    measurementId: "G-ZYBQYPZWCF"
};

// Initialize Firebase
if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}
</script>
Make sure your restaurant panel's login page includes:
3. Optional: Add Impersonation Banner to Dashboard
   File to Modify: [RESTAURANT_PANEL_ROOT]/resources/views/dashboard.blade.php (or main layout)
<script>
// Check if user is impersonated and show notification
(function() {
    const impersonationData = localStorage.getItem('restaurant_impersonation');
    
    if (impersonationData) {
        try {
            const data = JSON.parse(impersonationData);
            
            if (data.isImpersonated) {
                // Show impersonation banner
                const banner = document.createElement('div');
                banner.innerHTML = `
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 5px; position: relative;">
                        <strong>🔐 Admin Impersonation Active</strong><br>
                        You are currently logged in as this restaurant owner for support purposes.<br>
                        <small>Impersonated at: ${new Date(data.impersonatedAt).toLocaleString()}</small>
                        <button onclick="this.parentElement.parentElement.remove()" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
                    </div>
                `;
                
                // Insert at the top of the page
                const body = document.body;
                if (body.firstChild) {
                    body.insertBefore(banner, body.firstChild);
                } else {
                    body.appendChild(banner);
                }
            }
        } catch (error) {
            console.error('Error parsing impersonation data:', error);
        }
    }
})();
</script>
Script to Add:
🧪 Testing Checklist
Before Restaurant Panel Implementation:
[ ] Admin panel impersonation button works
[ ] Token generation successful
[ ] Redirect to restaurant panel works
[ ] URL contains correct parameters
After Restaurant Panel Implementation:
[ ] Auto-login script detects parameters
[ ] Firebase authentication works
[ ] User is logged in automatically
[ ] Redirect to dashboard works
[ ] Impersonation banner shows (optional)
Error Scenarios:
[ ] Token expired (5 minutes)
[ ] Invalid token
[ ] Firebase not loaded
[ ] Network issues
📊 Current Status
Component	Status	Notes
Admin Panel Token Generation	✅ Complete	Working perfectly
Admin Panel UI	✅ Complete	Button, confirmation, notifications
Security Features	✅ Complete	Rate limiting, logging, validation
Restaurant Panel Auto-Login	❌ Missing	This is what needs to be implemented
Restaurant Panel Firebase Config	❓ Unknown	Need to verify
End-to-End Flow	❌ Incomplete	Missing restaurant panel implementation
Next Steps
Implement auto-login script in restaurant panel's login.blade.php
Verify Firebase configuration in restaurant panel
Test complete flow from admin to restaurant panel
Add impersonation banner (optional)
Deploy to production
🔧 Files Created/Modified
Admin Panel Files:
✅ app/Services/FirebaseImpersonationService.php (NEW)
✅ app/Http/Controllers/ImpersonationController.php (NEW)
✅ app/Http/Middleware/ImpersonationSecurityMiddleware.php (NEW)
✅ resources/views/restaurants/index.blade.php (MODIFIED - added button)
✅ routes/web.php (MODIFIED - added routes)
✅ app/Http/Kernel.php (MODIFIED - registered middleware)
✅ database/seeders/ImpersonationPermissionsSeeder.php (NEW)
Restaurant Panel Files (TO BE IMPLEMENTED):
❌ [RESTAURANT_PANEL_ROOT]/resources/views/auth/login.blade.php (MODIFY - add auto-login script)
❌ [RESTAURANT_PANEL_ROOT]/resources/views/dashboard.blade.php (MODIFY - add banner, optional)
🚨 Critical Issue
The only missing piece is the auto-login script in the restaurant panel's login page. Once this is implemented, the complete flow will work end-to-end.
Current Flow: Admin → Token → Restaurant Panel → ❌ STOPS HERE (shows normal login)
Expected Flow: Admin → Token → Restaurant Panel → Auto-Login → Dashboard ✅
This documentation provides a complete overview of what has been implemented and what still needs to be done to achieve the full "Admin Impersonates Restaurant" functionality.
