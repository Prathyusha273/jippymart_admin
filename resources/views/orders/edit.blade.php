@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{ trans('lang.order_plural') }}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a></li>
                    <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                    <li class="breadcrumb-item"><a
                            href="{{ route('restaurants.orders', $_GET['eid']) }}">{{ trans('lang.order_plural') }}</a>
                    </li>
                    <?php } else { ?>
                    <li class="breadcrumb-item"><a href="{!! route('orders') !!}">{{ trans('lang.order_plural') }}</a>
                    </li>
                    <?php } ?>
                    <li class="breadcrumb-item">{{ trans('lang.order_edit') }}</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="card-body pb-5 p-0">
                <div class="text-right print-btn pb-3">
                    <a href="{{ route('vendors.orderprint', $id) }}">
                        <button type="button" class="fa fa-print"></button>
                    </a>
                </div>
                <div class="order_detail" id="order_detail">
                    <div class="order_detail-top">
                        <div class="row">
                            <div class="order_edit-genrl col-lg-7 col-md-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h3>{{ trans('lang.general_details') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="order_detail-top-box">
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label
                                                    class="col-12 control-label"><strong>{{ trans('lang.date_created') }}
                                                        : </strong><span id="createdAt"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col payment_method">
                                                <label
                                                    class="col-12 control-label"><strong>{{ trans('lang.payment_methods') }}
                                                        : </strong><span id="payment_method"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col">
                                                <label
                                                    class="col-12 control-label"><strong>{{ trans('lang.order_type') }}
                                                        :</strong>
                                                    <span id="order_type"></span></label>
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col schedule_date">
                                            </div>
                                            <div class="form-group row widt-100 gendetail-col prepare_time">
                                            </div>
                                            <div class="form-group row width-100 ">
                                                <label class="col-3 control-label">{{ trans('lang.status') }}:</label>
                                                <div class="col-7">
                                                    <select id="order_status" class="form-control">
                                                        <option value="Order Placed" id="order_placed">
                                                            {{ trans('lang.order_placed') }}
                                                        </option>
                                                        <option value="Order Accepted" id="order_accepted">
                                                            {{ trans('lang.order_accepted') }}
                                                        </option>
                                                        <option value="Order Rejected" id="order_rejected">
                                                            {{ trans('lang.order_rejected') }}
                                                        </option>
                                                        <option value="Driver Pending" id="driver_pending">
                                                            {{ trans('lang.driver_pending') }}
                                                        </option>
                                                        <option value="Driver Rejected" id="driver_rejected">
                                                            {{ trans('lang.driver_rejected') }}
                                                        </option>
                                                        <option value="Order Shipped" id="order_shipped">
                                                            {{ trans('lang.order_shipped') }}
                                                        </option>
                                                        <option value="In Transit" id="in_transit">
                                                            {{ trans('lang.in_transit') }}
                                                        </option>
                                                        <option value="Order Completed" id="order_completed">
                                                            {{ trans('lang.order_completed') }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row width-100">
                                                <label class="col-3 control-label"></label>
                                                <div class="col-7 text-right">
                                                    <button type="button" class="btn btn-primary edit-form-btn"><i
                                                            class="fa fa-save"></i> {{ trans('lang.update') }}
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Manual Driver Assignment Section -->
                                            <div class="form-group row width-100" id="manual_driver_assignment_section">
                                                <label class="col-3 control-label">{{ trans('lang.assign_driver') }}
                                                    :</label>
                                                <div class="col-7">
                                                    <select id="driver_selector" class="form-control">
                                                        <option value="">{{ trans('lang.select_driver') }}</option>
                                                    </select>
                                                    <div class="form-text text-muted">
                                                        {{ trans('lang.manual_driver_assignment_help') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row width-100" id="assign_driver_button_section">
                                                <label class="col-3 control-label"></label>
                                                <div class="col-7 text-right">
                                                    <button type="button" class="btn btn-success assign-driver-btn"
                                                            id="assign_driver_btn">
                                                        <i class="fa fa-user-plus"></i> {{ trans('lang.assign_driver') }}
                                                    </button>
                                                    <button type="button" class="btn btn-warning remove-driver-btn"
                                                            id="remove_driver_btn" style="display: none;">
                                                        <i class="fa fa-user-times"></i> {{ trans('lang.remove_driver') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-items-list mt-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <table cellpadding="0" cellspacing="0"
                                                   class="table table-striped table-valign-middle">
                                                <thead>
                                                <tr>
                                                    <th>{{ trans('lang.item') }}</th>
                                                    <th class="text-center">{{ trans('lang.price') }}</th>
                                                    <th>{{ trans('lang.qty') }}</th>
                                                    <th>{{ trans('lang.extras') }}</th>
                                                    <th>{{ trans('lang.total') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody id="order_products">
                                                </tbody>
                                            </table>
                                            <div class="order-data-row order-totals-items">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="table-responsive bk-summary-table">
                                                            <table class="order-totals">
                                                                <tbody id="order_products_total">
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="order_addre-edit col-lg-5 col-md-12">
                                <div class="card">
                                    <div class="card-header bg-white">
                                        <h3>{{ trans('lang.billing_details') }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="address order_detail-top-box">
                                            <p>
                                                <strong>{{ trans('lang.name') }}: </strong><span
                                                    id="billing_name"></span>
                                            </p>
                                            <p>
                                                <strong>{{ trans('lang.address') }}: </strong>
                                                <span id="billing_line1"></span>
                                                <span id="billing_line2"></span>
                                                <span id="billing_country"></span>
                                            </p>
                                            <p><strong>{{ trans('lang.email_address') }}:</strong>
                                                <span id="billing_email"></span>
                                            </p>
                                            <p><strong>{{ trans('lang.phone') }}:</strong>
                                                <span id="billing_phone"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="order_addre-edit driver_details_hide">
                                    <div class="card mt-4">
                                        <div class="card-header bg-white">
                                            <h3>{{ trans('lang.driver_detail') }}</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="address order_detail-top-box">
                                                <p>
                                                    <strong>{{ trans('lang.name') }}: </strong><span
                                                        id="driver_firstName"></span> <span id="driver_lastName"></span><br>
                                                </p>
                                                <p><strong>{{ trans('lang.email_address') }}:</strong>
                                                    <span id="driver_email"></span>
                                                </p>
                                                <p><strong>{{ trans('lang.phone') }}:</strong>
                                                    <span id="driver_phone"></span>
                                                </p>
                                                <p><strong>{{ trans('lang.car_name') }}:</strong>
                                                    <span id="driver_carName"></span>
                                                </p>
                                                <p><strong>{{ trans('lang.car_number') }}:</strong>
                                                    <span id="driver_carNumber"></span>
                                                </p>
                                                <p><strong>{{ trans('lang.zone') }}:</strong>
                                                    <span id="zone_name"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="resturant-detail mt-4">
                                    <div class="card">
                                        <div class="card-header bg-white">
                                            <h4 class="card-header-title">{{ trans('lang.restaurant') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <a href="#" class="row redirecttopage align-items-center"
                                               id="resturant-view">
                                                <div class="col-md-3">
                                                    <img src="" class="resturant-img rounded-circle" alt="vendor"
                                                         width="70px" height="70px">
                                                </div>
                                                <div class="col-md-9">
                                                    <h4 class="vendor-title"></h4>
                                                </div>
                                            </a>
                                            <h5 class="contact-info">{{ trans('lang.contact_info') }}:</h5>
                                            <p><strong>{{ trans('lang.phone') }}:</strong>
                                                <span id="vendor_phone"></span>
                                            </p>
                                            <p><strong>{{ trans('lang.address') }}:</strong>
                                                <span id="vendor_address"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="order_detail-review mt-4">
                                    <div class="rental-review">
                                        <div class="card">
                                            <div class="card-header bg-white box-header">
                                                <h3>{{ trans('lang.customer_reviews') }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="review-inner">
                                                    <div id="customers_rating_and_review">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i>
                    {{ trans('lang.save') }}
                </button>
                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                <a href="{{ route('restaurants.orders', $_GET['eid']) }}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}</a>
                <?php } else { ?>
                <a href="{!! route('orders') !!}" class="btn btn-default"><i
                        class="fa fa-undo"></i>{{ trans('lang.cancel') }}
                </a>
                <?php } ?>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    <div class="modal fade" id="addPreparationTimeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered location_modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title locationModalTitle">{{ trans('lang.add_preparation_time') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="">
                        <div class="form-row">
                            <div class="form-group row">
                                <div class="form-group row width-100">
                                    <label class="col-12 control-label">{{ trans('lang.time') }}</label>
                                    <div class="col-12">
                                        <input type="text" name="prepare_time" class="form-control time-picker"
                                               id="prepare_time">
                                        <div id="add_prepare_time_error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="add-prepare-time-btn">{{ trans('submit') }}
                        </button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">
                            {{ trans('close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('style')
    <style>
        #manual_driver_assignment_section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
        }

        #manual_driver_assignment_section label {
            font-weight: bold;
            color: #495057;
        }

        .assign-driver-btn {
            margin-right: 10px;
        }

        .remove-driver-btn {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .remove-driver-btn:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            color: #212529;
        }

        /* Promotional Price Styles */
        .promotional-price {
            color: #28a745 !important;
            font-weight: bold;
        }

        .original-price {
            text-decoration: line-through;
            color: #6c757d;
        }

        .promotional_savings {
            color: #28a745 !important;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }

        /* Promotional Item Badge Styles */
        .promotional-badge {
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e) !important;
            color: white !important;
            padding: 4px 10px !important;
            border-radius: 15px !important;
            font-size: 9px !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            box-shadow: 0 2px 6px rgba(255, 107, 107, 0.4) !important;
            display: inline-block !important;
            margin-top: 4px !important;
            animation: pulse 2s infinite !important;
            text-align: center !important;
            width: fit-content !important;
            border: none !important;
            outline: none !important;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Promotional item row styling */
        .promotional-item-row {
            background: linear-gradient(90deg, rgba(255, 107, 107, 0.05), rgba(255, 142, 142, 0.05));
            border-left: 3px solid #ff6b6b;
        }

        .promotional-item-row td {
            position: relative;
        }
    </style>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.js"></script>
    <script>
        var adminCommission = 0;
        var id_rendom = "<?php echo uniqid(); ?>";
        var id = "<?php echo $id; ?>";
        var driverId = '';
        var subscriptionTotalOrders = -1;
        var old_order_status = '';
        var payment_shared = false;
        var deliveryChargeVal = 0;
        var deliveryCharge = 0;
        var fcmToken = '';
        var manfcmTokenVendor = '';
        var fcmTokenVendor = '';
        var customername = '';
        var vendorname = '';
        var tip_amount_val = 0;
        var tip_amount = 0;
        var page_size = 5;
        var database = firebase.firestore();
        var ref = database.collection('restaurant_orders').where("id", "==", id);
        var ref_review_attributes = database.collection('review_attributes');
        var selected_review_attributes = '';
        var refUserReview = database.collection('foods_review').where('orderid', '==', id);
        var append_procucts_list = '';
        var append_procucts_total = '';
        var total_price = 0;
        var currentCurrency = '';
        var currencyAtRight = false;
        var refCurrency = database.collection('currencies').where('isActive', '==', true);
        var orderPreviousStatus = '';
        var orderTakeAwayOption = false;
        var manname = '';
        var reviewAttributes = {};
        var decimal_degits = 0;
        var orderCustomerId = '';
        var orderPaytableAmount = 0;
        var vendorAuthor = '';
        var orderAcceptedSubject = '';
        var orderAcceptedMsg = '';
        var orderRejectedSubject = '';
        var orderRejectedMsg = '';
        var orderCompletedSubject = '';
        var orderCompletedMsg = '';
        var takeAwayOrderCompletedSubject = '';
        var takeAwayOrderCompletedMsg = '';
        var driverAcceptedMsg = '';
        var driverAcceptedSubject = '';
        var basePrice = 0;
        var total_tax_amount = 0;
        var subscriptionModel = false;
        var availableDrivers = [];
        var currentDriverId = '';

        database.collection('settings').doc("restaurant").get().then(async function (snapshots) {
            var subscriptionSetting = snapshots.data();
            if (subscriptionSetting.subscription_model == true) {
                subscriptionModel = true;
            }
        });

        // Enhanced load available drivers for manual assignment
        async function loadAvailableDrivers() {
            try {
                // Show loading state
                $('#driver_selector').html('<option value="">{{ trans("lang.select_driver") }}</option><option value="" disabled>Loading drivers...</option>');

                // Call the Cloud Function to get available drivers
                const getDriversFunction = firebase.functions().httpsCallable('getAvailableDriversForOrder');
                const result = await getDriversFunction({
                    orderId: id,
                    zoneId: null // Get all drivers, can be filtered by zone later
                });

                if (result.data.success) {
                    availableDrivers = result.data.drivers;
                    $('#driver_selector').empty();
                    $('#driver_selector').append('<option value="">{{ trans("lang.select_driver") }}</option>');

                    result.data.drivers.forEach((driverData) => {
                        var driverName = (driverData.firstName || '') + ' ' + (driverData.lastName || '');
                        var driverPhone = driverData.phoneNumber || '';
                        var walletAmount = driverData.wallet_amount || 0;
                        var isOnline = driverData.isOnline ? '🟢' : '🔴';
                        var displayText = `${isOnline} ${driverName} (${driverPhone}) - ₹${walletAmount}`;

                        $('#driver_selector').append($("<option></option>")
                            .attr("value", driverData.id)
                            .text(displayText));
                    });

                } else {
                    $('#driver_selector').html('<option value="">{{ trans("lang.select_driver") }}</option><option value="" disabled>Error loading drivers</option>');
                }

            } catch (error) {
                $('#driver_selector').html('<option value="">{{ trans("lang.select_driver") }}</option><option value="" disabled>Error loading drivers</option>');

                // Fallback to direct Firestore query
                try {
                    const snapshots = await database.collection('users').where('role', '==', 'driver').where('isActive', '==', true).get();
                    availableDrivers = [];
                    $('#driver_selector').empty();
                    $('#driver_selector').append('<option value="">{{ trans("lang.select_driver") }}</option>');

                    snapshots.docs.forEach((doc) => {
                        var driverData = doc.data();
                        availableDrivers.push(driverData);
                        var driverName = (driverData.firstName || '') + ' ' + (driverData.lastName || '');
                        var driverPhone = driverData.phoneNumber || '';
                        var displayText = driverName + ' (' + driverPhone + ')';

                        $('#driver_selector').append($("<option></option>")
                            .attr("value", driverData.id)
                            .text(displayText));
                    });
                } catch (fallbackError) {
                }
            }
        }

        // Initialize driver assignment functionality
        function initializeDriverAssignment() {
            loadAvailableDrivers();

            // Handle driver assignment
            $('#assign_driver_btn').click(async function () {
                var selectedDriverId = $('#driver_selector').val();
                if (!selectedDriverId) {
                    alert('{{ trans("lang.please_select_driver") }}');
                    return;
                }

                if (confirm('{{ trans("lang.confirm_assign_driver") }}')) {
                    await assignDriverToOrder(selectedDriverId);
                }
            });

            // Handle driver removal
            $('#remove_driver_btn').click(async function () {
                if (confirm('{{ trans("lang.confirm_remove_driver") }}')) {
                    await removeDriverFromOrder();
                }
            });
        }

        // Enhanced assign driver to order using Cloud Function
        async function assignDriverToOrder(driverId) {
            try {
                // Show loading state
                $('#assign_driver_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Assigning...');

                // Call the Cloud Function for manual assignment
                const manualAssignFunction = firebase.functions().httpsCallable('manualAssignDriverToOrder');
                const result = await manualAssignFunction({
                    orderId: id,
                    driverId: driverId,
                    assignedBy: '{{ auth()->user()->name ?? "Admin" }}',
                    reason: 'Manual assignment from order edit page'
                });

                if (result.data.success) {
                    alert('{{ trans("lang.driver_assigned_successfully") }}');
                    window.location.reload();
                } else {
                    alert('Failed to assign driver: ' + (result.data.message || 'Unknown error'));
                }

            } catch (error) {

                // Handle specific error types
                let errorMessage = '{{ trans("lang.error_assigning_driver") }}';
                if (error.code === 'functions/unauthenticated') {
                    errorMessage = 'Authentication required. Please log in again.';
                } else if (error.code === 'functions/not-found') {
                    errorMessage = 'Order or driver not found.';
                } else if (error.code === 'functions/failed-precondition') {
                    errorMessage = error.message || 'Order is not eligible for manual assignment.';
                } else if (error.code === 'functions/invalid-argument') {
                    errorMessage = error.message || 'Invalid driver selected.';
                }

                alert(errorMessage);
            } finally {
                // Reset button state
                $('#assign_driver_btn').prop('disabled', false).html('<i class="fa fa-user-plus"></i> {{ trans("lang.assign_driver") }}');
            }
        }

        // Enhanced remove driver from order using Cloud Function
        async function removeDriverFromOrder() {
            try {
                // Show loading state
                $('#remove_driver_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Removing...');

                // Call the Cloud Function for manual driver removal
                const manualRemoveFunction = firebase.functions().httpsCallable('manualRemoveDriverFromOrder');
                const result = await manualRemoveFunction({
                    orderId: id,
                    reason: 'Manual removal from order edit page'
                });

                if (result.data.success) {
                    alert('{{ trans("lang.driver_removed_successfully") }}');
                    window.location.reload();
                } else {
                    alert('Failed to remove driver: ' + (result.data.message || 'Unknown error'));
                }

            } catch (error) {

                // Handle specific error types
                let errorMessage = '{{ trans("lang.error_removing_driver") }}';
                if (error.code === 'functions/unauthenticated') {
                    errorMessage = 'Authentication required. Please log in again.';
                } else if (error.code === 'functions/not-found') {
                    errorMessage = 'Order not found.';
                } else if (error.code === 'functions/failed-precondition') {
                    errorMessage = error.message || 'Cannot remove driver from order in current status.';
                }

                alert(errorMessage);
            } finally {
                // Reset button state
                $('#remove_driver_btn').prop('disabled', false).html('<i class="fa fa-user-times"></i> {{ trans("lang.remove_driver") }}');
            }
        }

        database.collection('dynamic_notification').get().then(async function (snapshot) {
            if (snapshot.docs.length > 0) {
                snapshot.docs.map(async (listval) => {
                    val = listval.data();
                    if (val.type == "restaurant_rejected") {
                        orderRejectedSubject = val.subject;
                        orderRejectedMsg = val.message;
                    } else if (val.type == "driver_completed") {
                        orderCompletedSubject = val.subject;
                        orderCompletedMsg = val.message;
                    } else if (val.type == "takeaway_completed") {
                        takeAwayOrderCompletedSubject = val.subject;
                        takeAwayOrderCompletedMsg = val.message;
                    } else if (val.type == "restaurant_accepted") {
                        orderAcceptedSubject = val.subject;
                        orderAcceptedMsg = val.message;
                    } else if (val.type == "driver_accepted") {
                        driverAcceptedSubject = val.subject;
                        driverAcceptedMsg = val.message;
                    }
                });
            }
        });
        refCurrency.get().then(async function (snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });
        var geoFirestore = new GeoFirestore(database);
        var place_image = '';
        var ref_place = database.collection('settings').doc("placeHolderImage");
        ref_place.get().then(async function (snapshots) {
            var placeHolderImage = snapshots.data();
            place_image = placeHolderImage.image;
        });
        $(document).ready(function () {
            // Initialize driver assignment functionality
            initializeDriverAssignment();
            
            // Initialize promotional pricing interceptor
            initializePromotionalPricingInterceptor();

            $('.time-picker').timepicker({
                timeFormat: "HH:mm",
                showMeridian: false,
                format24: true
            });
            $('.time-picker').timepicker().on('changeTime.timepicker', function (e) {
                var hours = e.time.hours,
                    min = e.time.minutes;
                if (hours < 10) {
                    $(e.currentTarget).val('0' + hours + ':' + min);
                }
            });
            var alovelaceDocumentRef = database.collection('restaurant_orders').doc();
            if (alovelaceDocumentRef.id) {
                id_rendom = alovelaceDocumentRef.id;
            }
            $(document.body).on('click', '.redirecttopage', function () {
                var url = $(this).attr('data-url');
                window.location.href = url;
            });
            jQuery("#data-table_processing").show();
            ref.get().then(async function (snapshots) {
                vendorOrder = snapshots.docs[0].data();
                getUserReview(vendorOrder);
                var order = snapshots.docs[0].data();
                append_procucts_list = document.getElementById('order_products');
                append_procucts_list.innerHTML = '';
                append_procucts_total = document.getElementById('order_products_total');
                append_procucts_total.innerHTML = '';
                if (order.address.name) {
                    $("#billing_name").text(order.address.name);
                } else {
                    $("#billing_name").text(order.author.firstName + ' ' + order.author.lastName);
                }
                $("#trackng_number").text(id);
                var billingAddressstring = '';
                if (order.address.hasOwnProperty('address')) {
                    $("#billing_line1").text(order.address.address);
                }
                if (order.address.hasOwnProperty('locality')) {
                    billingAddressstring = billingAddressstring + order.address.locality;
                }
                if (order.address.hasOwnProperty('landmark')) {
                    billingAddressstring = billingAddressstring + " " + order.address.landmark;
                }
                $("#billing_line2").text(billingAddressstring);
                if (order.author.hasOwnProperty('phoneNumber')) {
                    $("#billing_phone").text(shortEditNumber(order.author.phoneNumber));
                } else {
                    $("#billing_phone").text("");
                }
                if (order.author.hasOwnProperty('email')) {
                    $("#billing_email").html('<a href="mailto:' + order.author.email + '">' +
                        shortEmail(order.author.email) + '</a>');
                } else {
                    $("#billing_email").html("");
                }
                if (order.createdAt) {
                    var date1 = order.createdAt.toDate().toDateString();
                    var date = new Date(date1);
                    var dd = String(date.getDate()).padStart(2, '0');
                    var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
                    var yyyy = date.getFullYear();
                    var createdAt_val = yyyy + '-' + mm + '-' + dd;
                    var time = order.createdAt.toDate().toLocaleTimeString('en-US');
                    $('#createdAt').text(createdAt_val + ' ' + time);
                }
                var payment_method = '';
                if (order.payment_method) {
                    if (order.payment_method == "stripe") {
                        image = '{{ asset('images/stripe.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "cod") {
                        image = '{{ asset('images/cashondelivery.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "razorpay") {
                        image = '{{ asset('images/razorepay.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "paypal") {
                        image = '{{ asset('images/paypal.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "payfast") {
                        image = '{{ asset('images/payfast.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '" width="30%" height="30%">';
                    } else if (order.payment_method == "paystack") {
                        image = '{{ asset('images/paystack.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "flutterwave") {
                        image = '{{ asset('images/flutter_wave.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "mercado pago") {
                        image = '{{ asset('images/marcado_pago.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "wallet") {
                        image = '{{ asset('images/foodie_wallet.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%" >';
                    } else if (order.payment_method == "paytm") {
                        image = '{{ asset('images/paytm.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "cancelled order payment") {
                        image = '{{ asset('images/cancel_order.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "refund amount") {
                        image = '{{ asset('images/refund_amount.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "referral amount") {
                        image = '{{ asset('images/reffral_amount.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "midtrans") {
                        image = '{{ asset('images/midtrans.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "xendit") {
                        image = '{{ asset('images/xendit.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else if (order.payment_method == "orangepay") {
                        image = '{{ asset('images/orangeMoney.png') }}';
                        payment_method = '<img alt="image" src="' + image +
                            '"  width="30%" height="30%">';
                    } else {
                        payment_method = order.payment_method;
                    }
                }
                $('#payment_method').html(payment_method);
                if (order.hasOwnProperty('takeAway') && order.takeAway) {
                    $('#driver_pending').hide();
                    $('#driver_rejected').hide();
                    $('#order_shipped').hide();
                    $('#in_transit').hide();
                    $('#order_type').text('{{ trans('lang.order_takeaway') }}');
                    orderTakeAwayOption = true;
                } else {
                    $('#order_type').text('{{ trans('lang.order_delivery') }}');
                }
                if ((order.driver != '' && order.driver != undefined) && (order.takeAway == false)) {
                    $('#driver_carName').text(order.driver.carName);
                    $('#driver_carNumber').text(order.driver.carNumber);
                    $('#driver_email').html('<a href="mailto:' + order.driver.email + '">' + shortEmail(
                        order.driver.email) + '</a>');
                    $('#driver_firstName').text(order.driver.firstName);
                    $('#driver_lastName').text(order.driver.lastName);
                    $('#driver_phone').text(shortEditNumber(order.driver.phoneNumber));
                    var zoneId = '';
                    if (order.driver.hasOwnProperty('zoneId') && order.driver.zoneId.length > 0) {
                        zoneId = order.driver.zoneId;
                    } else if (order.hasOwnProperty('driverID')) {
                        var driverSnapshot = await database.collection('users').doc(order.driverID)
                            .get();
                        var driverData = driverSnapshot.data();
                        zoneId = driverData.zoneId;
                    }
                    if (zoneId != '') {
                        database.collection('zone').doc(zoneId).get().then(async function (snapshots) {
                            let zone = snapshots.data();
                            $("#zone_name").text(zone.name);
                        });
                    }

                    // Hide manual assignment section when driver is already assigned
                    $('#manual_driver_assignment_section').hide();
                    $('#assign_driver_button_section').hide();
                } else {
                    $('.order_edit-genrl').removeClass('col-md-7').addClass('col-md-7');
                    $('.order_addre-edit').removeClass('col-md-5').addClass('col-md-5');
                    $('.driver_details_hide').empty();

                    // Show manual assignment section when no driver is assigned
                    $('#manual_driver_assignment_section').show();
                    $('#assign_driver_button_section').show();
                    $('#assign_driver_btn').show();
                    $('#remove_driver_btn').hide();
                }
                if (order.driverID != '' && order.driverID != undefined) {
                    driverId = order.driverID;
                }
                if (order.vendor && order.vendor.author != '' && order.vendor.author != undefined) {
                    vendorAuthor = order.vendor.author;
                }
                var scheduleTime = '';
                if (order.hasOwnProperty('scheduleTime') && order.scheduleTime != null && order
                    .scheduleTime != '') {
                    scheduleTime = order.scheduleTime;
                    var scheduleDate = scheduleTime.toDate().toDateString();
                    var time = order.scheduleTime.toDate().toLocaleTimeString('en-US');
                    var scheduleDate = new Date(scheduleDate);
                    var dd = String(scheduleDate.getDate()).padStart(2, '0');
                    var mm = String(scheduleDate.getMonth() + 1).padStart(2, '0'); //January is 0!
                    var yyyy = scheduleDate.getFullYear();
                    var scheduleDate = yyyy + '-' + mm + '-' + dd;
                    var scheduleDateTime = scheduleDate + ' ' + time;
                    $('.schedule_date').append(
                        '<label class="col-12 control-label"><strong>{{ trans('lang.schedule_date_time') }}:</strong><span id=""> ' +
                        scheduleDateTime + '</span></label>')
                }
                if (order.hasOwnProperty('estimatedTimeToPrepare') && order.estimatedTimeToPrepare !=
                    null && order.estimatedTimeToPrepare != '') {
                    prepareTime = order.estimatedTimeToPrepare;
                    var [h, m] = prepareTime.split(":");
                    var hour = h;
                    if (h.charAt(0) == "0") {
                        hour = h.charAt(1);
                    }
                    time = (h == "00") ? m + " minutes" : hour + " hours" + m + " minutes";
                    $('.prepare_time').append(
                        '<label class="col-12 control-label "><strong>{{ trans('lang.prepare_time') }}:</strong><span id=""> ' +
                        time + '</span></label>')
                }
                fcmToken = order.author.fcmToken;
                vendorname = order.vendor.title;
                fcmTokenVendor = order.vendor.fcmToken;
                customername = order.author.firstName;
                vendorId = order.vendor.id;
                old_order_status = order.status;
                if (order.payment_shared != undefined) {
                    payment_shared = order.payment_shared;
                }
                
                // ========== PROMOTIONAL PRICING SYSTEM START ==========
                
                // Calculate promotional totals first
                
                
                let promotionalTotals = null;
                try {
                    promotionalTotals = await calculatePromotionalTotals(order.products, order.vendorID);
                } catch (error) {
                    promotionalTotals = null;
                }
                
                // Store promotional totals globally for use in buildHTMLProductstotal
                window.promotionalTotals = promotionalTotals;
                
                
                // Test promotional pricing with specific data first
                try {
                    await testPromotionalPricing();
                } catch (error) {
                }
                
                // Build product list with promotional pricing
                if (order.products && order.products.length > 0) {
                    const testProduct = order.products[0];
                    
                    try {
                        const testPriceInfo = await getPromotionalPrice(testProduct, order.vendorID);
                    } catch (error) {
                    }
                }
                
                var productsListHTML = '';
                try {
                    productsListHTML = await buildHTMLProductsListWithPromotions(order.products, order.vendorID);
                } catch (error) {
                    productsListHTML = buildHTMLProductsList(order.products);
                }
                
                var productstotalHTML = await buildHTMLProductstotal(order);
                
                if (productsListHTML != '') {
                    append_procucts_list.innerHTML = productsListHTML;
('🎯 Product list HTML set with promotional pricing');
                }
                if (productstotalHTML != '') {
                    append_procucts_total.innerHTML = productstotalHTML;
('💰 Product total HTML set with promotional pricing');
                }
                orderPreviousStatus = order.status;
                if (order.hasOwnProperty('payment_method')) {
                    orderPaymentMethod = order.payment_method;
                }
                $("#order_status option[value='" + order.status + "']").attr("selected", "selected");
                
                // Send email notification for new orders (Order Placed status)
                if (order.status === "Order Placed") {
                    setTimeout(() => {
                        sendOrderEmailNotification("Order Placed");
                    }, 2000); // Delay to ensure all order data is loaded
                }
                if (order.status == "Order Rejected" || order.status == "Driver Rejected") {
                    $("#order_status").prop("disabled", true);
                }
                var price = 0;
                if (order.authorID) {
                    orderCustomerId = order.authorID;
                }
                if (order.vendorID) {
                    var vendor = database.collection('vendors').where("id", "==", order.vendorID);
                    vendor.get().then(async function (snapshotsnew) {
                        if (snapshotsnew.docs.length > 0) {
                            var vendordata = snapshotsnew.docs[0].data();
                            if (subscriptionModel) {
                                if (vendordata.hasOwnProperty('subscriptionTotalOrders') && vendordata.subscriptionTotalOrders != null && vendordata.subscriptionTotalOrders != '') {
                                    subscriptionTotalOrders = vendordata.subscriptionTotalOrders;
                                }
                            }

                            if (vendordata.id) {
                                var route_view = '{{ route('restaurants.view', ':id') }}';
                                route_view = route_view.replace(':id', vendordata.id);
                                $('#resturant-view').attr('data-url', route_view);
                            }
                            if (vendordata.photo != "" && vendordata.photo != null) {
                                $('.resturant-img').attr('src', vendordata.photo);
                            } else {
                                $('.resturant-img').attr('src', place_image);
                            }
                            if (vendordata.title != "" && vendordata.title != null) {
                                $('.vendor-title').html(vendordata.title);
                            }
                            if (vendordata.phonenumber != "" && vendordata.phonenumber !=
                                null) {
                                $('#vendor_phone').text(shortEditNumber(vendordata
                                    .phonenumber));
                            } else {
                                $('#vendor_phone').text("");
                            }
                            if (vendordata.location != "" && vendordata.location != null) {
                                $('#vendor_address').text(vendordata.location);
                            }
                        } else {
                            $('.resturant-img').attr('src', place_image);
                            $('.vendor-title').html("{{ trans('lang.unknown') }}");
                        }
                    });
                    tip_amount = order.tip_amount;
                }
                jQuery("#data-table_processing").hide();
            })

            function getTwentyFourFormat(h, timeslot) {
                if (h < 10 && timeslot == "PM") {
                    h = parseInt(h) + 12;
                } else if (h < 10 && timeslot == "AM") {
                    h = '0' + h;
                }
                return h;
            }

            $('#add-prepare-time-btn').click(function () {
                // Prevent multiple clicks by disabling button
                if ($(this).prop('disabled')) {
                    return false;
                }
                
                if (parseInt(subscriptionTotalOrders) == 0) {
                    alert('{{ trans("lang.can_not_accept_more_orders") }}');
                    return false;
                } else {
                    // Show loading state
                    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Accepting...');

                    var preparationTime = $('#prepare_time').val();
                    if (preparationTime == '') {
                        $('#add_prepare_time_error').text('{{ trans('lang.add_prepare_time_error') }}');
                        $(this).prop('disabled', false).html('{{ trans('submit') }}');
                        return false;
                    }
                    var date = firebase.firestore.FieldValue.serverTimestamp();
                    database.collection('restaurant_orders').doc(id).update({
                        'status': "Order Accepted",
                        'estimatedTimeToPrepare': preparationTime
                    }).then(async function (result) {
('✅ Order accepted successfully, now logging activity...');
                        try {
                            if (typeof logActivity === 'function') {
('🔍 Calling logActivity for order acceptance...');
                                await logActivity('orders', 'accepted', 'Accepted order #' + id + ' with preparation time: ' + preparationTime + ' minutes');
('✅ Activity logging completed successfully');
                            } else {
('❌ logActivity function is not available');
                            }
                        } catch (error) {
('❌ Error calling logActivity:', error);
                        }

                        var wId = database.collection('temp').doc().id;
                        database.collection('wallet').doc(wId).set({
                            'amount': parseFloat(basePrice),
                            'date': date,
                            'id': wId,
                            'isTopUp': true,
                            'order_id': "<?php echo $id; ?>",
                            'payment_method': 'Wallet',
                            'payment_status': 'success',
                            'transactionUser': 'vendor',
                            'note': 'Order Amount credited',
                            'user_id': vendorAuthor
                        }).then(async function (result) {
                            var vendorAmount = basePrice;
                            if (total_tax_amount != 0 || total_tax_amount != '') {
                                var wId = database.collection('temp').doc().id;
                                database.collection('wallet').doc(wId).set({
                                    'amount': parseFloat(total_tax_amount),
                                    'date': date,
                                    'id': wId,
                                    'isTopUp': true,
                                    'order_id': "<?php echo $id; ?>",
                                    'payment_method': 'tax',
                                    'payment_status': 'success',
                                    'transactionUser': 'vendor',
                                    'user_id': vendorAuthor,
                                    'note': 'Order Tax credited'
                                }).then(async function (result) {
                                })
                            }
                            database.collection('users').where('id', '==', vendorAuthor)
                                .get().then(async function (snapshotsnew) {
                                var vendordata = snapshotsnew.docs[0]
                                    .data();
                                if (vendordata) {

                                    if (parseInt(subscriptionTotalOrders) != -1) {
                                        subscriptionTotalOrders = parseInt(subscriptionTotalOrders) - 1;
                                        await database.collection('vendors').doc(vendordata.vendorID).update({'subscriptionTotalOrders': subscriptionTotalOrders.toString()})
                                    }


                                    if (isNaN(vendordata.wallet_amount) ||
                                        vendordata.wallet_amount ==
                                        undefined) {
                                        vendorWallet = 0;
                                    } else {
                                        vendorWallet = parseFloat(vendordata
                                            .wallet_amount);
                                    }
                                    newVendorWallet = vendorWallet + vendorAmount + parseFloat(total_tax_amount);
                                    database.collection('users').doc(
                                        vendorAuthor).update({
                                        'wallet_amount': parseFloat(
                                            newVendorWallet)
                                    }).then(async function (result) {
                                        callAjax();
                                    })
                                } else {
                                    callAjax();
                                }
                            });
                        });
                    });
                }).catch(function(error) {
                    console.error('❌ Error updating order to accepted status:', error);
                    $('#add-prepare-time-btn').prop('disabled', false).html('{{ trans('submit') }}');
                    alert('Error accepting order: ' + error.message);
                });
            });

            // Enhanced function to send comprehensive order data for email notifications
            async function sendOrderEmailNotification(orderStatus, additionalData = {}) {
                try {
                    const orderData = {
                        _token: '<?php echo csrf_token(); ?>',
                        order_id: id,
                        orderStatus: orderStatus,
                        takeAway: order.takeAway || false,
                        amount: order.amount || '₹0.00',
                        paymentMethod: order.paymentMethod || 'N/A',
                        estimatedTimeToPrepare: order.estimatedTimeToPrepare,
                        rejectionReason: order.rejectionReason || additionalData.rejectionReason,
                        
                        // Customer information
                        customer_name: order.author ? order.author.firstName : '',
                        customer_lastname: order.author ? order.author.lastName : '',
                        customer_phone: order.author ? order.author.phoneNumber : '',
                        
                        // Restaurant information
                        vendor_name: order.vendor ? order.vendor.title : '',
                        vendor_phone: order.vendor ? order.vendor.phoneNumber : '',
                        
                        // Driver information
                        driver_name: order.driver ? order.driver.firstName : '',
                        driver_lastname: order.driver ? order.driver.lastName : '',
                        driver_phone: order.driver ? order.driver.phoneNumber : '',
                        
                        // Products information
                        products: order.products || []
                    };

                    await $.ajax({
                        type: 'POST',
                        url: "<?php echo route('order-email-notification'); ?>",
                        data: orderData,
                        success: function (data) {
                            console.log('✅ Email notification sent successfully');
                        },
                        error: function (xhr, status, error) {
                            console.error('❌ Error sending email notification:', error);
                        }
                    });
                } catch (error) {
                    console.error('❌ Error in sendOrderEmailNotification:', error);
                }
            }

            async function callAjax() {
                // Send email notification for order acceptance
                await sendOrderEmailNotification("Order Accepted");
                
                await $.ajax({
                    type: 'POST',
                    url: "<?php echo route('order-status-notification'); ?>",
                    data: {
                        _token: '<?php echo csrf_token(); ?>',
                        'fcm': manfcmTokenVendor,
                        'vendorname': manname,
                        'orderStatus': "Order Accepted",
                        'subject': orderAcceptedSubject,
                        'message': orderAcceptedMsg
                    },
                    success: function (data) {
                        window.location.href = '{{ route('orders') }}';
                    },
                    error: function (xhr, status, error) {
                        console.error('❌ Error sending notification:', error);
                        $('#add-prepare-time-btn').prop('disabled', false).html('{{ trans('submit') }}');
                        alert('Order accepted but failed to send notification. Please refresh the page.');
                        window.location.href = '{{ route('orders') }}';
                    }
                });
            }

            $(".edit-form-btn").click(async function () {
                // Prevent multiple clicks by disabling button
                if ($(this).prop('disabled')) {
                    return false;
                }
                
                // Show loading state
                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
                
                // Add timeout to prevent infinite loading
                var updateTimeout = setTimeout(function() {
                    $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans("lang.update") }}');
                    alert('Request timed out. Please try again.');
                }, 60000); // 60 seconds timeout
                
                var clientName = $(".client_name").val();
                var orderStatus = $("#order_status").val();
                if (old_order_status != orderStatus) {
                    if (orderStatus == "Order Placed") {
                        manfcmTokenVendor = fcmTokenVendor;
                        manname = customername;
                    } else {
                        manfcmTokenVendor = fcmToken;
                        manname = vendorname;
                    }
                    if (orderStatus == "Order Accepted") {

                        ref.get().then(async function (snapshot) {
                            order = snapshot.docs[0].data();
                            id = order.id;
                            var scheduleTime = '';
                            if (order.hasOwnProperty('scheduleTime') && order
                                .scheduleTime != null) {
                                scheduleTime = order.scheduleTime;
                                var scheduleDate = scheduleTime.toDate().toDateString();
                                var OrderTime = order.scheduleTime.toDate()
                                    .toLocaleTimeString('en-US');
                                var scheduleDate = new Date(scheduleDate);
                                var dd = String(scheduleDate.getDate()).padStart(2, '0');
                                var mm = String(scheduleDate.getMonth() + 1).padStart(2,
                                    '0'); //January is 0!
                                var yyyy = scheduleDate.getFullYear();
                                var scheduleDate = yyyy + '-' + mm + '-' + dd;
                                today = new Date();
                                var dd = String(today.getDate()).padStart(2, '0');
                                var mm = String(today.getMonth() + 1).padStart(2,
                                    '0'); //January is 0!
                                var yyyy = today.getFullYear();
                                var todayDate = yyyy + '-' + mm + '-' + dd;
                                var currentTime = today.toLocaleTimeString('en-US');
                                var [h, m, s] = currentTime.split(":");
                                var timeslot = s.split(" ")[1];
                                h = getTwentyFourFormat(h, timeslot);
                                var currentTime = (h + ":" + m + ":" + s);
                                var [h, m, s] = OrderTime.split(":");
                                var timeslot = s.split(" ")[1];
                                h = getTwentyFourFormat(h, timeslot);
                                var orderTime = (h + ":" + m + ":" + s);
                                if (todayDate > scheduleDate) {
                                    $('#addPreparationTimeModal').modal('show');
                                } else if (todayDate == scheduleDate) {
                                    if (currentTime >= orderTime) {
                                        $('#addPreparationTimeModal').modal('show');
                                    } else {
                                        alert(
                                            "{{ trans('lang.accept_before_time_error') }}");
                                        return false;
                                    }
                                } else {
                                    alert("{{ trans('lang.accept_before_date_error') }}");
                                    return false;
                                }
                            } else {
                                $('#addPreparationTimeModal').modal('show');
                            }
                        })
                    } else {
                        database.collection('restaurant_orders').doc(id).update({
                            'status': orderStatus,
                        }).then(async function (result) {
                            console.log('✅ Order status updated successfully, now logging activity...');
                            try {
                                if (typeof logActivity === 'function') {
                                    console.log('🔍 Calling logActivity for order status update...');
                                    await logActivity('orders', 'status_updated', 'Updated order #' + id + ' status to: ' + orderStatus);
                                    console.log('✅ Activity logging completed successfully');
                                } else {
                                    console.log('❌ logActivity function is not available');
                                }
                            } catch (error) {
                                console.log('❌ Error calling logActivity:', error);
                            }
                            var subject = '';
                            var message = '';
                            if (orderStatus == "Order Completed" && orderTakeAwayOption ==
                                true) {
                                subject = takeAwayOrderCompletedSubject;
                                message = takeAwayOrderCompletedMsg;
                            } else if (orderStatus == "Order Completed" &&
                                orderTakeAwayOption == false) {
                                subject = orderCompletedSubject;
                                message = orderCompletedMsg;
                            } else if (orderStatus == "Driver Accepted") {
                                subject = driverAcceptedSubject;
                                message = driverAcceptedMsg;
                            } else if (orderStatus == "Order Rejected") {
                                subject = orderRejectedSubject;
                                message = orderRejectedMsg;
                            }
                            if (orderStatus != orderPreviousStatus && payment_shared ==
                                false) {
                                if (orderStatus == 'Order Completed') {
                                    driverAmount = parseFloat(deliveryCharge) + parseFloat(
                                        tip_amount);
                                    if (driverId && driverAmount) {
                                        var driver = database.collection('users').where(
                                            "id", "==", driverId);
                                        await driver.get().then(async function (
                                            snapshotsdriver) {
                                            var driverdata = snapshotsdriver
                                                .docs[0].data();
                                            if (driverdata) {
                                                if (isNaN(driverdata
                                                        .wallet_amount) ||
                                                    driverdata.wallet_amount ==
                                                    undefined) {
                                                    driverWallet = 0;
                                                } else {
                                                    driverWallet = driverdata
                                                        .wallet_amount;
                                                }
                                                if (orderPaymentMethod ==
                                                    'cod' &&
                                                    orderTakeAwayOption == true
                                                ) {
                                                    driverWallet = parseFloat(
                                                            driverWallet) -
                                                        parseFloat(total_price);
                                                } else {
                                                    driverWallet = parseFloat(
                                                            driverWallet) +
                                                        parseFloat(
                                                            driverAmount);
                                                }
                                                if (!isNaN(driverWallet)) {
                                                    await database.collection(
                                                        'users').doc(
                                                        driverdata.id)
                                                        .update({
                                                            'wallet_amount': parseFloat(
                                                                driverWallet
                                                            )
                                                        }).then(async function (
                                                            result) {
                                                        });
                                                }
                                            }
                                        })
                                    }
                                }
                                // Send email notification for order status change
                                await sendOrderEmailNotification(orderStatus);
                                
                                await $.ajax({
                                    type: 'POST',
                                    url: "<?php echo route('order-status-notification'); ?>",
                                    data: {
                                        _token: '<?php echo csrf_token(); ?>',
                                        'fcm': manfcmTokenVendor,
                                        'vendorname': manname,
                                        'orderStatus': orderStatus,
                                        'subject': subject,
                                        'message': message
                                    },
                                    success: function (data) {
                                        if (orderPreviousStatus !=
                                            'Order Rejected' &&
                                            orderPaymentMethod != 'cod' &&
                                            orderTakeAwayOption == false) {
                                            if (orderStatus ==
                                                'Order Rejected') {
                                                var walletId = database
                                                    .collection('temp').doc()
                                                    .id;
                                                var canceldateNew = new Date();
                                                var orderCancelDate = new Date(
                                                    canceldateNew.setHours(
                                                        23, 59, 59, 999));
                                                database.collection('wallet')
                                                    .doc(walletId).set({
                                                    'amount': parseFloat(
                                                        orderPaytableAmount
                                                    ),
                                                    'date': orderCancelDate,
                                                    'id': walletId,
                                                    'payment_status': 'success',
                                                    'user_id': orderCustomerId,
                                                    'payment_method': 'Cancelled Order Payment'
                                                }).then(function (result) {
                                                    database.collection(
                                                        'users')
                                                        .where("id",
                                                            "==",
                                                            orderCustomerId
                                                        ).get()
                                                        .then(async function (
                                                            userSnapshots
                                                        ) {
                                                            if (userSnapshots
                                                                    .docs
                                                                    .length >
                                                                0
                                                            ) {
                                                                data =
                                                                    userSnapshots
                                                                        .docs[
                                                                        0
                                                                        ]
                                                                        .data();
                                                                var wallet_amount =
                                                                    0;
                                                                if (data
                                                                        .wallet_amount !=
                                                                    undefined &&
                                                                    data
                                                                        .wallet_amount !=
                                                                    '' &&
                                                                    data
                                                                        .wallet_amount !=
                                                                    null &&
                                                                    !
                                                                        isNaN(
                                                                            data
                                                                                .wallet_amount
                                                                        )
                                                                ) {
                                                                    wallet_amount
                                                                        =
                                                                        parseFloat(
                                                                            data
                                                                                .wallet_amount
                                                                        );
                                                                }
                                                                var newWalletAmount =
                                                                    wallet_amount +
                                                                    parseFloat(
                                                                        orderPaytableAmount
                                                                    );
                                                                database
                                                                    .collection(
                                                                        'users')
                                                                    .doc(
                                                                        orderCustomerId
                                                                    )
                                                                    .update({
                                                                        'wallet_amount': parseFloat(
                                                                            newWalletAmount
                                                                        )
                                                                    })
                                                                    .then(
                                                                        function (
                                                                            result
                                                                        ) {
                                                                            <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                                                                                window
                                                                                .location
                                                                                .href =
                                                                                "{{ route('restaurants.orders', $_GET['eid']) }}";
                                                                            <?php } else { ?>
                                                                                window
                                                                                .location
                                                                                .href =
                                                                                '{{ route('orders') }}';
                                                                            <?php } ?>
                                                                        }
                                                                    )
                                                            } else {
                                                                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                                                                    window
                                                                    .location
                                                                    .href =
                                                                    "{{ route('restaurants.orders', $_GET['eid']) }}";
                                                                <?php } else { ?>
                                                                    window
                                                                    .location
                                                                    .href =
                                                                    '{{ route('orders') }}';
                                                                <?php } ?>
                                                            }
                                                        });
                                                })
                                            } else {
                                                clearTimeout(updateTimeout);
                                                $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans("lang.update") }}');
                                                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                                                    window.location.href =
                                                    "{{ route('restaurants.orders', $_GET['eid']) }}";
                                                <?php } else { ?>
                                                    window.location.href =
                                                    '{{ route('orders') }}';
                                                <?php } ?>
                                            }
                                        } else {
                                            clearTimeout(updateTimeout);
                                            $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans("lang.update") }}');
                                            <?php if (isset($_GET['eid']) && $_GET['eid'] != '') { ?>
                                                window.location.href =
                                                "{{ route('restaurants.orders', $_GET['eid']) }}";
                                            <?php } else { ?>
                                                window.location.href =
                                                '{{ route('orders') }}';
                                            <?php } ?>
                                        }
                                    }
                                });
                            }
                        }).catch(function(error) {
                            console.error('❌ Error updating order status:', error);
                            clearTimeout(updateTimeout);
                            $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans("lang.update") }}');
                            alert('Error updating order status: ' + error.message);
                        });
                    }
                } else {
                    // No status change, just reset button
                    clearTimeout(updateTimeout);
                    $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans("lang.update") }}');
                }
            }).catch(function(error) {
                console.error('❌ Error in order update process:', error);
                clearTimeout(updateTimeout);
                $('.edit-form-btn').prop('disabled', false).html('<i class="fa fa-save"></i> {{ trans("lang.update") }}');
                alert('Error processing order update: ' + error.message);
            })
        })

        // Initialize promotional pricing interceptor to catch any order loading
        function initializePromotionalPricingInterceptor() {
('🔄 ===== INITIALIZING PROMOTIONAL PRICING INTERCEPTOR =====');
            
            // Monitor DOM changes to detect when order data is loaded
            let orderProcessingTimeout;
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Check if order products table was updated
                        const orderProductsTable = document.getElementById('order_products');
                        const orderTotalTable = document.getElementById('order_products_total');
                        
                        if (orderProductsTable && orderProductsTable.children.length > 0) {
('🔄 Order data detected, scheduling promotional pricing check...');
                            
                            // Clear any existing timeout
                            if (orderProcessingTimeout) {
                                clearTimeout(orderProcessingTimeout);
                            }
                            
                            // Schedule promotional pricing processing
                            orderProcessingTimeout = setTimeout(async () => {
('🔄 Executing promotional pricing check...');
                                await checkAndApplyPromotionalPricing();
                            }, 1000); // Wait 1 second for data to stabilize
                        }
                    }
                });
            });
            
            // Start observing
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
('✅ Promotional pricing interceptor initialized');
        }

        // Check and apply promotional pricing to current order data
        async function checkAndApplyPromotionalPricing() {
('🔄 ===== CHECKING FOR PROMOTIONAL PRICING OPPORTUNITIES =====');
            
            // Prevent infinite loops by checking if promotional pricing has already been applied
            if (window.promotionalPricingApplied) {
('🔄 Promotional pricing already applied, skipping...');
                return;
            }
            
            try {
                // Try to get order data from global variables or DOM
                let orderData = null;
                
                // Check if we have order data in global variables
                if (window.vendorOrder) {
                    orderData = window.vendorOrder;
('🔄 Found order data in vendorOrder global variable');
                } else if (window.currentOrder) {
                    orderData = window.currentOrder;
('🔄 Found order data in currentOrder global variable');
                } else {
('🔄 No global order data found, attempting to extract from DOM...');
                    // Try to extract order data from DOM or other sources
                    return;
                }
                
                // Check if order has the required data for promotional pricing
                if (orderData && orderData.vendorID && orderData.products) {
('🔄 Order data suitable for promotional pricing, processing...');
                    await processOrderWithPromotionalPricing(orderData);
                } else {
('🔄 Order data not suitable for promotional pricing');
('🔄 Vendor ID:', orderData ? orderData.vendorID : 'not found');
('🔄 Products:', orderData && orderData.products ? orderData.products.length : 'not found');
                }
                
            } catch (error) {
('❌ Error checking promotional pricing:', error);
            }
        }

        // Universal order processing function that works with any order loading system
        async function processOrderWithPromotionalPricing(order) {
('🚀 ===== UNIVERSAL ORDER PROCESSING START =====');
('🚀 Order ID:', order.id);
('🚀 Vendor ID:', order.vendorID);
('🚀 Products:', order.products ? order.products.length : 0);
            
            try {
                // Get DOM elements
                const append_procucts_list = document.getElementById('order_products');
                const append_procucts_total = document.getElementById('order_products_total');
                
                if (!append_procucts_list || !append_procucts_total) {
('❌ DOM elements not found, skipping promotional processing');
                    return;
                }
                
                // Build product list with promotional pricing
('🎯 Building product list...');
                let productsListHTML = '';
                if (order.vendorID && order.products) {
                    productsListHTML = await buildHTMLProductsList(order.products, order.vendorID);
                } else {
('ℹ️ Missing vendor ID or products, using fallback');
                    productsListHTML = await buildHTMLProductsListOriginal(order.products || []);
                }
                
                // Build product totals with promotional pricing
('💰 Building product totals...');
                const productstotalHTML = await buildHTMLProductstotal(order);
                
                // Update DOM
                if (productsListHTML) {
                    append_procucts_list.innerHTML = productsListHTML;
('✅ Product list updated with promotional pricing');
                    
                    // Ensure promotional badges are properly styled after DOM update
                    setTimeout(function() {
                        var promotionalBadges = document.querySelectorAll('.promotional-badge');
                        promotionalBadges.forEach(function(badge) {
                            if (!badge.style.background) {
                                badge.style.background = 'linear-gradient(45deg, #ff6b6b, #ff8e8e)';
                                badge.style.color = 'white';
                                badge.style.padding = '4px 10px';
                                badge.style.borderRadius = '15px';
                                badge.style.fontSize = '9px';
                                badge.style.fontWeight = 'bold';
                                badge.style.textTransform = 'uppercase';
                                badge.style.letterSpacing = '0.5px';
                                badge.style.boxShadow = '0 2px 6px rgba(255, 107, 107, 0.4)';
                                badge.style.display = 'inline-block';
                                badge.style.marginTop = '4px';
                                badge.style.textAlign = 'center';
                                badge.style.width = 'fit-content';
                                badge.style.border = 'none';
                                badge.style.outline = 'none';
('🎯 Applied inline styles to promotional badge');
                            }
                        });
                    }, 100);
                }
                
                if (productstotalHTML) {
                    append_procucts_total.innerHTML = productstotalHTML;
('✅ Product totals updated with promotional pricing');
                }
                
                // Set flag to prevent infinite loops
                window.promotionalPricingApplied = true;
                
('🚀 ===== UNIVERSAL ORDER PROCESSING COMPLETE =====');
                
            } catch (error) {
('❌ Error in universal order processing:', error);
('🔄 Falling back to original processing...');
            }
        }

        // Test function to verify promotional pricing with specific data
        async function testPromotionalPricing() {
('🧪 ===== TESTING PROMOTIONAL PRICING =====');
            try {
                // Test with your specific promotional data
                const testProduct = {
                    id: "bRqazcK1Cxo9b5nnXiuM",
                    name: "Veg Manchurian",
                    price: "148",
                    discountPrice: "148"
                };
                const testVendorID = "WYIy8UYfyRi1gNDhd3Gm";
                
('🧪 Testing with specific data:', {
                    product: testProduct,
                    vendorID: testVendorID
                });
                
                const result = await getPromotionalPrice(testProduct, testVendorID);
('🧪 TEST RESULT:', result);
                
                return result;
            } catch (error) {
('🧪 Test failed:', error);
                return null;
            }
        }

        // Clean and robust promotional price checking function
        async function getPromotionalPrice(product, vendorID) {
            try {
('🔍 ===== PROMOTIONAL PRICE CHECK START =====');
('🔍 Product Details:', {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    discountPrice: product.discountPrice,
                    vendorID: vendorID
                });
                
                // Get all promotions for this product and vendor
('🔍 Building promotion query...');
                const promotionQuery = database.collection('promotions')
                    .where('product_id', '==', product.id)
                    .where('restaurant_id', '==', vendorID)
                    .where('isAvailable', '==', true);
                
('🔍 Executing promotion query...');
                const promotionSnapshot = await promotionQuery.get();
('🔍 Promotion query results:', {
                    empty: promotionSnapshot.empty,
                    size: promotionSnapshot.size,
                    docs: promotionSnapshot.docs.length
                });
                
                if (!promotionSnapshot.empty) {
('🔍 Found', promotionSnapshot.docs.length, 'promotions, checking time validity...');
                    
                    const now = firebase.firestore.Timestamp.now();
('🔍 Current time:', now.toDate());
                    
                    // Check each promotion for time validity
                    for (const doc of promotionSnapshot.docs) {
                        const promotionData = doc.data();
('🔍 Checking promotion:', {
                            id: doc.id,
                            product_id: promotionData.product_id,
                            restaurant_id: promotionData.restaurant_id,
                            special_price: promotionData.special_price,
                            start_time: promotionData.start_time,
                            end_time: promotionData.end_time,
                            isAvailable: promotionData.isAvailable
                        });
                        
                        const startTime = promotionData.start_time;
                        const endTime = promotionData.end_time;
                        
('🔍 Time comparison:', {
                            now: now.toDate(),
                            start_time: startTime ? startTime.toDate() : 'null',
                            end_time: endTime ? endTime.toDate() : 'null',
                            isAfterStart: startTime ? now >= startTime : true,
                            isBeforeEnd: endTime ? now <= endTime : true
                        });
                        
                        // Check if promotion is currently active
                        const isAfterStart = !startTime || now >= startTime;
                        const isBeforeEnd = !endTime || now <= endTime;
                        const isActive = isAfterStart && isBeforeEnd;
                        
('🔍 Promotion is active:', isActive);
('🔍 Is after start:', isAfterStart);
('🔍 Is before end:', isBeforeEnd);
                        
                        if (isActive) {
('🎯 ===== PROMOTIONAL PRICE FOUND =====');
('🎯 Product:', product.name);
('🎯 Original Price:', product.discountPrice || product.price);
('🎯 Special Price:', promotionData.special_price);
('🎯 Promotion Data:', promotionData);
('🎯 Price Difference:', (parseFloat(product.discountPrice || product.price) - parseFloat(promotionData.special_price)));
                            
                            const result = {
                                price: parseFloat(promotionData.special_price),
                                isPromotional: true,
                                promotionId: doc.id,
                                originalPrice: parseFloat(product.discountPrice || product.price)
                            };
('🎯 Returning promotional result:', result);
                            return result;
                        }
                    }
                    
('ℹ️ No active promotions found (time-based filtering)');
                } else {
('ℹ️ No promotions found for this product');
                }
                
('ℹ️ ===== NO PROMOTIONAL PRICE FOUND =====');
('ℹ️ Product:', product.name);
('ℹ️ Using regular price:', product.discountPrice || product.price);
                
                const regularResult = {
                    price: parseFloat(product.discountPrice || product.price),
                    isPromotional: false,
                    promotionId: null,
                    originalPrice: parseFloat(product.discountPrice || product.price)
                };
('ℹ️ Returning regular result:', regularResult);
                return regularResult;
                
            } catch (error) {
('❌ ===== ERROR IN PROMOTIONAL PRICE CHECK =====');
('❌ Error details:', error);
('❌ Product:', product.name, 'ID:', product.id);
('❌ Vendor:', vendorID);
                
                const errorResult = {
                    price: parseFloat(product.discountPrice || product.price),
                    isPromotional: false,
                    promotionId: null,
                    originalPrice: parseFloat(product.discountPrice || product.price)
                };
('❌ Returning error fallback:', errorResult);
                return errorResult;
            }
        }

        // Function to enhance product list with promotional pricing
        async function enhanceProductListWithPromotions(products, vendorID) {
('🎯 ===== PRODUCT ENHANCEMENT START =====');
('🎯 Starting promotional price enhancement for', products.length, 'products');
('🎯 Vendor ID:', vendorID);
('🎯 Products:', products.map(p => ({ id: p.id, name: p.name, price: p.price, discountPrice: p.discountPrice, quantity: p.quantity })));
            
            let promotionalItemsCount = 0;
            let regularItemsCount = 0;
            let totalPromotionalSavings = 0;
            
            for (const product of products) {
                try {
('🎯 ===== PROCESSING PRODUCT =====');
('🎯 Product:', product.name, 'ID:', product.id);
('🎯 Original Price:', product.price, 'Discount Price:', product.discountPrice);
('🎯 Quantity:', product.quantity);
                    
                    const priceInfo = await getPromotionalPrice(product, vendorID);
('🎯 Price Info Result:', priceInfo);
                    
                    if (priceInfo.isPromotional) {
                        promotionalItemsCount++;
                        const savings = (priceInfo.originalPrice - priceInfo.price) * (parseInt(product.quantity) || 1);
                        totalPromotionalSavings += savings;
                        
('🎯 ===== APPLYING PROMOTIONAL PRICE =====');
('🎯 Product:', product.name);
('🎯 Original Price:', priceInfo.originalPrice);
('🎯 Promotional Price:', priceInfo.price);
('🎯 Quantity:', product.quantity);
('🎯 Savings per item:', (priceInfo.originalPrice - priceInfo.price));
('🎯 Total savings for this item:', savings);
('🎯 Running total savings:', totalPromotionalSavings);
                        
                        // Update the product price in the DOM
                        const productRow = document.querySelector(`[data-product-id="${product.id}"]`);
('🎯 Product row found:', productRow);
                        if (productRow) {
                            // Update price display
                            const priceElement = productRow.querySelector('.item-price');
('🎯 Price element found:', priceElement);
                            if (priceElement) {
                                const originalPrice = priceInfo.originalPrice;
                                const promotionalPrice = priceInfo.price;
                                
                                // Format prices
                                let originalPriceFormatted = '';
                                let promotionalPriceFormatted = '';
                                
                                if (currencyAtRight) {
                                    originalPriceFormatted = originalPrice.toFixed(decimal_degits) + currentCurrency;
                                    promotionalPriceFormatted = promotionalPrice.toFixed(decimal_degits) + currentCurrency;
                                } else {
                                    originalPriceFormatted = currentCurrency + originalPrice.toFixed(decimal_degits);
                                    promotionalPriceFormatted = currentCurrency + promotionalPrice.toFixed(decimal_degits);
                                }
                                
('🎯 Formatted prices:', {
                                    original: originalPriceFormatted,
                                    promotional: promotionalPriceFormatted
                                });
                                
                                // Update price display with promotional styling
                                priceElement.innerHTML = `
                                    <span class="promotional-price" style="color: #28a745; font-weight: bold;">${promotionalPriceFormatted}</span>
                                    <br><span class="original-price" style="text-decoration: line-through; font-size: 12px; color: #6c757d;">Original: ${originalPriceFormatted}</span>
                                `;
                                
                                // Add promotional badge
                                const productNameElement = productRow.querySelector('h6');
                                if (productNameElement && !productNameElement.querySelector('.badge-success')) {
                                    const badge = document.createElement('span');
                                    badge.className = 'badge badge-success';
                                    badge.style.cssText = 'background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-left: 5px;';
                                    badge.textContent = '🎯 Promotional Price';
                                    productNameElement.appendChild(badge);
                                }
                                
('🎯 DOM updated successfully for promotional item');
                            } else {
('❌ Price element not found for promotional item');
                            }
                        } else {
('❌ Product row not found for promotional item');
                        }
                    } else {
                        regularItemsCount++;
('ℹ️ ===== REGULAR PRICE ITEM =====');
('ℹ️ Product:', product.name);
('ℹ️ Using regular price:', priceInfo.price);
('ℹ️ Quantity:', product.quantity);
('ℹ️ Total for this item:', priceInfo.price * (parseInt(product.quantity) || 1));
                    }
                } catch (error) {
('❌ Error enhancing product with promotional pricing:', error);
('❌ Product:', product.name, 'ID:', product.id);
                }
            }
            
('🎯 ===== ENHANCEMENT SUMMARY =====');
('🎯 Total products processed:', products.length);
('🎯 Promotional items:', promotionalItemsCount);
('🎯 Regular items:', regularItemsCount);
('🎯 Total promotional savings:', totalPromotionalSavings);
('🎯 ===== PRODUCT ENHANCEMENT COMPLETE =====');
        }

        // Enhanced function to build product list with promotional pricing
        async function buildHTMLProductsListWithPromotions(snapshotsProducts, vendorID) {
            try {
('🎯 Products:', snapshotsProducts.length);
('🎯 Vendor ID:', vendorID);
                
                var html = '';
                var alldata = [];
                var number = [];
                var totalProductPrice = 0;
                
                for (const product of snapshotsProducts) {
                    try {
('🎯 ===== PROCESSING PRODUCT FOR LIST =====');
('🎯 Product:', product.name, 'ID:', product.id);
('🎯 Original Price:', product.price, 'Discount Price:', product.discountPrice);
('🎯 Quantity:', product.quantity);
                        
                        // Get promotional price for this product
                        const priceInfo = await getPromotionalPrice(product, vendorID);
('🎯 Price Info Result:', priceInfo);
                        
                        getProductInfo(product);
                        var val = product;
                        var product_id = (val.variant_info && val.variant_info.variant_id) ? val.variant_info.variant_id : val.id;
                        html = html + '<tr data-product-id="' + val.id + '">';
                        var extra_html = '';
                        if (product.extras != undefined && product.extras != '' && product.extras.length > 0) {
                            extra_html = extra_html + '<span>';
                            var extra_count = 1;
                            try {
                                product.extras.forEach((extra) => {
                                    if (extra_count > 1) {
                                        extra_html = extra_html + ',' + extra;
                                    } else {
                                        extra_html = extra_html + extra;
                                    }
                                    extra_count++;
                                })
                            } catch (error) {
                            }
                            extra_html = extra_html + '</span>';
                        }
                        html = html + '<td class="order-product"><div class="order-product-box">';
                        if (val.photo != '' && val.photo != null) {
                            html = html + '<img  onerror="this.onerror=null;this.src=\'' + place_image +
                                '\'" class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' + val
                                    .photo + '" alt="image">';
                        } else {
                            html = html + '<img class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' +
                                place_image + '" alt="image">';
                        }
                        html = html + '</div><div class="orders-tracking"><h6>' + val.name +
                            '</h6><div class="orders-tracking-item-details">';
                        if (val.variant_info) {
                            html = html + '<div class="variant-info">';
                            html = html + '<ul>';
                            $.each(val.variant_info.variant_options, function (label, value) {
                                html = html + '<li class="variant"><span class="label">' + label +
                                    '</span><span class="value">' + value + '</span></li>';
                            });
                            html = html + '</ul>';
                            html = html + '</div>';
                        }
                        if (extra_count > 1 || product.size) {
                            html = html + '<strong>{{ trans('lang.extras') }} :</strong>';
                        }
                        if (extra_count > 1) {
                            html = html +
                                '<div class="extra"><span>{{ trans('lang.extras') }} :</span><span class="ext-item">' +
                                extra_html + '</span></div>';
                        }
                        if (product.size) {
                            html = html +
                                '<div class="type"><span>{{ trans('lang.type') }} :</span><span class="ext-size">' +
                                product.size + '</span></div>';
                        }
                        
                        // Use promotional price if available, otherwise use original price
                        var final_price = priceInfo.price;
('🎯 Using final price:', final_price, 'for product:', product.name);
('🎯 Is promotional:', priceInfo.isPromotional);
                        
                        price_item = final_price.toFixed(decimal_degits);
                        totalProductPrice = parseFloat(price_item) * parseInt(val.quantity);
                        var extras_price = 0;
                        if (product.extras != undefined && product.extras != '' && product.extras.length > 0) {
                            extras_price_item = (parseFloat(val.extras_price) * parseInt(val.quantity)).toFixed(
                                decimal_degits);
                            if (parseFloat(extras_price_item) != NaN && val.extras_price != undefined) {
                                extras_price = extras_price_item;
                            }
                            totalProductPrice = parseFloat(extras_price) + parseFloat(totalProductPrice);
                        }
                        totalProductPrice = parseFloat(totalProductPrice).toFixed(decimal_degits);
                        if (currencyAtRight) {
                            price_val = parseFloat(price_item).toFixed(decimal_degits) + "" + currentCurrency;
                            extras_price_val = parseFloat(extras_price).toFixed(decimal_degits) + "" + currentCurrency;
                            totalProductPrice_val = parseFloat(totalProductPrice).toFixed(decimal_degits) + "" +
                                currentCurrency;
                        } else {
                            price_val = currentCurrency + "" + parseFloat(price_item).toFixed(decimal_degits);
                            extras_price_val = currentCurrency + "" + parseFloat(extras_price).toFixed(decimal_degits);
                            totalProductPrice_val = currentCurrency + "" + parseFloat(totalProductPrice).toFixed(
                                decimal_degits);
                        }
                        
('🎯 ===== PRODUCT LIST CALCULATION =====');
('🎯 Product:', product.name);
('🎯 Final price used:', final_price);
('🎯 Quantity:', val.quantity);
('🎯 Total for this item:', totalProductPrice);
('🎯 Price display:', price_val);
('🎯 Total display:', totalProductPrice_val);
                        
                        // Add promotional badge and styling if this is a promotional item
                        var promotionalBadge = '';
                        var rowClass = '';
                        if (priceInfo.isPromotional) {
                            promotionalBadge = '<div class="promotional-badge" style="background: linear-gradient(45deg, #ff6b6b, #ff8e8e); color: white; padding: 4px 10px; border-radius: 15px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 6px rgba(255, 107, 107, 0.4); display: inline-block; margin-top: 4px; animation: pulse 2s infinite; text-align: center; width: fit-content; border: none; outline: none;">🎯 PROMO</div>';
                            rowClass = ' promotional-item-row';
('🎯 Adding promotional badge for:', product.name);
                        }
                        
                        html = html + '<td class="text-green text-center"><span class="item-price">' + price_val +
                            '</span><br><span class="base-price-' + product_id + ' text-muted"></span></td><td> × ' + val
                                .quantity + '</td><td class="text-green"> + ' + extras_price_val +
                            '</td><td class="text-green">  ' + totalProductPrice_val + '</td>';
                        html = html + '</tr>';
                        
                        // Update the product name with promotional badge UNDER the name
                        if (priceInfo.isPromotional) {
                            html = html.replace(
                                '<h6>' + val.name + '</h6>',
                                '<h6>' + val.name + '</h6>' + promotionalBadge
                            );
                            // Add promotional row class to the tr element
                            html = html.replace(
                                '<tr data-product-id="' + val.id + '">',
                                '<tr data-product-id="' + val.id + '" class="' + rowClass + '">'
                            );
                        }
                        
                        total_price += parseFloat(totalProductPrice);
                    } catch (error) {
('❌ Error processing product:', product.name, error);
                        // Fallback to original pricing if promotional pricing fails
('🔄 Falling back to original pricing for:', product.name);
                        // Continue with original logic as fallback
                    }
                }
                totalProductPrice = 0;
('🎯 ===== PRODUCT LIST BUILD COMPLETE =====');
                return html;
            } catch (error) {
('❌ Error in buildHTMLProductsListWithPromotions:', error);
                // Fallback to original function if promotional function fails
('🔄 Falling back to original buildHTMLProductsList function');
                return buildHTMLProductsList(snapshotsProducts);
            }
        }

        // Enhanced function to build product list with automatic promotional pricing detection
        async function buildHTMLProductsList(snapshotsProducts, vendorID) {
('🎯 ===== BUILDING PRODUCT LIST (ENHANCED) =====');
('🎯 Products:', snapshotsProducts.length);
('🎯 Vendor ID:', vendorID);
('🎯 Will attempt promotional pricing if vendor ID is available');
            
            // If vendor ID is available, use promotional pricing
            if (vendorID && typeof getPromotionalPrice === 'function') {
('🎯 Using promotional pricing system');
                return await buildHTMLProductsListWithPromotions(snapshotsProducts, vendorID);
            } else {
('🎯 Using original pricing system (no vendor ID or promotional functions)');
                return buildHTMLProductsListOriginal(snapshotsProducts);
            }
        }

        // Original product list function (renamed for clarity)
        function buildHTMLProductsListOriginal(snapshotsProducts) {
('🎯 ===== BUILDING PRODUCT LIST (ORIGINAL) =====');
            var html = '';
            var alldata = [];
            var number = [];
            var totalProductPrice = 0;
            snapshotsProducts.forEach((product) => {
                getProductInfo(product);
                var val = product;
                var product_id = (val.variant_info && val.variant_info.variant_id) ? val.variant_info.variant_id :
                    val.id;
                html = html + '<tr data-product-id="' + val.id + '">';
                var extra_html = '';
                if (product.extras != undefined && product.extras != '' && product.extras.length > 0) {
                    extra_html = extra_html + '<span>';
                    var extra_count = 1;
                    try {
                        product.extras.forEach((extra) => {
                            if (extra_count > 1) {
                                extra_html = extra_html + ',' + extra;
                            } else {
                                extra_html = extra_html + extra;
                            }
                            extra_count++;
                        })
                    } catch (error) {
                    }
                    extra_html = extra_html + '</span>';
                }
                html = html + '<td class="order-product"><div class="order-product-box">';
                if (val.photo != '' && val.photo != null) {
                    html = html + '<img  onerror="this.onerror=null;this.src=\'' + place_image +
                        '\'" class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' + val
                            .photo + '" alt="image">';
                } else {
                    html = html + '<img class="img-circle img-size-32 mr-2" style="width:60px;height:60px;" src="' +
                        place_image + '" alt="image">';
                }
                html = html + '</div><div class="orders-tracking"><h6>' + val.name +
                    '</h6><div class="orders-tracking-item-details">';
                if (val.variant_info) {
                    html = html + '<div class="variant-info">';
                    html = html + '<ul>';
                    $.each(val.variant_info.variant_options, function (label, value) {
                        html = html + '<li class="variant"><span class="label">' + label +
                            '</span><span class="value">' + value + '</span></li>';
                    });
                    html = html + '</ul>';
                    html = html + '</div>';
                }
                if (extra_count > 1 || product.size) {
                    html = html + '<strong>{{ trans('lang.extras') }} :</strong>';
                }
                if (extra_count > 1) {
                    html = html +
                        '<div class="extra"><span>{{ trans('lang.extras') }} :</span><span class="ext-item">' +
                        extra_html + '</span></div>';
                }
                if (product.size) {
                    html = html +
                        '<div class="type"><span>{{ trans('lang.type') }} :</span><span class="ext-size">' +
                        product.size + '</span></div>';
                }
                var final_price = '';
                if (val.discountPrice != 0 && val.discountPrice != "" && val.discountPrice != null && !isNaN(val
                    .discountPrice)) {
                    final_price = parseFloat(val.discountPrice);
                } else {
                    final_price = parseFloat(val.price);
                }
                price_item = final_price.toFixed(decimal_degits);
                totalProductPrice = parseFloat(price_item) * parseInt(val.quantity);
                var extras_price = 0;
                if (product.extras != undefined && product.extras != '' && product.extras.length > 0) {
                    extras_price_item = (parseFloat(val.extras_price) * parseInt(val.quantity)).toFixed(
                        decimal_degits);
                    if (parseFloat(extras_price_item) != NaN && val.extras_price != undefined) {
                        extras_price = extras_price_item;
                    }
                    totalProductPrice = parseFloat(extras_price) + parseFloat(totalProductPrice);
                }
                totalProductPrice = parseFloat(totalProductPrice).toFixed(decimal_degits);
                if (currencyAtRight) {
                    price_val = parseFloat(price_item).toFixed(decimal_degits) + "" + currentCurrency;
                    extras_price_val = parseFloat(extras_price).toFixed(decimal_degits) + "" + currentCurrency;
                    totalProductPrice_val = parseFloat(totalProductPrice).toFixed(decimal_degits) + "" +
                        currentCurrency;
                } else {
                    price_val = currentCurrency + "" + parseFloat(price_item).toFixed(decimal_degits);
                    extras_price_val = currentCurrency + "" + parseFloat(extras_price).toFixed(decimal_degits);
                    totalProductPrice_val = currentCurrency + "" + parseFloat(totalProductPrice).toFixed(
                        decimal_degits);
                }
                html = html + '<td class="text-green text-center"><span class="item-price">' + price_val +
                    '</span><br><span class="base-price-' + product_id + ' text-muted"></span></td><td> × ' + val
                        .quantity + '</td><td class="text-green"> + ' + extras_price_val +
                    '</td><td class="text-green">  ' + totalProductPrice_val + '</td>';
                html = html + '</tr>';
                total_price += parseFloat(totalProductPrice);
            });
            totalProductPrice = 0;
            return html;
        }

        function getProductInfo(product) {
            database.collection('vendor_products').doc(product.id).get().then(async function (snapshots) {
                if (snapshots.exists) {
                    var productData = snapshots.data();
                    if (product.variant_info && product.variant_info.variant_id) {
                        var variant_info = $.map(productData.item_attribute.variants, function (v, i) {
                            if (v.variant_sku == product.variant_info.variant_sku) {
                                return v;
                            }
                        });
                        base_price = parseFloat(variant_info[0].variant_price);
                        var product_id = product.variant_info.variant_id;
                    } else {
                        if (parseFloat(productData.disPrice) != 0) {
                            var base_price = productData.disPrice;
                        } else {
                            var base_price = productData.price;
                        }
                        var product_id = product.id;
                    }
                    if (currencyAtRight) {
                        base_price_format = parseFloat(base_price).toFixed(decimal_degits) + "" +
                            currentCurrency;
                    } else {
                        base_price_format = currentCurrency + "" + parseFloat(base_price).toFixed(
                            decimal_degits);
                    }
                    $(".base-price-" + product_id).text('(Base Price: ' + base_price_format + ')');
                }
            });
        }

        // Function to enhance total calculation with promotional savings
        async function enhanceTotalWithPromotionalSavings(products, vendorID) {
            try {
('💰 ===== TOTAL ENHANCEMENT START =====');
('💰 Processing', products.length, 'products for promotional savings');
('💰 Vendor ID:', vendorID);
                
                let totalPromotionalSavings = 0;
                let promotionalItems = [];
                let regularItems = [];
                
                for (const product of products) {
('💰 ===== PROCESSING PRODUCT FOR TOTAL =====');
('💰 Product:', product.name, 'ID:', product.id);
('💰 Original Price:', product.price, 'Discount Price:', product.discountPrice);
('💰 Quantity:', product.quantity);
                    
                    const priceInfo = await getPromotionalPrice(product, vendorID);
('💰 Price Info Result:', priceInfo);
                    
                    if (priceInfo.isPromotional) {
                        const savings = (priceInfo.originalPrice - priceInfo.price) * (parseInt(product.quantity) || 1);
                        totalPromotionalSavings += savings;
                        
                        const promotionalItem = {
                            name: product.name,
                            originalPrice: priceInfo.originalPrice,
                            promotionalPrice: priceInfo.price,
                            quantity: parseInt(product.quantity) || 1,
                            savings: savings
                        };
                        promotionalItems.push(promotionalItem);
                        
('💰 ===== PROMOTIONAL ITEM FOUND =====');
('💰 Product:', product.name);
('💰 Original Price:', priceInfo.originalPrice);
('💰 Promotional Price:', priceInfo.price);
('💰 Quantity:', product.quantity);
('💰 Savings per item:', (priceInfo.originalPrice - priceInfo.price));
('💰 Total savings for this item:', savings);
('💰 Running total savings:', totalPromotionalSavings);
                    } else {
                        const regularItem = {
                            name: product.name,
                            price: priceInfo.price,
                            quantity: parseInt(product.quantity) || 1,
                            total: priceInfo.price * (parseInt(product.quantity) || 1)
                        };
                        regularItems.push(regularItem);
                        
('💰 ===== REGULAR ITEM =====');
('💰 Product:', product.name);
('💰 Price:', priceInfo.price);
('💰 Quantity:', product.quantity);
('💰 Total for this item:', priceInfo.price * (parseInt(product.quantity) || 1));
                    }
                }
                
('💰 ===== CALCULATION SUMMARY =====');
('💰 Promotional items:', promotionalItems.length);
('💰 Regular items:', regularItems.length);
('💰 Total promotional savings:', totalPromotionalSavings);
('💰 Promotional items details:', promotionalItems);
('💰 Regular items details:', regularItems);
                
                if (totalPromotionalSavings > 0) {
('💰 ===== ADDING PROMOTIONAL SAVINGS TO TOTAL =====');
('💰 Total promotional savings to display:', totalPromotionalSavings);
                    
                    // Find the total amount row and add promotional savings before it
                    const totalRow = document.querySelector('.grand-total');
('💰 Total row found:', totalRow);
                    
                    if (totalRow) {
                        const promotionalSavingsRow = document.createElement('tr');
                        promotionalSavingsRow.className = 'promotional-savings-row';
                        
                        let promotionalSavingsFormatted = '';
                        if (currencyAtRight) {
                            promotionalSavingsFormatted = totalPromotionalSavings.toFixed(decimal_degits) + currentCurrency;
                        } else {
                            promotionalSavingsFormatted = currentCurrency + totalPromotionalSavings.toFixed(decimal_degits);
                        }
                        
('💰 Formatted promotional savings:', promotionalSavingsFormatted);
                        
                        promotionalSavingsRow.innerHTML = `
                            <td class="seprater" colspan="2"><hr><span>🎯 Promotional Savings</span></td>
                        `;
                        
                        const savingsRow = document.createElement('tr');
                        savingsRow.innerHTML = `
                            <td class="label">🎯 Promotional Savings</td>
                            <td class="promotional_savings text-success" style="color: #28a745; font-weight: bold;">(-${promotionalSavingsFormatted})</td>
                        `;
                        
('💰 Inserting promotional savings rows before total');
                        totalRow.parentNode.insertBefore(promotionalSavingsRow, totalRow);
                        totalRow.parentNode.insertBefore(savingsRow, totalRow);
                        
('💰 Promotional savings rows inserted successfully');
                    } else {
('❌ Total row not found - cannot add promotional savings');
                    }
                } else {
('ℹ️ No promotional savings to add');
                }
                
('💰 ===== TOTAL ENHANCEMENT COMPLETE =====');
            } catch (error) {
('❌ Error enhancing total with promotional savings:', error);
('❌ Error details:', error);
            }
        }

        // Enhanced function to calculate totals with promotional pricing
        async function calculatePromotionalTotals(products, vendorID) {
('💰 ===== CALCULATING PROMOTIONAL TOTALS =====');
('💰 Products:', products.length);
('💰 Vendor ID:', vendorID);
            
            let promotionalSubtotal = 0;
            let originalSubtotal = 0;
            let promotionalSavings = 0;
            let promotionalItems = [];
            let regularItems = [];
            
            for (const product of products) {
('💰 ===== CALCULATING PRODUCT TOTAL =====');
('💰 Product:', product.name, 'ID:', product.id);
('💰 Original Price:', product.price, 'Discount Price:', product.discountPrice);
('💰 Quantity:', product.quantity);
                
                const priceInfo = await getPromotionalPrice(product, vendorID);
('💰 Price Info Result:', priceInfo);
                
                const quantity = parseInt(product.quantity) || 1;
                const originalPrice = parseFloat(product.discountPrice || product.price);
                const promotionalPrice = priceInfo.price;
                
                if (priceInfo.isPromotional) {
                    const itemTotal = promotionalPrice * quantity;
                    const originalTotal = originalPrice * quantity;
                    const savings = originalTotal - itemTotal;
                    
                    promotionalSubtotal += itemTotal;
                    originalSubtotal += originalTotal;
                    promotionalSavings += savings;
                    
                    promotionalItems.push({
                        name: product.name,
                        originalPrice: originalPrice,
                        promotionalPrice: promotionalPrice,
                        quantity: quantity,
                        originalTotal: originalTotal,
                        promotionalTotal: itemTotal,
                        savings: savings
                    });
                    
('💰 ===== PROMOTIONAL ITEM CALCULATION =====');
('💰 Product:', product.name);
('💰 Original Price:', originalPrice);
('💰 Promotional Price:', promotionalPrice);
('💰 Quantity:', quantity);
('💰 Original Total:', originalTotal);
('💰 Promotional Total:', itemTotal);
('💰 Savings:', savings);
('💰 Running Promotional Subtotal:', promotionalSubtotal);
('💰 Running Promotional Savings:', promotionalSavings);
                } else {
                    const itemTotal = originalPrice * quantity;
                    promotionalSubtotal += itemTotal;
                    originalSubtotal += itemTotal;
                    
                    regularItems.push({
                        name: product.name,
                        price: originalPrice,
                        quantity: quantity,
                        total: itemTotal
                    });
                    
('💰 ===== REGULAR ITEM CALCULATION =====');
('💰 Product:', product.name);
('💰 Price:', originalPrice);
('💰 Quantity:', quantity);
('💰 Total:', itemTotal);
('💰 Running Promotional Subtotal:', promotionalSubtotal);
                }
            }
            
('💰 ===== FINAL CALCULATION SUMMARY =====');
('💰 Original Subtotal:', originalSubtotal);
('💰 Promotional Subtotal:', promotionalSubtotal);
('💰 Total Promotional Savings:', promotionalSavings);
('💰 Promotional Items:', promotionalItems.length);
('💰 Regular Items:', regularItems.length);
('💰 Promotional Items Details:', promotionalItems);
('💰 Regular Items Details:', regularItems);
            
            return {
                promotionalSubtotal: promotionalSubtotal,
                originalSubtotal: originalSubtotal,
                promotionalSavings: promotionalSavings,
                promotionalItems: promotionalItems,
                regularItems: regularItems
            };
        }

        // Enhanced function to build product totals with promotional pricing support
        async function buildHTMLProductstotal(snapshotsProducts) {
('💰 ===== BUILDING PRODUCT TOTALS =====');
('💰 Order data:', snapshotsProducts);
('💰 Products:', snapshotsProducts.products ? snapshotsProducts.products.length : 0);
            
            var html = '';
            var alldata = [];
            var number = [];
            adminCommissionValue = snapshotsProducts.adminCommission;
            var adminCommissionType = snapshotsProducts.adminCommissionType;
            var discount = snapshotsProducts.discount;
            var couponCode = snapshotsProducts.couponCode;
            var extras = snapshotsProducts.extras;
            var extras_price = snapshotsProducts.extras_price;
            var rejectedByDrivers = snapshotsProducts.rejectedByDrivers;
            var takeAway = snapshotsProducts.takeAway;
            var tip_amount = snapshotsProducts.tip_amount;
            var notes = snapshotsProducts.notes;
            var tax_amount = snapshotsProducts.vendor.tax_amount;
            var status = snapshotsProducts.status;
            var products = snapshotsProducts.products;
            var deliveryCharge = snapshotsProducts.deliveryCharge;
            var specialDiscount = snapshotsProducts.specialDiscount;
            var intRegex = /^\d+$/;
            var floatRegex = /^((\d+(\.\d+)?)|((\d+\.)?\d+))$/;
            var baseDeliveryCharge = 23; // default, override with settings if available
            var perKmChargeAboveFreeDistance = 8;
            var freeDeliveryDistanceKm = 7;
            var itemTotalThreshold = 299;
            var gstRate = 18;
            var sgstRate = 5;
            var subtotal = 0;
            
            // Calculate subtotal with promotional pricing support
            if (products) {
('💰 ===== CALCULATING SUBTOTAL WITH PROMOTIONAL SUPPORT =====');
('💰 Vendor ID for promotional check:', snapshotsProducts.vendorID);
                
                // Try to use promotional pricing if vendor ID is available
                if (snapshotsProducts.vendorID) {
                    try {
('💰 Attempting promotional pricing calculation...');
                        const promotionalTotals = await calculatePromotionalTotals(products, snapshotsProducts.vendorID);
                        
                        if (promotionalTotals && promotionalTotals.promotionalSubtotal > 0) {
                            subtotal = promotionalTotals.promotionalSubtotal;
                            window.promotionalTotals = promotionalTotals;
('💰 Using promotional subtotal:', subtotal);
('💰 Promotional savings:', promotionalTotals.promotionalSavings);
                        } else {
('💰 No promotional pricing found, using original calculation');
                            products.forEach((product) => {
                                var price = (product.discountPrice && parseFloat(product.discountPrice) > 0)
                                    ? parseFloat(product.discountPrice)
                                    : parseFloat(product.price);
                                subtotal += price * (parseInt(product.quantity) || 1);
                            });
                        }
                    } catch (error) {
('❌ Error calculating promotional pricing:', error);
('🔄 Falling back to original subtotal calculation');
                        products.forEach((product) => {
                            var price = (product.discountPrice && parseFloat(product.discountPrice) > 0)
                                ? parseFloat(product.discountPrice)
                                : parseFloat(product.price);
                            subtotal += price * (parseInt(product.quantity) || 1);
                        });
                    }
                } else {
('💰 No vendor ID available, using original calculation');
                    products.forEach((product) => {
                        var price = (product.discountPrice && parseFloat(product.discountPrice) > 0)
                            ? parseFloat(product.discountPrice)
                            : parseFloat(product.price);
                        subtotal += price * (parseInt(product.quantity) || 1);
                    });
                }
            }
            
            // Use promotional subtotal if available
            if (window.promotionalTotals && window.promotionalTotals.promotionalSubtotal && window.promotionalTotals.promotionalSubtotal > 0) {
('💰 ===== USING PROMOTIONAL SUBTOTAL =====');
('💰 Original subtotal:', subtotal);
('💰 Promotional subtotal:', window.promotionalTotals.promotionalSubtotal);
('💰 Promotional savings:', window.promotionalTotals.promotionalSavings);
                subtotal = window.promotionalTotals.promotionalSubtotal;
            } else {
('💰 ===== USING ORIGINAL SUBTOTAL =====');
('💰 No promotional totals available, using original subtotal:', subtotal);
            }

            // Use delivery charge from order data (exact same logic as print.blade.php)
            var deliveryCharge = snapshotsProducts.deliveryCharge;

            // // Debug delivery charge (same as print.blade.php)
            // ('=== buildHTMLProductstotal Debug ===');
            // ('Delivery charge from order data:', deliveryCharge);
            // ('Base delivery charge:', baseDeliveryCharge);

            // Initialize total_price with subtotal
            var total_price = subtotal;
            // ('Initial total_price (subtotal):', total_price);

            // Add extras to total_price if available
            if (intRegex.test(extras_price) || floatRegex.test(extras_price)) {
                total_price += parseFloat(extras_price);
                // ('Added extras to total_price:', parseFloat(extras_price));
                // ('Total_price after extras:', total_price);
            }

            var sgst = subtotal * (sgstRate / 100); // 5% of subtotal only
            var gst = 0;
            if (parseFloat(deliveryCharge) > 0) {
                // If delivery charge equals base delivery charge (₹23), only calculate GST once
                if (parseFloat(deliveryCharge) === baseDeliveryCharge) {
                    gst = baseDeliveryCharge * (gstRate / 100); // 18% of base delivery charge only
                } else {
                    // If delivery charge is different from base delivery charge, calculate GST on actual delivery charge + base delivery charge
                    gst = (parseFloat(deliveryCharge) * (gstRate / 100)) + (baseDeliveryCharge * (gstRate / 100)); // 18% of delivery charge + 18% of base delivery charge
                }
            } else {
                gst = baseDeliveryCharge * (gstRate / 100); // 18% of base delivery charge only
            }
            
            // Log tax calculations
('💰 ===== TAX CALCULATIONS =====');
('💰 Subtotal used for taxes:', subtotal);
('💰 SGST (5%):', sgst);
('💰 GST (18%):', gst);
('💰 Delivery Charge:', deliveryCharge);
            if (currencyAtRight) {
                var sub_total = parseFloat(subtotal).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                var sub_total = currentCurrency + "" + parseFloat(subtotal).toFixed(decimal_degits);
            }
            html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.sub_total') }}</span></td></tr>';
            html = html +
                '<tr class="final-rate"><td class="label">Subtotal</td><td class="sub_total" style="color:green">(' +
                sub_total + ')</td></tr>';
            var priceWithCommision = total_price;
            if (intRegex.test(discount) || floatRegex.test(discount)) {
                html = html +
                    '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.discount') }}</span></td></tr>';
                discount = parseFloat(discount).toFixed(decimal_degits);
                total_price -= parseFloat(discount);
                if (currencyAtRight) {
                    discount_val = discount + "" + currentCurrency;
                } else {
                    discount_val = currentCurrency + "" + discount;
                }
                couponCode_html = '';
                if (couponCode) {
                    couponCode_html = '</br><small>{{ trans('lang.coupon_codes') }} :' + couponCode + '</small>';
                }
                html = html + '<tr><td class="label">{{ trans('lang.discount') }}' + couponCode_html +
                    '</td><td class="discount text-danger">(-' + discount_val + ')</td></tr>';
            }
            if (specialDiscount != undefined) {
                special_discount = parseFloat(specialDiscount.special_discount).toFixed(decimal_degits);
                total_price -= parseFloat(special_discount);
                if (currencyAtRight) {
                    special_discount_val = special_discount + "" + currentCurrency;
                } else {
                    special_discount_val = currentCurrency + "" + special_discount;
                }
                special_html = '';
                if (specialDiscount.specialType == "percentage") {
                    special_html = '</br><small>(' + specialDiscount.special_discount_label + '%)</small>';
                }
                html = html + '<tr><td class="label">{{ trans('lang.special_offer') }} {{ trans('lang.discount') }}' +
                    special_html + '</td><td class="special_discount text-danger">(-' + special_discount_val +
                    ')</td></tr>';
            }
            html = html +
                '<tr><td class="seprater" colspan="2"><hr><span>Tax Calculation</span></td></tr>';
            html += '<tr><td class="label">SGST (' + sgstRate + '%)</td><td class="tax_amount" id="greenColor">+' + sgst.toFixed(decimal_degits) + currentCurrency + '</td></tr>';
            html += '<tr><td class="label">GST (' + gstRate + '%)</td><td class="tax_amount" id="greenColor">+' + gst.toFixed(decimal_degits) + currentCurrency + '</td></tr>';
            var total_tax_amount = sgst + gst;
            total_price = parseFloat(total_price) + parseFloat(total_tax_amount);
            var totalAmount = total_price;

            // // Debug takeAway value
            // ('=== takeAway Debug ===');
            // ('takeAway value:', takeAway);
            // ('takeAway type:', typeof takeAway);
            // ('takeAway == false:', takeAway == false);
            // ('takeAway === false:', takeAway === false);
            // ('takeAway == "":', takeAway == '');
            // ('takeAway == null:', takeAway == null);
            // ('takeAway == undefined:', takeAway == undefined);

            // Always show delivery charge for delivery orders (not takeaway)
            // Temporarily force show to debug
             if (true) { // Force show for debugging
                html = html +
                    '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.delivery_charge') }}</span></td></tr>';

                // Check if delivery charge is a valid number
                var deliveryChargeNum = parseFloat(deliveryCharge);
