@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Mart Sub-Categories</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('mart-categories') !!}">Mart Categories</a></li>
                    <li class="breadcrumb-item"><a href="#" id="subcategoriesLink">Sub-Categories</a></li>
                    <li class="breadcrumb-item active">Edit Sub-Category</li>
                </ol>
            </div>
        </div>
        <div class="container-fluid">
            <div class="cat-edite-page max-width-box">
                <div class="card  pb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs align-items-end card-header-tabs w-100">
                            <li role="presentation" class="nav-item">
                                <a href="#subcategory_information" aria-controls="description" role="tab" data-toggle="tab"
                                   class="nav-link active">Sub-Category Information</a>
                            </li>
                            <li role="presentation" class="nav-item">
                                <a href="#review_attributes" aria-controls="review_attributes" role="tab" data-toggle="tab"
                                   class="nav-link">{{trans('lang.reviewattribute_plural')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="error_top" style="display:none"></div>
                        <div class="row restaurant_payout_create" role="tabpanel">
                            <div class="restaurant_payout_create-inner tab-content">
                                <div role="tabpanel" class="tab-pane active" id="subcategory_information">
                                    <fieldset>
                                        <legend>Edit Mart Sub-Category</legend>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Sub-Category Name</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control subcategory-name">
                                                <div class="form-text text-muted">Enter the name for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label ">Sub-Category Description</label>
                                            <div class="col-7">
                            <textarea rows="7" class="subcategory_description form-control"
                                      id="subcategory_description"></textarea>
                                                <div class="form-text text-muted">Enter a description for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Sub-Category Image</label>
                                            <div class="col-7">
                                                <input type="file" id="subcategory_image">
                                                <div class="placeholder_img_thumb subcategory_image"></div>
                                                <div id="uploding_image"></div>
                                                <div class="form-text text-muted w-50">Upload an image for this sub-category
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Order</label>
                                            <div class="col-7">
                                                <input type="number" class="form-control" id="subcategory_order" value="1" min="1">
                                                <div class="form-text text-muted w-50">Display order within parent category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Section</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="section_info" readonly>
                                                <div class="form-text text-muted w-50">Inherited from parent category</div>
                                            </div>
                                        </div>
                                        <div class="form-group row width-100">
                                            <label class="col-3 control-label">Parent Category</label>
                                            <div class="col-7">
                                                <input type="text" class="form-control" id="parent_category_info" readonly>
                                                <div class="form-text text-muted w-50">Parent category information</div>
                                            </div>
                                        </div>
                                       <div class="form-check row width-100">
                                        <input type="checkbox" class="item_publish" id="item_publish">
                                        <label class="col-3 control-label"
                                               for="item_publish">Publish</label>
                                       </div>
                                        <div class="form-check row width-100" id="show_in_home">
                                            <input type="checkbox" id="show_in_homepage">
                                            <label class="col-3 control-label" for="show_in_homepage">{{trans('lang.show_in_home')}}</label>
                                            <div class="form-text text-muted w-50">{{trans('lang.show_in_home_desc')}}<span id="forsection"></span></div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="review_attributes">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary save-setting-btn"><i class="fa fa-save"></i>
                            {{trans('lang.save')}}
                        </button>
                        <a href="#" id="backToSubcategories" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
<script>
    var database = firebase.firestore();
    var subcategoryId = "{{ $id }}";
    var ref = database.collection('mart_subcategories');
    var photo = "";
    var fileName='';
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    var ref_review_attributes = database.collection('review_attributes');
    var subcategoryData = null;
    var oldImageUrl = '';
    
    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    
    $(document).ready(function () {
        jQuery("#data-table_processing").show();
        // Load sub-category data
        loadSubCategoryData();
        
        // Load review attributes
        loadReviewAttributes();
        
        $(".save-setting-btn").click(async function () {
            var title = $(".subcategory-name").val();
            var description = $(".subcategory_description").val();
            var item_publish = $("#item_publish").is(":checked");
            var show_in_homepage = $("#show_in_homepage").is(":checked");
            var review_attributes = [];
            $('#review_attributes input').each(function () {
                if ($(this).is(':checked')) {
                    review_attributes.push($(this).val());
                }
            });
            if (title == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>Please enter a sub-category name</p>");
                window.scrollTo(0, 0);
            } else {
                jQuery("#data-table_processing").show();
                storeImageData().then(IMG => {
                    var updateData = {
                        'title': title,
                        'description': description,
                        'photo': IMG,
                        'subcategory_order': parseInt($('#subcategory_order').val()) || 1,
                        'review_attributes': review_attributes,
                        'publish': item_publish,
                        'show_in_homepage': show_in_homepage,
                    };
                    
                    database.collection('mart_subcategories').doc(subcategoryId).update(updateData).then(async function (result) {
                        console.log('✅ Sub-category updated successfully, now logging activity...');
                        try {
                            if (typeof logActivity === 'function') {
                                console.log('🔍 Calling logActivity for sub-category update...');
                                await logActivity('mart_subcategories', 'updated', 'Updated sub-category: ' + title);
                                console.log('✅ Activity logging completed successfully');
                            } else {
                                console.error('❌ logActivity function is not available');
                            }
                        } catch (error) {
                            console.error('❌ Error calling logActivity:', error);
                        }
                        
                        // Delete old image if new image was uploaded
                        if (photo && oldImageUrl && oldImageUrl !== placeholderImage && oldImageUrl !== IMG) {
                            try {
                                await storage.refFromURL(oldImageUrl).delete();
                                console.log('✅ Old image deleted successfully');
                            } catch (error) {
                                console.error('❌ Error deleting old image:', error);
                            }
                        }
                        
                        jQuery("#data-table_processing").hide();
                        var subcategoriesUrl = '{{ route("mart-subcategories.index", ["category_id" => ":category_id"]) }}'.replace(':category_id', subcategoryData.parent_category_id);
                        window.location.href = subcategoriesUrl;
                    }).catch(function (error) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                    });
                });
            }
        });
    });

    // Load sub-category data
    function loadSubCategoryData() {
        database.collection('mart_subcategories').doc(subcategoryId).get().then(function(doc) {
            if (doc.exists) {
                subcategoryData = doc.data();
                oldImageUrl = subcategoryData.photo;
                
                // Populate form fields
                $('.subcategory-name').val(subcategoryData.title);
                $('.subcategory_description').val(subcategoryData.description);
                $('#subcategory_order').val(subcategoryData.subcategory_order || 1);
                $('#item_publish').prop('checked', subcategoryData.publish);
                $('#show_in_homepage').prop('checked', subcategoryData.show_in_homepage);
                $('#section_info').val(subcategoryData.section || 'General');
                $('#parent_category_info').val(subcategoryData.parent_category_title);
                
                // Show current image
                if (subcategoryData.photo && subcategoryData.photo !== placeholderImage) {
                    $('.subcategory_image').html('<img class="rounded" style="width:50px" src="' + subcategoryData.photo + '" alt="image">');
                }
                
                // Update navigation links
                var subcategoriesUrl = '{{ route("mart-subcategories.index", ["category_id" => ":category_id"]) }}'.replace(':category_id', subcategoryData.parent_category_id);
                $('#subcategoriesLink').attr('href', subcategoriesUrl);
                $('#backToSubcategories').attr('href', subcategoriesUrl);
                
            } else {
                alert('Sub-category not found');
                window.history.back();
            }
        }).catch(function(error) {
            console.error('Error loading sub-category:', error);
            alert('Error loading sub-category data');
        });
    }

    // Load review attributes
    function loadReviewAttributes() {
        ref_review_attributes.get().then(async function (snapshots) {
            var ra_html = '';
            snapshots.docs.forEach((listval) => {
                var data = listval.data();
                var isChecked = subcategoryData && subcategoryData.review_attributes && subcategoryData.review_attributes.includes(data.id) ? 'checked' : '';
                ra_html += '<div class="form-check width-100">';
                ra_html += '<input type="checkbox" id="review_attribute_' + data.id + '" value="' + data.id + '" ' + isChecked + '>';
                ra_html += '<label class="col-3 control-label" for="review_attribute_' + data.id + '">' + data.title + '</label>';
                ra_html += '</div>';
            });
            $('#review_attributes').html(ra_html);
        });
    }

    var storageRef = firebase.storage().ref('images');
    async function storeImageData() {
        var newPhoto = '';
        try {
            if (photo) {
                photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', {contentType: 'image/jpg'});
                var downloadURL = await uploadTask.ref.getDownloadURL();
                newPhoto = downloadURL;
                photo = downloadURL;
            } else {
                newPhoto = oldImageUrl || placeholderImage;
            }
        } catch (error) {
            console.log("ERR ===", error);
            newPhoto = oldImageUrl || placeholderImage;
        }
        return newPhoto;
    }

    //upload image with compression
    $("#subcategory_image").resizeImg({
        callback: function(base64str) {
            var val = $('#subcategory_image').val().toLowerCase();
            var ext = val.split('.')[1];
            var docName = val.split('fakepath')[1];
            var filename = $('#subcategory_image').val().replace(/C:\\fakepath\\/i, '')
            var timestamp = Number(new Date());
            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
            photo = base64str;
            fileName=filename;
            $(".subcategory_image").empty();
            $(".subcategory_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
            $("#subcategory_image").val('');
        }
    });
</script>
@endsection
