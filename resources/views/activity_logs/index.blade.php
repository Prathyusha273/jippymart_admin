@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Activity Logs</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">Activity Logs</li>
            </ol>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><i class="mdi mdi-history"></i></span>
                            <h3 class="mb-0">Activity Logs</h3>
                            <span class="counter ml-3" id="logs-count">0</span>
                            <span id="selected-count" class="badge badge-pill badge-primary ml-2 align-self-center" style="display: none;">0 selected</span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="select-box mr-3">
                                <select id="module-filter" class="form-control">
                                    <option value="">All Modules</option>
                                    <option value="foods">Foods</option>
                                    <option value="orders">Orders</option>
                                    <option value="users">Users/Customers</option>
                                    <option value="vendors">Owners/Vendors</option>
                                    <option value="drivers">Drivers</option>
                                    <option value="categories">Categories</option>
                                    <option value="restaurants">Restaurants</option>
                                    <option value="settings">Settings</option>
                                    <option value="coupons">Coupons</option>
                                    <option value="subscription_plans">Subscription Plans</option>
                                    <option value="notifications">Notifications</option>
                                    <option value="drivers">Drivers</option>
                                    <option value="customers">Customers</option>
                                    <option value="payments">Payments</option>
                                    <option value="reports">Reports</option>
                                    <option value="attributes">Attributes</option>
                                    <option value="documents">Documents</option>
                                    <option value="gift_cards">Gift Cards</option>
                                     <option value="promotions">Promotions</option>
                                     <option value="banner_items">Banner Items</option>
                                     <option value="cms_pages">CMS Pages</option>
                                     <option value="email_templates">Email Templates</option>
                                     <option value="on_boarding">On Boarding</option>
                                     <option value="media">Media</option>
                                     <option value="settings">Settings</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card border">
                    <div class="card-header d-flex justify-content-between align-items-center border-0">
                        <div class="card-header-title">
                            <h3 class="text-dark-2 mb-2 h4">Activity Logs</h3>
                            <p class="mb-0 text-dark-2">Track all user activities across the system</p>
                        </div>
                        <div class="card-header-right d-flex align-items-center">
                            <div class="card-header-btn mr-3">
                                <button class="btn btn-outline-primary rounded-full" id="refresh-logs">
                                    <i class="mdi mdi-refresh mr-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="activity-logs-table">
                                <thead>
                                    <tr>                                 
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Role</th>
                                        <th>Module</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody id="logs-tbody">
                                    <!-- Logs will be populated here -->
                                </tbody>
                            </table>
                        </div>
                        <div id="loading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2">Loading activity logs...</p>
                        </div>
                        <div id="no-logs" class="text-center py-4" style="display: none;">
                            <i class="mdi mdi-information-outline text-muted" style="font-size: 48px;"></i>
                            <p class="mt-2 text-muted">No activity logs found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for jQuery to be available