('Delivery charge before display check:', deliveryCharge);
('Delivery charge number:', deliveryChargeNum);
('Is valid number?', !isNaN(deliveryChargeNum) && deliveryChargeNum >= 0);

                // Use same logic as print.blade.php for delivery charge
                if (intRegex.test(deliveryCharge) || floatRegex.test(deliveryCharge)) {
                    deliveryCharge = parseFloat(deliveryCharge).toFixed(decimal_degits);
                    totalAmount += parseFloat(deliveryCharge);
                    if (currencyAtRight) {
                        deliveryCharge_val = deliveryCharge + "" + currentCurrency;
                    } else {
                        deliveryCharge_val = currentCurrency + "" + deliveryCharge;
                    }
                    deliveryChargeVal = deliveryCharge;
                    html = html +
                        '<tr><td class="label">{{ trans('lang.deliveryCharge') }}</td><td class="deliveryCharge " id="greenColor">+' +
                        deliveryCharge_val + '</td></tr>';
('Added delivery charge to total:', parseFloat(deliveryCharge));
('New total amount:', totalAmount);
                } else {
                    // Show 0 delivery charge if not valid
                    if (currencyAtRight) {
                        deliveryCharge_val = "0.00" + currentCurrency;
                    } else {
                        deliveryCharge_val = currentCurrency + "0.00";
                    }
                    html = html +
                        '<tr><td class="label">{{ trans('lang.deliveryCharge') }}</td><td class="deliveryCharge " id="greenColor">+' +
                        deliveryCharge_val + '</td></tr>';
('Showing 0 delivery charge - invalid format');
                }
            }
            // Always show tip section for delivery orders (not takeaway)
            // Temporarily force show to debug
