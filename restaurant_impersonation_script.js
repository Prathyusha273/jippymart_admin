/**
 * Simple Session-Based Impersonation Script for Restaurant Panel
 * 
 * This script checks for impersonation sessions and processes them automatically.
 * No URL parameters needed - everything is handled server-side.
 */

(function() {
    'use strict';
    
    console.log('🔍 Session-based impersonation script loaded');
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeImpersonation);
    } else {
        initializeImpersonation();
    }
    
    function initializeImpersonation() {
        console.log('🔍 Initializing session-based impersonation check...');
        
        // Check if there's an impersonation session
        checkImpersonationSession();
    }
    
    function checkImpersonationSession() {
        console.log('🔍 Checking for impersonation session...');
        
        // Get impersonation key from URL
        const urlParams = new URLSearchParams(window.location.search);
        const impersonationKey = urlParams.get('impersonation_key');
        
        if (!impersonationKey) {
            console.log('ℹ️ No impersonation key found in URL');
            return;
        }
        
        console.log('🔍 Impersonation key found:', impersonationKey);
        
        fetch('/api/check-impersonation?impersonation_key=' + encodeURIComponent(impersonationKey), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.has_impersonation) {
                console.log('✅ Impersonation session detected!');
                console.log('Restaurant UID:', data.restaurant_uid);
                console.log('Restaurant Name:', data.restaurant_name);
                
                // Show loading indicator
                showImpersonationLoading();
                
                // Process the impersonation
                processImpersonation(data.cache_key);
            } else {
                console.log('ℹ️ No valid impersonation session found');
            }
        })
        .catch(error => {
            console.error('❌ Error checking impersonation session:', error);
        });
    }
    
    function processImpersonation(cacheKey) {
        console.log('🚀 Processing impersonation...');
        
        fetch('/api/process-impersonation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCSRFToken()
            },
            body: JSON.stringify({
                cache_key: cacheKey
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('✅ Impersonation successful!');
                console.log('Restaurant:', data.restaurant_name);
                
                // Show success message
                showImpersonationSuccess(data.restaurant_name);
                
                // Reload the page to apply impersonation
                setTimeout(() => {
                    console.log('🔄 Reloading page to apply impersonation...');
                    window.location.reload();
                }, 2000);
            } else {
                console.error('❌ Impersonation failed:', data.message);
                showImpersonationError(data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error processing impersonation:', error);
            showImpersonationError('Error processing impersonation: ' + error.message);
        });
    }
    
    function getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }
    
    function showImpersonationLoading() {
        // Remove any existing loading indicator
        const existing = document.getElementById('impersonation-loading');
        if (existing) {
            existing.remove();
        }
        
        const loading = document.createElement('div');
        loading.id = 'impersonation-loading';
        loading.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; justify-content: center; align-items: center;">
                <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                    <div style="border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                    <h3 style="margin: 0 0 10px 0; color: #333;">🔐 Admin Impersonation</h3>
                    <p style="margin: 0; color: #666;">Processing impersonation...</p>
                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">Please wait while we authenticate you.</p>
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
    
    function showImpersonationSuccess(restaurantName) {
        // Remove loading indicator
        const loading = document.getElementById('impersonation-loading');
        if (loading) {
            loading.remove();
        }
        
        const success = document.createElement('div');
        success.id = 'impersonation-success';
        success.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 4px solid #28a745;">
                <strong>✅ Impersonation Successful!</strong><br>
                You are now logged in as <strong>${restaurantName}</strong>
                <button onclick="this.parentElement.parentElement.remove()" style="float: right; background: none; border: none; font-size: 18px; cursor: pointer; margin-left: 10px; color: #155724;">&times;</button>
            </div>
        `;
        document.body.appendChild(success);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (success.parentNode) {
                success.remove();
            }
        }, 5000);
    }
    
    function showImpersonationError(message) {
        // Remove loading indicator
        const loading = document.getElementById('impersonation-loading');
        if (loading) {
            loading.remove();
        }
        
        const error = document.createElement('div');
        error.id = 'impersonation-error';
        error.innerHTML = `
            <div style="position: fixed; top: 20px; right: 20px; background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 4px solid #dc3545;">
                <strong>❌ Impersonation Failed</strong><br>
                ${message}
                <button onclick="this.parentElement.parentElement.remove()" style="float: right; background: none; border: none; font-size: 18px; cursor: pointer; margin-left: 10px; color: #721c24;">&times;</button>
            </div>
        `;
        document.body.appendChild(error);
        
        // Auto-remove after 8 seconds
        setTimeout(() => {
            if (error.parentNode) {
                error.remove();
            }
        }, 8000);
    }
    
    // Add impersonation status indicator to the page
    function addImpersonationStatusIndicator() {
        // Check if user is currently impersonated
        fetch('/api/impersonation-status', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.is_impersonated) {
                const indicator = document.createElement('div');
                indicator.id = 'impersonation-indicator';
                indicator.innerHTML = `
                    <div style="position: fixed; top: 0; left: 0; right: 0; background: #fff3cd; color: #856404; padding: 10px; text-align: center; z-index: 1000; border-bottom: 1px solid #ffeaa7; font-size: 14px;">
                        <strong>🔐 Admin Impersonation Active</strong> - You are logged in as <strong>${data.restaurant_name}</strong> for support purposes.
                        <button onclick="endImpersonation()" style="margin-left: 15px; background: #856404; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 12px;">End Impersonation</button>
                    </div>
                `;
                document.body.insertBefore(indicator, document.body.firstChild);
                
                // Adjust body padding to account for the indicator
                document.body.style.paddingTop = '50px';
            }
        })
        .catch(error => {
            console.error('Error checking impersonation status:', error);
        });
    }
    
    // Function to end impersonation
    window.endImpersonation = function() {
        fetch('/api/end-impersonation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCSRFToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Impersonation ended successfully');
                // Reload the page
                window.location.reload();
            } else {
                console.error('❌ Error ending impersonation:', data.message);
                alert('Error ending impersonation: ' + data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error ending impersonation:', error);
            alert('Error ending impersonation: ' + error.message);
        });
    };
    
    // Add status indicator after a short delay
    setTimeout(addImpersonationStatusIndicator, 1000);
    
})();
