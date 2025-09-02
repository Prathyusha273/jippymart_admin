@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">Edit Mart Banner Item</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{!! route('mart.banners') !!}">Mart Banner Items</a></li>
                <li class="breadcrumb-item active">Edit Banner</li>
            </ol>
        </div>
    </div>
    <div class="card-body">
        <div class="error_top"></div>
        <div class="row restaurant_payout_create">
            <div class="restaurant_payout_create-inner">
                <fieldset>
                    <legend>Mart Banner Item Details</legend>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Title *</label>
                        <div class="col-7">
                            <input type="text" class="form-control title" placeholder="Enter banner title">
                        </div>
                    </div>
                    <div class="form-group row width-100">
                        <label class="col-3 control-label">Description</label>
                        <div class="col-7">
                            <textarea class="form-control description" rows="3" placeholder="Enter banner description"></textarea>
                        </div>
                    </div>
                    <div class="form-group row width-100">
                        <label class="col-3 control-label">Text</label>
                        <div class="col-7">
                            <textarea class="form-control text" rows="3" placeholder="Enter additional text content"></textarea>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Set Order</label>
                        <div class="col-7">
                            <input type="number" class="form-control set_order" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group row width-100">
                        <div class="form-check width-100">
                            <input type="checkbox" id="is_publish">
                            <label class="col-3 control-label" for="is_publish">Publish</label>
                        </div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Photo</label>
                        <input type="file" onChange="handleFileSelect(event)" class="col-7" accept="image/*">
                        <div id="uploding_image"></div>
                        <div class="placeholder_img_thumb user_image"></div>
                    </div>
                    <div class="form-group row width-50">
                        <label class="col-3 control-label">Position</label>
                        <div class="col-7">
                            <select name="position" id="position" class="form-control">
                                <option value="top">Top</option>
                                <option value="middle">Middle</option>
                                <option value="bottom">Bottom</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-100 radio-form-row d-flex" id="redirect_type_div">
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="store" name="redirect_type" id="store">
                            <label class="custom-control-label">Store</label>
                        </div>
                        <div class="radio-form col-md-2">
                            <input type="radio" class="redirect_type" value="product" name="redirect_type" id="product">
                            <label class="custom-control-label">Product</label>
                        </div>
                        <div class="radio-form col-md-4">
                            <input type="radio" class="redirect_type" value="external_link" name="redirect_type" id="external_links">
                            <label class="custom-control-label">External Link</label>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="vendor_div" style="display: none;">
                        <label class="col-3 control-label">Store</label>
                        <div class="col-7">
                            <select name="storeId" id="storeId" class="form-control">
                                <option value="">Select Store</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-50" id="product_div" style="display: none;">
                        <label class="col-3 control-label">Product</label>
                        <div class="col-7">
                            <select name="productId" id="productId" class="form-control">
                                <option value="">Select Product</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row width-100" id="external_link_div" style="display: none;">
                        <label class="col-3 control-label">External Link</label>
                        <div class="col-7">
                            <input type="text" class="form-control extlink" id="external_link" placeholder="https://example.com">
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <div class="form-group col-12 text-center">
        <button type="button" class="btn btn-primary edit-mart-banner-btn"><i class="fa fa-save"></i> Save</button>
        <a href="{!! route('mart.banners') !!}" class="btn btn-default"><i class="fa fa-undo"></i>Cancel</a>
    </div>
</div>
@endsection
@section('scripts')
<!-- Load toastr library -->
<script src="{{ asset('js/toastr.js') }}"></script>