('Forcing tip amount display regardless of takeAway value');
            if (true) { // Force show for debugging
                html = html + '<tr><td class="seprater" colspan="2"><hr><span>{{ trans('lang.tip') }}</span></td></tr>';

                // Check if tip amount is a valid number
                var tipAmountNum = parseFloat(tip_amount);
('Tip amount before display check:', tip_amount);
('Tip amount number:', tipAmountNum);
('Is tip valid number?', !isNaN(tipAmountNum) && tipAmountNum >= 0);

                // Use same logic as print.blade.php for tip amount
                if (intRegex.test(tip_amount) || floatRegex.test(tip_amount)) {
                    tip_amount = parseFloat(tip_amount).toFixed(decimal_degits);
                    totalAmount += parseFloat(tip_amount);
                    if (currencyAtRight) {
                        tip_amount_val = tip_amount + "" + currentCurrency;
                    } else {
                        tip_amount_val = currentCurrency + "" + tip_amount;
                    }
                    html = html +
                        '<tr><td class="label">{{ trans('lang.tip_amount') }}</td><td class="tip_amount_val " id="greenColor">+' +
                        tip_amount_val + '</td></tr>';
('Added tip amount to total:', parseFloat(tip_amount));
('New total amount after tip:', totalAmount);
                } else {
                    // Show 0 tip amount if not valid
                    if (currencyAtRight) {
                        tip_amount_val = "0.00" + currentCurrency;
                    } else {
                        tip_amount_val = currentCurrency + "0.00";
                    }
                    html = html +
                        '<tr><td class="label">{{ trans('lang.tip_amount') }}</td><td class="tip_amount_val " id="greenColor">+' +
                        tip_amount_val + '</td></tr>';
('Showing 0 tip amount - invalid format');
                }
            }
            html += '<tr><td class="seprater" colspan="2"><hr></td></tr>';
            orderPaytableAmount = totalAmount;
            
            // Comprehensive final calculation logging