$(document).ready(function() {
    // Firebase configuration
    const firebaseConfig = {
        apiKey: "AIzaSyAf_lICoxPh8qKE1QnVkmQYTFJXKkYmRXU",
        authDomain: "jippymart-27c08.firebaseapp.com",
        projectId: "jippymart-27c08",
        storageBucket: "jippymart-27c08.firebasestorage.app",
        messagingSenderId: "592427852800",
        appId: "1:592427852800:web:f74df8ceb2a4b597d1a4e5",
        measurementId: "G-ZYBQYPZWCF"
    };

    // Initialize Firebase only if not already initialized
    let db;
    try {
        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        db = firebase.firestore();
        console.log('Firebase initialized successfully');
    } catch (error) {
        console.error('Firebase initialization error:', error);
        $('#loading').hide();
        $('#no-logs').show().html('<p class="text-danger">Error connecting to Firebase. Please check your configuration.</p>');
        return;
    }

    let currentModule = '';
    let logsListener = null;

    // Initialize with all logs
    loadActivityLogs();
    
    // Module filter change
    $('#module-filter').on('change', function() {
        currentModule = $(this).val();
        loadActivityLogs();
    });
    
    // Refresh button
    $('#refresh-logs').on('click', function() {
        loadActivityLogs();
    });

    function loadActivityLogs() {
        $('#loading').show();
        $('#no-logs').hide();
        $('#logs-tbody').empty();
        
        // Clear existing listener
        if (logsListener) {
            logsListener();
        }
        
        let query = db.collection('activity_logs').orderBy('created_at', 'desc').limit(100);
        
        if (currentModule) {
            query = query.where('module', '==', currentModule);
        }
        
        logsListener = query.onSnapshot(function(snapshot) {
            $('#loading').hide();
            
            if (snapshot.empty) {
                $('#no-logs').show();
                $('#logs-count').text('0');
                return;
            }
            
            $('#logs-tbody').empty();
            $('#logs-count').text(snapshot.docs.length);
            
            snapshot.docs.forEach(function(doc) {
                const data = doc.data();
                const timestamp = data.created_at ? new Date(data.created_at.toDate()).toLocaleString() : 'N/A';
                
                const row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <input type="checkbox" class="log-checkbox" value="${doc.id}">
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm mr-3">
                                    <div class="avatar-title bg-light rounded-circle">
                                        <i class="mdi mdi-account"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-weight-bold">${data.user_id}</div>
                                    <small class="text-muted">ID: ${data.user_id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-${getUserTypeBadge(data.user_type)}">${data.user_type}</span>
                        </td>
                        <td>
                            <span class="badge badge-info">${data.role}</span>
                        </td>
                        <td>
                            <span class="badge badge-secondary">${data.module}</span>
                        </td>
                        <td>
                            <span class="badge badge-${getActionBadge(data.action)}">${data.action}</span>
                        </td>
                        <td>
                            <div class="text-wrap" style="max-width: 300px;">
                                ${data.description}
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">${data.ip_address}</small>
                        </td>
                        <td>
                            <small class="text-muted">${timestamp}</small>
                        </td>
                    </tr>
                `;
                
                $('#logs-tbody').append(row);
            });
        }, function(error) {
            $('#loading').hide();
            console.error('Error loading activity logs:', error);
            $('#no-logs').show().html('<p class="text-danger">Error loading activity logs. Please try again.</p>');
        });
    }

    function getUserTypeBadge(userType) {
        switch(userType) {
            case 'admin': return 'primary';
            case 'merchant': return 'success';
            case 'driver': return 'warning';
            case 'customer': return 'info';
            default: return 'secondary';
        }
    }

    function getActionBadge(action) {
        switch(action) {
            case 'created': return 'success';
            case 'updated': return 'warning';
            case 'deleted': return 'danger';
            case 'viewed': return 'info';
            default: return 'secondary';
        }
    }

    // Global logActivity function is now available from global-activity-logger.js
    // No need to redefine it here

    // Handle select all functionality
    $('#select-all-logs').on('change', function() {
        $('.log-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkDeleteButton();
    });

    // Handle individual checkbox changes
    $(document).on('change', '.log-checkbox', function() {
        updateBulkDeleteButton();
        
        // Update select all checkbox
        var totalCheckboxes = $('.log-checkbox').length;
        var checkedCheckboxes = $('.log-checkbox:checked').length;
        
        if (checkedCheckboxes === 0) {
            $('#select-all-logs').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $('#select-all-logs').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#select-all-logs').prop('indeterminate', true);
        }
    });

    // Handle bulk delete
    $(document).on('click', '.bulk-delete-logs', function() {
        var selectedLogs = $('.log-checkbox:checked');
        
        if (selectedLogs.length === 0) {
            alert('Please select logs to delete');
            return;
        }
        
        if (confirm('Are you sure you want to delete ' + selectedLogs.length + ' selected log(s)?')) {
            var logIds = [];
            selectedLogs.each(function() {
                logIds.push($(this).val());
            });
            
            // Delete selected logs from Firestore
            deleteSelectedLogs(logIds);
        }
    });

    function updateBulkDeleteButton() {
        var selectedCount = $('.log-checkbox:checked').length;
        if (selectedCount > 0) {
            if (!$('.bulk-delete-logs').length) {
                $('.card-header-right').append(`
                    <button class="btn btn-danger rounded-full bulk-delete-logs">
                        <i class="fa fa-trash mr-2"></i>Delete Selected (${selectedCount})
                    </button>
                `);
            } else {
                $('.bulk-delete-logs').html(`<i class="fa fa-trash mr-2"></i>Delete Selected (${selectedCount})`);
            }
        } else {
            $('.bulk-delete-logs').remove();
        }
    }

    function deleteSelectedLogs(logIds) {
        // Show loading
        $('.bulk-delete-logs').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Deleting...');
        
        // Delete logs from Firestore
        var deletePromises = logIds.map(function(logId) {
            return db.collection('activity_logs').doc(logId).delete();
        });
        
        Promise.all(deletePromises).then(function() {
            // Refresh the logs
            loadActivityLogs();
            alert('Selected logs deleted successfully');
        }).catch(function(error) {
            console.error('Error deleting logs:', error);
            alert('Error deleting logs. Please try again.');
        }).finally(function() {
            $('.bulk-delete-logs').prop('disabled', false);
        });
    }
});
</script>
@endsection