<script>
    var database = firebase.firestore();
    var storage = firebase.storage();
    var photo = '';
    var new_added_photos = [];
    var bannerImageFile = "";
    var id = '{{ $id }}';

    $(document).ready(function() {
        // Load stores for store redirect
        loadStores();
        
        // Load products for product redirect
        loadProducts();

        // Load existing banner data
        loadBannerData();

        // Handle redirect type change
        $('.redirect_type').on('change', function() {
            var redirectType = $(this).val();
            $('#vendor_div, #product_div, #external_link_div').hide();
            
            if (redirectType === 'store') {
                $('#vendor_div').show();
            } else if (redirectType === 'product') {
                $('#product_div').show();
            } else if (redirectType === 'external_link') {
                $('#external_link_div').show();
            }
        });

        // Handle save button click
        $('.edit-mart-banner-btn').on('click', function() {
            updateMartBanner();
        });
    });

    // Load existing banner data
    async function loadBannerData() {
        try {
            const doc = await database.collection('mart_banners').doc(id).get();
            if (doc.exists) {
                const bannerData = doc.data();
                
                // Populate form fields
                $('.title').val(bannerData.title || '');
                $('.description').val(bannerData.description || '');
                $('.text').val(bannerData.text || '');
                $('.set_order').val(bannerData.set_order || 0);
                $('#is_publish').prop('checked', bannerData.is_publish !== false);
                $('#position').val(bannerData.position || 'top');
                
                // Set redirect type
                $('.redirect_type[value="' + (bannerData.redirect_type || 'external_link') + '"]').prop('checked', true);
                
                // Set redirect specific values
                if (bannerData.redirect_type === 'store') {
                    $('#storeId').val(bannerData.storeId || '');
                    $('#vendor_div').show();
                } else if (bannerData.redirect_type === 'product') {
                    $('#productId').val(bannerData.productId || '');
                    $('#product_div').show();
                } else if (bannerData.redirect_type === 'external_link') {
                    $('#external_link').val(bannerData.external_link || '');
                    $('#external_link_div').show();
                }
                
                // Load image if exists
                if (bannerData.photo) {
                    bannerImageFile = bannerData.photo;
                    $('.user_image').html('<img src="' + bannerData.photo + '" style="max-width: 100px; max-height: 100px; border-radius: 4px;">');
                    photo = bannerData.photo;
                }
                
            } else {
                toastr.error('Banner not found');
                setTimeout(() => {
                    window.location.href = '{{ route("mart.banners") }}';
                }, 2000);
            }
        } catch (error) {
            console.error('Error loading banner data:', error);
            toastr.error('Error loading banner data');
        }
    }

    // Load stores for store redirect
    function loadStores() {
        $('#storeId').html("");
        $('#storeId').append($("<option value=''>Select Store</option>"));
        
        var ref_vendors = database.collection('vendors');
        ref_vendors.get().then(async function(snapshots) {
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                $('#storeId').append($("<option></option>")
                    .attr("value", data.id)
                    .text(data.title));
            });
        }).catch(function(error) {
            console.error('Error loading stores:', error);
        });
    }

    // Load products for product redirect
    function loadProducts() {
        $('#productId').html("");
        $('#productId').append($("<option value=''>Select Product</option>"));
        
        var ref_products = database.collection('mart_items');
        ref_products.get().then(async function(snapshots) {
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                $('#productId').append($("<option></option>")
                    .attr("value", data.id)
                    .text(data.name));
            });
        }).catch(function(error) {
            console.error('Error loading products:', error);
        });
    }

    // Handle file selection
    function handleFileSelect(event) {
        var file = event.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                alert('File size should be less than 5MB');
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                $('.user_image').html('<img src="' + e.target.result + '" style="max-width: 100px; max-height: 100px; border-radius: 4px;">');
                photo = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    // Update mart banner
    async function updateMartBanner() {
        var title = $('.title').val();
        var description = $('.description').val();
        var text = $('.text').val();
        var setOrder = $('.set_order').val();
        var isPublish = $('#is_publish').is(':checked');
        var position = $('#position').val();
        var redirectType = $('.redirect_type:checked').val();
        var storeId = $('#storeId').val();
        var productId = $('#productId').val();
        var externalLink = $('#external_link').val();

        // Validation
        if (!title) {
            $('.error_top').html('<p>Please enter banner title</p>');
            return;
        }

        // Prepare banner data
        var bannerData = {
            title: title,
            description: description,
            text: text,
            photo: photo,
            set_order: parseInt(setOrder) || 0,
            is_publish: isPublish,
            position: position,
            redirect_type: redirectType,
            storeId: redirectType === 'store' ? storeId : null,
            productId: redirectType === 'product' ? productId : null,
            external_link: redirectType === 'external_link' ? externalLink : null
        };

        // Show loading
        $('.edit-mart-banner-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        try {
            // Update in Firestore
            await database.collection('mart_banners').doc(id).update({
                ...bannerData,
                updated_at: firebase.firestore.FieldValue.serverTimestamp()
            });

            // Log activity
            await logActivity('mart_banner_items', 'updated', 'Updated mart banner item: ' + title);

            // Success message and redirect
            toastr.success('Mart banner item updated successfully');
            setTimeout(() => {
                window.location.href = '{{ route("mart.banners") }}';
            }, 1500);

        } catch (error) {
            console.error('Error updating banner:', error);
            $('.error_top').html('<p>Error updating banner: ' + error.message + '</p>');
            $('.edit-mart-banner-btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
        }
    }

    // Activity logging function
    async function logActivity(module, action, description) {
        try {
            await database.collection('activity_logs').add({
                module: module,
                action: action,
                description: description,
                user_id: '{{ auth()->id() }}',
                user_name: '{{ auth()->user()->name }}',
                timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                ip_address: '{{ request()->ip() }}',
                user_agent: '{{ request()->userAgent() }}'
            });
        } catch (error) {
            console.error('Error logging activity:', error);
        }
    }
</script>
@endsection