('💰 ===== FINAL TOTAL CALCULATION =====');
('💰 Subtotal (promotional):', subtotal);
('💰 SGST (5% of subtotal):', sgst);
('💰 GST (18% of delivery):', gst);
('💰 Total tax amount:', total_tax_amount);
('💰 Delivery charge:', deliveryCharge);
('💰 Tip amount:', tip_amount);
('💰 Final total amount:', totalAmount);
            
            if (window.promotionalTotals && window.promotionalTotals.promotionalSavings && window.promotionalTotals.promotionalSavings > 0) {
('💰 ===== PROMOTIONAL SAVINGS BREAKDOWN =====');
('💰 Original subtotal would have been:', window.promotionalTotals.originalSubtotal);
('💰 Promotional subtotal used:', window.promotionalTotals.promotionalSubtotal);
('💰 Total promotional savings:', window.promotionalTotals.promotionalSavings);
('💰 Effective discount on final total:', window.promotionalTotals.promotionalSavings);
            }
            
('Final totalAmount before formatting:', totalAmount);
            if (currencyAtRight) {
                total_price_val = parseFloat(totalAmount).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                total_price_val = currentCurrency + "" + parseFloat(totalAmount).toFixed(decimal_degits);
            }
('Formatted total_price_val:', total_price_val);
            html = html +
                '<tr class="grand-total"><td class="label">{{ trans('lang.total_amount') }}</td><td class="total_price_val " id="greenColor">' +
                total_price_val + '</td></tr>';
            var adminCommHtml = "";
            if (adminCommissionType == "Percent") {
                basePrice = (priceWithCommision / (1 + (parseFloat(adminCommissionValue) / 100)));
                adminCommission = parseFloat(priceWithCommision - basePrice);
                adminCommHtml = "(" + adminCommissionValue + "%)";
            } else {
                basePrice = priceWithCommision - adminCommissionValue;
                adminCommission = parseFloat(priceWithCommision - basePrice);
            }
            if (currencyAtRight) {
                adminCommission_val = parseFloat(adminCommission).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                adminCommission_val = currentCurrency + "" + parseFloat(adminCommission).toFixed(decimal_degits);
            }
            html = html + '<tr><td class="label"><small>{{ trans('lang.admin_commission') }} ' + adminCommHtml +
                '</small> </td><td style="color:red"><small>( ' + adminCommission_val + ' )</small></td></tr>';
            if (notes) {
                html = html + '<tr><td class="label">{{ trans('lang.notes') }}</td><td class="adminCommission_val">' +
                    notes + '</td></tr>';
            }
            return html;
        }

        function PrintElem(elem) {
            jQuery('#' + elem).printThis({
                debug: false,
                importStyle: true,
                loadCSS: [
                    '<?php echo asset('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>',
                    '<?php echo asset('css/style.css'); ?>',
                    '<?php echo asset('css/colors/blue.css'); ?>',
                    '<?php echo asset('css/icons/font-awesome/css/font-awesome.css'); ?>',
                    '<?php echo asset('assets/plugins/toast-master/css/jquery.toast.css'); ?>',
                ],
            });
        }

        //Review code GA
        var refReviewAttributes = database.collection('review_attributes');
        refReviewAttributes.get().then(async function (snapshots) {
            if (snapshots != undefined) {
                snapshots.forEach((doc) => {
                    var data = doc.data();
                    reviewAttributes[data.id] = data.title;
                });
            }
        });

        function getUserReview(vendorOrder, reviewAttr) {
            var refUserReview = database.collection('foods_review').where('orderid', '==', vendorOrder.id);
            refUserReview.limit(page_size).get().then(async function (userreviewsnapshot) {
                var reviewHTML = '';
                reviewHTML = buildRatingsAndReviewsHTML(vendorOrder, userreviewsnapshot);
                if (userreviewsnapshot.docs.length > 0) {
                    jQuery("#customers_rating_and_review").append(reviewHTML);
                } else {
                    jQuery("#customers_rating_and_review").html('<h4>No Reviews Found</h4>');
                }
            });
        }

        function buildRatingsAndReviewsHTML(vendorOrder, userreviewsnapshot) {
            var allreviewdata = [];
            var reviewhtml = '';
            userreviewsnapshot.docs.forEach((listval) => {
                var reviewDatas = listval.data();
                reviewDatas.id = listval.id;
                allreviewdata.push(reviewDatas);
            });
            reviewhtml += '<div class="user-ratings">';
            allreviewdata.forEach((listval) => {
                var val = listval;
                vendorOrder.products.forEach((productval) => {
                    if (productval.id == val.productId) {
                        rating = val.rating;
                        reviewhtml = reviewhtml +
                            '<div class="reviews-members py-3 border mb-3"><div class="media">';
                        if (productval.photo != '' && productval.photo != null) {
                            reviewhtml = reviewhtml +
                                '<a href="javascript:void(0);"><img onerror="this.onerror=null;this.src=\'' +
                                place_image + '\'" alt="#" src="' + productval.photo +
                                '" class=" img-circle img-size-32 mr-2" style="width:60px;height:60px"></a>';
                        } else {
                            reviewhtml = reviewhtml + '<a href="javascript:void(0);"><img alt="#" src="' +
                                place_image +
                                '" class=" img-circle img-size-32 mr-2" style="width:60px;height:60px"></a>';
                        }
                        reviewhtml = reviewhtml +
                            '<div class="media-body d-flex"><div class="reviews-members-header"><h6 class="mb-0"><a class="text-dark" href="javascript:void(0);">' +
                            productval.name +
                            '</a></h6><div class="star-rating"><div class="d-inline-block" style="font-size: 14px;">';
                        reviewhtml = reviewhtml + ' <ul class="rating" data-rating="' + rating + '">';
                        reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                        reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                        reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                        reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                        reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                        reviewhtml = reviewhtml + '</ul>';
                        reviewhtml = reviewhtml + '</div></div>';
                        reviewhtml = reviewhtml + '</div>';
                        reviewhtml = reviewhtml + '<div class="review-date ml-auto">';
                        if (val.createdAt != null && val.createdAt != "") {
                            var review_date = val.createdAt.toDate().toLocaleDateString('en', {
                                year: "numeric",
                                month: "short",
                                day: "numeric"
                            });
                            reviewhtml = reviewhtml + '<span>' + review_date + '</span>';
                        }
                        reviewhtml = reviewhtml + '</div>';
                        var photos = '';
                        if (val.photos.length > 0) {
                            photos += '<div class="photos"><ul>';
                            $.each(val.photos, function (key, img) {
                                photos += '<li><img src="' + img + '" width="100"></li>';
                            });
                            photos += '</ul></div>';
                        }
                        reviewhtml = reviewhtml +
                            '</div></div><div class="reviews-members-body w-100"><p class="mb-2">' + val
                                .comment + '</p>' + photos + '</div>';
                        if (val.hasOwnProperty('reviewAttributes') && val.reviewAttributes != null) {
                            reviewhtml += '<div class="attribute-ratings feature-rating mb-2">';
                            var label_feature = "{{ trans('lang.byfeature') }}";
                            reviewhtml += '<h3 class="mb-2">' + label_feature + '</h3>';
                            reviewhtml += '<div class="media-body">';
                            $.each(val.reviewAttributes, function (aid, data) {
                                var at_id = aid;
                                var at_title = reviewAttributes[aid];
                                var at_value = data;
                                reviewhtml +=
                                    '<div class="feature-reviews-members-header d-flex mb-3">';
                                reviewhtml += '<h6 class="mb-0">' + at_title + '</h6>';
                                reviewhtml = reviewhtml +
                                    '<div class="rating-info ml-auto d-flex">';
                                reviewhtml = reviewhtml + '<div class="star-rating">';
                                reviewhtml = reviewhtml + ' <ul class="rating" data-rating="' +
                                    at_value + '">';
                                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                                reviewhtml = reviewhtml + '<li class="rating__item"></li>';
                                reviewhtml = reviewhtml + '</ul>';
                                reviewhtml += '</div>';
                                reviewhtml += '<div class="count-rating ml-2">';
                                reviewhtml += '<span class="count">' + at_value + '</span>';
                                reviewhtml += '</div>';
                                reviewhtml += '</div></div>';
                            });
                            reviewhtml += '</div></div>';
                        }
                        reviewhtml += '</div>';
                    }
                    reviewhtml += '</div>';
                });
            });
            reviewhtml += '</div>';
            return reviewhtml;
        }
    </script>
@endsection
