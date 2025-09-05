// Restaurant Panel Auto-Login Script
// Add this script to your restaurant panel's login page

(function() {
    // Check if we have impersonation parameters
    const urlParams = new URLSearchParams(window.location.search);
    const impersonationToken = urlParams.get('impersonation_token');
    const restaurantUid = urlParams.get('restaurant_uid');
    const autoLogin = urlParams.get('auto_login');
    
    if (impersonationToken && restaurantUid && autoLogin === 'true') {
        console.log('🔐 Auto-login with impersonation token detected');
        
        // Set a flag to prevent other auth listeners from interfering
        localStorage.setItem('impersonation_in_progress', 'true');
        localStorage.setItem('impersonation_target_url', '/dashboard');
        
        // Initialize Firebase (make sure Firebase is already loaded)
        if (typeof firebase !== 'undefined' && firebase.auth) {
            const auth = firebase.auth();
            
            // Show loading indicator
            showImpersonationLoading();
            
            // Sign in with custom token
            auth.signInWithCustomToken(impersonationToken)
                .then(function(userCredential) {
                    console.log('✅ Successfully logged in with impersonation token');
                    
                    // Verify the user is the correct restaurant owner
                    if (userCredential.user.uid !== restaurantUid) {
                        throw new Error('Token UID mismatch. Security violation detected.');
                    }
                    
                    // Store impersonation info
                    localStorage.setItem('restaurant_impersonation', JSON.stringify({
                        isImpersonated: true,
                        restaurantUid: restaurantUid,
                        impersonatedAt: new Date().toISOString(),
                        tokenUsed: true
                    }));
                    
                    // Force redirect to dashboard after a short delay
                    setTimeout(function() {
                        console.log('🔄 Redirecting to dashboard...');
                        window.location.href = '/dashboard';
                    }, 1000);
                })
                .catch(function(error) {
                    console.error('❌ Auto-login failed:', error);
                    
                    // Clear the impersonation flags
                    localStorage.removeItem('impersonation_in_progress');
                    localStorage.removeItem('impersonation_target_url');
                    
                    // Show error message
                    showImpersonationError(error.message);
                    
                    // Clean URL parameters
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
        } else {
            console.error('❌ Firebase not loaded');
            showImpersonationError('Firebase not loaded. Please refresh the page.');
        }
    }
    
    // Helper function to show loading state
    function showImpersonationLoading() {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'impersonation-loading';
        loadingDiv.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; justify-content: center; align-items: center;">
                <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                    <div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                    <h3 style="margin: 0 0 10px 0; color: #333;">Logging you in...</h3>
                    <p style="margin: 0; color: #666;">Please wait while we authenticate you.</p>
                </div>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `;
        document.body.appendChild(loadingDiv);
    }
    
    // Helper function to show error
    function showImpersonationError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; z-index: 9999; max-width: 400px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <strong>Auto-login Failed:</strong><br>
                ${message}
                <button onclick="this.parentElement.parentElement.remove()" style="float: right; background: none; border: none; font-size: 18px; cursor: pointer; color: #721c24;">&times;</button>
            </div>
        `;
        document.body.appendChild(errorDiv);
    }
})();
