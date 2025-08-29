@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">Mart Items</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">{{trans('lang.dashboard')}}</a></li>
                    <?php if(isset($_GET['eid']) && $_GET['eid'] != ''){?>
                    <li class="breadcrumb-item"><a
                                href="{{route('marts.mart-items',$_GET['eid'])}}">{{trans('lang.mart_item_plural')}}</a>
                    </li>
                    <?php }else{ ?>
                    <li class="breadcrumb-item"><a href="{!! route('mart-items') !!}">Mart Items</a></li>
                    <?php } ?>
                    <li class="breadcrumb-item active">{{trans('lang.mart_item_edit')}}</li>
                </ol>
            </div>
        </div>
        <div>
            <div class="card-body">
                <div class="error_top" style="display:none"></div>
                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">
                        <fieldset>
                            <legend>Mart Item Information</legend>
                            <div class="form-group row width-100" id="admin_commision_info" style="display:none">
                                <div class="m-3">
                                    <div class="form-text font-weight-bold text-danger h6">{{trans('lang.price_instruction')}}</div>
                                    <div class="form-text font-weight-bold text-danger h6" id="admin_commision"></div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">Item Name</label>
                                <div class="col-7">
                                    <input type="text" class="form-control food_name" required>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.food_name_help") }}
                                    </div>
                                </div>
                            </div>
                                                    <div class="form-group row width-50">
                            <label class="col-3 control-label">Price</label>
                            <div class="col-7">
                                <input type="text" class="form-control food_price" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" required>
                            </div>
                        </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">Discount Price</label>
                                <div class="col-7">
                                    <input type="text" class="form-control food_discount" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');">
                                    <div class="form-text text-muted">
                                        {{ trans("lang.food_discount_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50 food_restaurant_div">
                                <label class="col-3 control-label">Mart</label>
                                <div class="col-7">
                                    <select id="food_restaurant" class="form-control" required>
                                        <option value="">Select Mart</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        Select the mart where this item will be available
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.food_category_id')}}</label>
                                <div class="col-7">
                                    <select id='food_category' class="form-control" required>
                                        <option value="">{{trans('lang.select_category')}}</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.food_category_id_help") }}
                                    </div>
                                </div>
                            </div> -->
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">Mart Categories</label>
                                <div class="col-7">
                                    <div id="selected_categories" class="mb-2"></div>
                                    <input type="text" id="food_category_search" class="form-control mb-2" placeholder="Search mart categories...">
                                    <select id='food_category' class="form-control" multiple required>
                                        <option value="">Select mart categories</option>
                                        <!-- options populated dynamically -->
                                    </select>
                                    <div class="form-text text-muted">
                                     Select the mart categories for this item
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">Mart Sub-Categories</label>
                                <div class="col-7">
                                    <div id="selected_subcategories" class="mb-2"></div>
                                    <input type="text" id="food_subcategory_search" class="form-control mb-2" placeholder="Search mart sub-categories...">
                                    <select id='food_subcategory' class="form-control" multiple>
                                        <option value="">Select mart sub-categories (optional)</option>
                                        <!-- options populated dynamically -->
                                    </select>
                                    <div class="form-text text-muted">
                                     Select the mart sub-categories for this item (optional)
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.item_quantity')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control item_quantity" value="-1">
                                    <div class="form-text text-muted">
                                        {{ trans("lang.item_quantity_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100" id="attributes_div">
                                <label class="col-3 control-label">{{trans('lang.item_attribute_id')}}</label>
                                <div class="col-7">
                                    <select id='item_attribute' class="form-control chosen-select" required
                                            multiple="multiple"
                                            onchange="selectAttribute();"></select>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <div class="item_attributes" id="item_attributes"></div>
                                <div class="item_variants" id="item_variants"></div>
                                <input type="hidden" id="attributes" value=""/>
                                <input type="hidden" id="variants" value=""/>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">Item Image</label>
                                <div class="col-7">
                                    <input type="file" id="product_image">
                                    <div class="placeholder_img_thumb product_image"></div>
                                    <div id="uploding_image"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.food_image_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <label class="col-3 control-label">Item Description</label>
                                <div class="col-7">
                                    <textarea rows="8" class="form-control food_description"
                                              id="food_description"></textarea>
                                </div>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="food_publish" id="food_publish">
                                <label class="col-3 control-label"
                                       for="food_publish">Publish</label>
                            </div>
                            <!-- Hidden non-veg field for now -->
                            <div class="form-check width-100" style="display: none;">
                                <input type="checkbox" class="food_nonveg" id="food_nonveg">
                                <label class="col-3 control-label" for="food_nonveg">{{ trans('lang.non_veg')}}</label>
                            </div>
                            <div class="form-check width-100" style="display: none">
                                <input type="checkbox" class="food_take_away_option" id="food_take_away_option">
                                <label class="col-3 control-label"
                                       for="food_take_away_option">{{trans('lang.food_take_away')}}</label>
                            </div>
                                                    <div class="form-check width-100">
                            <input type="checkbox" class="food_is_available" id="food_is_available">
                            <label class="col-3 control-label" for="food_is_available">Available</label>
                        </div>
                        
                        <!-- Enhanced Item Features -->
                        <div class="form-group row width-100">
                            <label class="col-3 control-label">Item Features</label>
                            <div class="col-7">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="isSpotlight">
                                            <label class="form-check-label" for="isSpotlight">Spotlight</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="isStealOfMoment">
                                            <label class="form-check-label" for="isStealOfMoment">Steal of Moment</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="isFeature">
                                            <label class="form-check-label" for="isFeature">Featured</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="isTrending">
                                            <label class="form-check-label" for="isTrending">Trending</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="isNew">
                                            <label class="form-check-label" for="isNew">New Arrival</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="isBestSeller">
                                            <label class="form-check-label" for="isBestSeller">Best Seller</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="isSeasonal">
                                            <label class="form-check-label" for="isSeasonal">Seasonal</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text text-muted">
                                    Select item features for better categorization and filtering
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    
                    <!-- Options Configuration -->
                    <fieldset>
                        <legend>Options Configuration</legend>
                        
                        <div class="form-check width-100">
                            <input type="checkbox" class="has_options" id="has_options">
                            <label class="col-3 control-label" for="has_options">
                                <strong>Enable Options for this item</strong>
                            </label>
                            <div class="form-text text-muted">
                                Enable this to create different variants/sizes for this item
                            </div>
                        </div>
                        
                        <div id="options_config" style="display:none;">
                            <div class="alert alert-info">
                                <i class="mdi mdi-information-outline"></i>
                                <strong>Options will be stored as part of this item.</strong>
                                Each option can have its own price, image, and specifications.
                            </div>
                            
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">Default Option</label>
                                <div class="col-7">
                                    <select id="default_option" class="form-control">
                                        <option value="">Select default option</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        The default option will be automatically selected when customers view this item. 
                                        This is typically the featured option or the most popular choice.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    
                    <!-- Options Management -->
                    <fieldset id="options_fieldset" style="display:none;">
                        <legend>Item Options</legend>
                        
                        <div class="options-list">
                            <!-- Dynamic options will be added here -->
                        </div>
                        
                        <div class="form-group row width-100">
                            <div class="col-12 text-center">
                                <button type="button" class="btn btn-primary" onclick="addNewOption()">
                                    <i class="mdi mdi-plus"></i> Add Option
                                </button>
                            </div>
                        </div>
                        
                        <div class="options-summary" style="display:none;">
                            <h5>Options Summary</h5>
                            <div class="summary-content">
                                <!-- Will show price range and option count -->
                            </div>
                        </div>
                    </fieldset>
                        <!-- Hidden ingredients fieldset for now -->
                        <fieldset style="display: none">
                            <legend>{{trans('lang.ingredients')}}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.calories')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control food_calories">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.grams')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control food_grams">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.fats')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control food_fats">
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.proteins')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control food_proteins">
                                </div>
                            </div>
                        </fieldset>
                        <!-- Hidden add-ons fieldset for now -->
                        <fieldset style="display: none;">
                            <legend>{{trans('lang.food_add_one')}}</legend>
                            <div class="form-group add_ons_list extra-row">
                            </div>
                            <div class="form-group row width-100">
                                <div class="col-7">
                                    <button type="button" onclick="addOneFunction()" class="btn btn-primary"
                                            id="add_one_btn">{{trans('lang.food_add_one')}}</button>
                                </div>
                            </div>
                            <div class="form-group row width-100" id="add_ones_div" style="display:none">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="col-3 control-label">{{trans('lang.food_title')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control add_ons_title">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="col-3 control-label">{{trans('lang.food_price')}}</label>
                                        <div class="col-7">
                                            <input type="number" class="form-control add_ons_price">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row save_add_one_btn width-100" style="display:none">
                                <div class="col-7">
                                    <button type="button" onclick="saveAddOneFunction()"
                                            class="btn btn-primary">{{trans('lang.save_add_ones')}}</button>
                                </div>
                            </div>
                        </fieldset>
                        <!-- Hidden product specification fieldset for now -->
                        <fieldset style="display: none">
                            <legend>{{trans('lang.product_specification')}}</legend>
                            <div class="form-group product_specification extra-row">
                                <div class="row" id="product_specification_heading" style="display: none;">
                                    <div class="col-6">
                                        <label class="col-2 control-label">{{trans('lang.lable')}}</label>
                                    </div>
                                    <div class="col-6">
                                        <label class="col-3 control-label">{{trans('lang.value')}}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-100">
                                <div class="col-7">
                                    <button type="button" onclick="addProductSpecificationFunction()"
                                            class="btn btn-primary"
                                            id="add_one_btn"> {{trans('lang.add_product_specification')}}</button>
                                </div>
                            </div>
                            <div class="form-group row width-100" id="add_product_specification_div"
                                 style="display:none">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="col-2 control-label">{{trans('lang.lable')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control add_label">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="col-3 control-label">{{trans('lang.value')}}</label>
                                        <div class="col-7">
                                            <input type="text" class="form-control add_value">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row save_product_specification_btn width-100" style="display:none">
                                <div class="col-7">
                                    <button type="button" onclick="saveProductSpecificationFunction()"
                                            class="btn btn-primary">{{trans('lang.save_product_specification')}}</button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="form-group col-12 text-center btm-btn">
                        <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
                    <?php if(isset($_GET['eid']) && $_GET['eid'] != ''){?>
                        <a href="{{route('marts.mart-items',$_GET['eid'])}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    <?php }else{ ?>
                        <a href="{!! route('foods') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Option Template (Hidden) -->
<div id="option_template" style="display:none;">
    <div class="option-item" data-option-id="">
        <div class="option-header">
            <h5>Option #<span class="option-number"></span></h5>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Option Type</label>
                    <select class="form-control option-type">
                        <option value="size">Size/Weight (kg, g, mg)</option>
                        <option value="volume">Volume (L, ml, cl)</option>
                        <option value="quantity">Quantity (pcs, units)</option>
                        <option value="pack">Pack (dozen, bundle)</option>
                        <option value="bundle">Bundle (mixed items)</option>
                        <option value="addon">Add-on (extras)</option>
                        <option value="variant">Variant (organic, premium)</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label>Option Title</label>
                    <input type="text" class="form-control option-title" placeholder="e.g., Pack of 2">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Option Subtitle</label>
                    <input type="text" class="form-control option-subtitle" placeholder="e.g., 180 g X 2">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label>Price (₹)</label>
                    <input type="number" class="form-control option-price" step="0.01" min="0">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Original Price (₹)</label>
                    <input type="number" class="form-control option-original-price" step="0.01" min="0">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" class="form-control option-quantity" min="0">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Unit</label>
                    <select class="form-control option-quantity-unit">
                        <option value="g">Grams (g)</option>
                        <option value="kg">Kilograms (kg)</option>
                        <option value="mg">Milligrams (mg)</option>
                        <option value="L">Liters (L)</option>
                        <option value="ml">Milliliters (ml)</option>
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="units">Units</option>
                        <option value="dozen">Dozen</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Unit Measure Base</label>
                    <input type="number" class="form-control option-unit-measure" value="100">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Unit Price Display</label>
                    <input type="text" class="form-control option-unit-price-display" readonly>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label>Discount Amount</label>
                    <input type="number" class="form-control option-discount" step="0.01" readonly>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Savings Display</label>
                    <input type="text" class="form-control option-savings-display" readonly>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="form-group">
                    <label>Smart Suggestions</label>
                    <div class="smart-suggestions-display">
                        <small class="text-muted">Select option type for smart defaults</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Option Image</label>
            <input type="file" class="option-image-input" accept="image/*">
            <div class="option-image-preview"></div>
        </div>
        
        <div class="form-check">
            <input type="checkbox" class="option-available" id="option_available_" checked>
            <label class="form-check-label" for="option_available_">Available</label>
        </div>
        
        <div class="form-check">
            <input type="checkbox" class="option-featured" id="option_featured_">
            <label class="form-check-label" for="option_featured_">Featured (Show first)</label>
        </div>
    </div>
</div>

<style>
.option-item {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background: #f9f9f9;
}

.option-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.option-header h5 {
    margin: 0;
    color: #333;
}

.option-image-preview {
    margin-top: 10px;
}

.option-image-preview img {
    border: 1px solid #ddd;
}

/* Visual feedback for option states */
.option-enabled {
    border-color: #28a745 !important;
    background: #f8fff9 !important;
}

.option-disabled {
    border-color: #dc3545 !important;
    background: #fff8f8 !important;
    opacity: 0.7;
}

.option-featured-highlight {
    border-color: #ffc107 !important;
    background: #fffdf0 !important;
    box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
}

/* Enhanced checkbox styling */
.option-item .form-check {
    margin: 15px 0;
    padding: 10px;
    border-radius: 4px;
    background: #f8f9fa;
}

.option-item .form-check input[type="checkbox"] {
    margin-right: 8px;
    transform: scale(1.2);
}

.option-item .form-check label {
    font-weight: 500;
    cursor: pointer;
    margin-bottom: 0;
}

.option-item .form-check:hover {
    background: #e9ecef;
}

.options-summary {
    background: #e8f5e8;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    padding: 15px;
    margin-top: 20px;
}

.summary-content {
    margin: 0;
}

#options_config {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    margin-top: 15px;
}

.selected-subcategory-tag {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    margin: 2px;
    font-size: 12px;
    position: relative;
}

.selected-subcategory-tag .remove-tag {
    margin-left: 5px;
    cursor: pointer;
    font-weight: bold;
}
</style>

@endsection
@section('scripts')
    <script>
        var id = "<?php echo $id;?>";
        var database = firebase.firestore();
                        var ref = database.collection('mart_items').doc(id);
        var storageRef = firebase.storage().ref('images');
        var storage = firebase.storage();
        var photo = "";
        var addOnesTitle = [];
        var addOnesPrice = [];
        var sizeTitle = [];
        var sizePrice = [];
        var attributes_list = [];
        var categories_list = [];
        var restaurant_list = [];
        var photos = [];
        var new_added_photos = [];
        var new_added_photos_filename = [];
        var photosToDelete = [];
        var product_specification = {};
        var placeholderImage = '';
        var productImagesCount = 0;
        var variant_photos=[];
        var variant_filename=[];
        var variantImageToDelete=[];
        var variant_vIds=[];
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        })
        var refCurrency = database.collection('currencies').where('isActive', '==', true);
        refCurrency.get().then(async function (snapshots) {
            var currencyData = snapshots.docs[0].data();
            currentCurrency = currencyData.symbol;
            currencyAtRight = currencyData.symbolAtRight;
            if (currencyData.decimal_degits) {
                decimal_degits = currencyData.decimal_degits;
            }
        });
        var refAdminCommission = database.collection('settings').doc("AdminCommission");
        refAdminCommission.get().then(async function (snapshots) {
            var adminCommissionSettings = snapshots.data();
            if(adminCommissionSettings){
                var commission_type = adminCommissionSettings.commissionType;
                var commission_value = adminCommissionSettings.fix_commission;
                if(commission_type == "Percent"){
                    var commission_text = commission_value+'%';
                }else{
                    if(currencyAtRight){
                        commission_text = parseFloat(commission_value).toFixed(decimal_degits) + "" + currentCurrency;
                    }else{
                        commission_text = currentCurrency + "" + parseFloat(commission_value).toFixed(decimal_degits);
                    }
                }
                if(adminCommissionSettings.isEnabled){
                    $("#admin_commision_info").show();
                    $("#admin_commision").html('Admin Commission: '+commission_text);
                }
            }
        });
        $(document).ready(function () {
            <?php if(isset($_GET['eid']) && $_GET['eid'] != ''){?>
                $(".food_restaurant_div").hide();
            <?php } else{?>
                $(".food_restaurant_div").show();
            <?php } ?>
            $("#attributes_div").show(); 
            jQuery(document).on("click", ".mdi-cloud-upload", function () {
                var variant = jQuery(this).data('variant');
                var fileurl = $('[id="variant_' + variant + '_url"]').val();
                if (fileurl) {
                    variantImageToDelete.push(fileurl);
                }
                var photo_remove = $(this).attr('data-img');
                index = variant_photos.indexOf(photo_remove);
                if (index > -1) {
                    variant_photos.splice(index, 1); // 2nd parameter means remove one item only
                }
                var file_remove = $(this).attr('data-file');
                fileindex = variant_filename.indexOf(file_remove);
                if (fileindex > -1) {
                    variant_filename.splice(fileindex, 1); // 2nd parameter means remove one item only
                }
                variantindex = variant_vIds.indexOf(variant);
                if (variantindex > -1) {
                    variant_vIds.splice(variantindex, 1); // 2nd parameter means remove one item only
                }
                $('[id="variant_' + variant + '_url"]').val('');
                $('[id="file_' + variant + '"]').click();
            });
            jQuery(document).on("click", ".mdi-delete", function () {
                var variant = jQuery(this).data('variant');
                var fileurl = $('[id="variant_' + variant + '_url"]').val();
                if (fileurl) {
                    variantImageToDelete.push(fileurl);
                }
                var photo_remove = $(this).attr('data-img');
                index = variant_photos.indexOf(photo_remove);
                if (index > -1) {
                    variant_photos.splice(index, 1); // 2nd parameter means remove one item only
                }
                var file_remove = $(this).attr('data-file');
                fileindex = variant_filename.indexOf(file_remove);
                if (fileindex > -1) {
                    variant_filename.splice(fileindex, 1); // 2nd parameter means remove one item only
                }
                variantindex = variant_vIds.indexOf(variant);
                if (variantindex > -1) {
                    variant_vIds.splice(variantindex, 1); // 2nd parameter means remove one item only
                }
                $('[id="variant_' + variant + '_image"]').empty();
                $('[id="variant_' + variant + '_url"]').val('');
            });
            jQuery("#data-table_processing").show();
            ref.get().then(async function (snapshots) {
                var product = snapshots.data();
                // Fetch vendors with vType: 'mart' (case-insensitive)
                database.collection('vendors').get().then(async function (snapshots) {
                    console.log('🔍 Edit page - Found ' + snapshots.docs.length + ' total vendors');
                    snapshots.docs.forEach((listval) => {
                        var data = listval.data();
                        // Check for mart vendors (case-insensitive)
                        if (data.vType && (data.vType.toLowerCase() === 'mart' || data.vType === 'Mart')) {
                            console.log('📋 Edit page - Mart Vendor:', data.title, 'ID:', data.id, 'vType:', data.vType);
                            restaurant_list.push(data);
                            if (data.id == product.vendorID) {
                                $('#food_restaurant').append($("<option selected></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            } else {
                                $('#food_restaurant').append($("<option></option>")
                                    .attr("value", data.id)
                                    .text(data.title));
                            }
                        }
                    });
                }).catch(function(error) {
                    console.error('❌ Edit page - Error fetching vendors:', error);
                });
                await database.collection('mart_categories').where('publish', '==', true).get().then(async function (snapshots) {
                    snapshots.docs.forEach((listval) => {
                        var data = listval.data();
                        categories_list.push(data);
                        if (data.id == product.categoryID) {
                            $('#food_category').append($("<option selected></option>")
                                .attr("value", data.id)
                                .text(data.title));
                        } else {
                            $('#food_category').append($("<option></option>")
                                .attr("value", data.id)
                                .text(data.title));
                        }
                        updateSelectedFoodCategoryTags();
                    })
                });
                
                // Load subcategories
                await database.collection('mart_subcategories').where('publish', '==', true).get().then(async function (snapshots) {
                    snapshots.docs.forEach((listval) => {
                        var data = listval.data();
                        if (data.id == product.subcategoryID) {
                            $('#food_subcategory').append($("<option selected></option>")
                                .attr("value", data.id)
                                .attr("data-parent", data.parent_category_id)
                                .text(data.title));
                        } else {
                            $('#food_subcategory').append($("<option></option>")
                                .attr("value", data.id)
                                .attr("data-parent", data.parent_category_id)
                                .text(data.title));
                        }
                        updateSelectedSubcategoryTags();
                    })
                });
                var selected_attributes = [];
                if (product.item_attribute != null) {
                    $("#attributes_div").show();
                    $.each(product.item_attribute.attributes, function (index, attribute) {
                        selected_attributes.push(attribute.attribute_id);
                    });
                    $('#attributes').val(JSON.stringify(product.item_attribute.attributes));
                    $('#variants').val(JSON.stringify(product.item_attribute.variants));
                }
                var attributes = database.collection('vendor_attributes');
                attributes.get().then(async function (snapshots) {
                    snapshots.docs.forEach((listval) => {
                        var data = listval.data();
                        attributes_list.push(data);
                        var selected = '';
                        if ($.inArray(data.id, selected_attributes) !== -1) {
                            var selected = 'selected="selected"';
                        }
                        var option = '<option value="' + data.id + '" ' + selected + '>' + data.title + '</option>';
                        $('#item_attribute').append(option);
                    });
                    $("#item_attribute").show().chosen({"placeholder_text": "{{trans('lang.select_attribute')}}"});
                    if (product.item_attribute) {
                        $("#item_attribute").attr("onChange", "selectAttribute('" + btoa(JSON.stringify(product.item_attribute)) + "')");
                        selectAttribute(btoa(JSON.stringify(product.item_attribute)));
                    } else {
                        $("#item_attribute").attr("onChange", "selectAttribute()");
                        selectAttribute();
                    }
                });
                if (product.hasOwnProperty('product_specification')) {
                    product_specification = product.product_specification;
                    if (product_specification != null && product_specification != "") {
                        product_specification = {};
                        $.each(product.product_specification, function (key, value) {
                            product_specification[key] = value;
                        });
                    }
                    for (var key in product.product_specification) {
                        $('#product_specification_heading').show();
                        $(".product_specification").append('<div class="row" style="margin-top:5px;" id="add_product_specification_iteam_' + key + '">' +
                            '<div class="col-5"><input class="form-control" type="text" value="' + key + '" disabled ></div>' +
                            '<div class="col-5"><input class="form-control" type="text" value="' + product.product_specification[key] + '" disabled ></div>' +
                            '<div class="col-2"><button class="btn" type="button" onclick=deleteProductSpecificationSingle("' + key + '")><span class="mdi mdi-delete"></span></button></div></div>');
                    }
                }
                if (product.hasOwnProperty('photo')) {
                    photo = product.photo;
                    if (product.photos != undefined && product.photos != '') {
                        photos = product.photos;
                    } else {
                        if (photo != '' && photo != null) {
                            photos.push(photo);
                        }
                    }
                    if (photos != '' && photos != null) {  
                        photos.forEach((element, index) => {
                            $(".product_image").append('<span class="image-item" id="photo_' + index + '"><span class="remove-btn" data-id="' + index + '" data-img="' + photos[index] + '" data-status="old"><i class="fa fa-remove"></i></span><img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" width="50px" id="" height="auto" src="' + photos[index] + '"></span>');
                        })
                    } else if (photo != '' && photo != null) {
                        $(".product_image").append('<span class="image-item" id="photo_1"><img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" class="rounded" width="50px" id="" height="auto" src="' + photo + '"></span>');
                    } else {
                        $(".product_image").append('<span class="image-item" id="photo_1"><img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
                    }
                }
                $(".food_name").val(product.name);
                $(".food_price").val(product.price);
                $(".item_quantity").val(product.quantity);
                $(".food_discount").val(product.disPrice);
                if (product.hasOwnProperty("calories")) {
                    $(".food_calories").val(product.calories)
                }
                if (product.hasOwnProperty("grams")) {
                    $(".food_grams").val(product.grams);
                }
                if (product.hasOwnProperty("proteins")) {
                    $(".food_proteins").val(product.proteins)
                }
                if (product.hasOwnProperty("fats")) {
                    $(".food_fats").val(product.fats);
                }
                $("#food_description").val(product.description);
                if (product.publish) {
                    $(".food_publish").prop('checked', true);
                }
                if (product.nonveg) {
                    $(".food_nonveg").prop('checked', true);
                }
                if (product.takeawayOption) {
                    $(".food_take_away_option").prop('checked', true);
                }
                if (product.hasOwnProperty('addOnsTitle')) {
                    product.addOnsTitle.forEach((element, index) => {
                        $(".add_ons_list").append('<div class="row" style="margin-top:5px;" id="add_ones_list_iteam_' + index + '"><div class="col-5"><input class="form-control" type="text" value="' + element + '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' + product.addOnsPrice[index] + '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick="deleteAddOnesSingle(' + index + ')"><span class="mdi mdi-delete"></span></button></div></div>');
                    })
                    addOnesTitle = product.addOnsTitle;
                    addOnesPrice = product.addOnsPrice;
                }
                if (product.hasOwnProperty('isAvailable') && product.isAvailable) {
                    $(".food_is_available").prop('checked', true);
                }
                
                // Load existing options if any - moved here after product data is loaded
                console.log('🔍 Checking for options in product:', product);
                console.log('🔍 Product has_options:', product?.has_options);
                console.log('🔍 Product options:', product?.options);
                console.log('🔍 Options length:', product?.options?.length);
                
                if (product && product.has_options && product.options && product.options.length > 0) {
                    console.log('✅ Loading existing options...');
                    $('.has_options').prop('checked', true);
                    $('#options_config').show();
                    $('#options_fieldset').show();
                    console.log('🔧 Options fieldset shown:', $('#options_fieldset').is(':visible'));
                    console.log('🔧 Options config shown:', $('#options_config').is(':visible'));
                    
                    // Load existing options
                    product.options.forEach((option, index) => {
                        console.log('📋 Loading option:', option);
                        loadExistingOption(option, index + 1);
                    });
                    
                    updateOptionsSummary();
                    updateDefaultOptionSelect();
                } else {
                    console.log('❌ No options found or conditions not met');
                }
                
                jQuery("#data-table_processing").hide();
            })
            $(".edit-form-btn").click( async function () {
                var name = $(".food_name").val();
                var price = $(".food_price").val();
                var quantity = $(".item_quantity").val();
                var restaurant = $("#food_restaurant option:selected").val();
                var category = $("#food_category option:selected").val();
                var foodCalories = parseInt($(".food_calories").val());
                var foodGrams = parseInt($(".food_grams").val());
                var foodProteins = parseInt($(".food_proteins").val());
                var foodFats = parseInt($(".food_fats").val());
                var description = $("#food_description").val();
                var foodPublish = $(".food_publish").is(":checked");
                var nonveg = $(".food_nonveg").is(":checked");
                var veg = !nonveg;
                var foodTakeaway = $(".food_take_away_option").is(":checked");
                var discount = $(".food_discount").val();
                if (discount == '') {
                    discount = "0";
                }
                if (!foodCalories) {
                    foodCalories = 0;
                }
                if (!foodGrams) {
                    foodGrams = 0;
                }
                if (!foodFats) {
                    foodFats = 0;
                }
                if (!foodProteins) {
                    foodProteins = 0;
                }
                if (photos.length > 0) {
                    photo = photos[0];
                } else {
                    photo = '';
                }
                if (name == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.enter_food_name_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (price == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.enter_food_price_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (restaurant == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>Please select a mart</p>");
                    window.scrollTo(0, 0);
                } else if (category == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>Please select a mart category</p>");
                    window.scrollTo(0, 0);
                } else if (parseInt(price) < parseInt(discount)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.price_should_not_less_then_discount_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (quantity == '' || quantity < -1) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    if (quantity == '') {
                        $(".error_top").append("<p>{{trans('lang.enter_item_quantity_error')}}</p>");
                    } else {
                        $(".error_top").append("<p>{{trans('lang.invalid_item_quantity_error')}}</p>");
                    }
                    window.scrollTo(0, 0);
                } else if (description == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.enter_food_description_error')}}</p>");
                    window.scrollTo(0, 0);
                } else {
                    $(".error_top").hide();
                    var item_attribute = null;
                    var quantityerror = 0;
                    var priceerror = 0;
                    var attributes = [];
                    var variants = [];
                    if ($("#item_attribute").val().length > 0) {
                        if ($('#attributes').val().length > 0) {
                            var attributes = $.parseJSON($('#attributes').val());
                        }else{
                            alert('Please add your attribute value');
                            return false;
                        }
                        if($("#item_attribute").val().length !== attributes.length){
                            alert('Please add your attribute value');
                            return false;
                        }
                        console.log($("#item_attribute").val());
                        console.log($('#attributes').val());
                    }
                  
                    if ($('#variants').val().length > 0) {
                        var variantsSet = $.parseJSON($('#variants').val());
                        await storeVariantImageData().then(async (vIMG) => {
                            $.each(variantsSet, function (key, variant) {
                                var variant_id = uniqid();
                                var variant_sku = variant;
                                var variant_price = $('[id="price_' + variant + '"]').val();
                                var variant_quantity = $('[id="qty_' + variant + '"]').val();
                                var variant_image = $('[id="variant_' + variant + '_url"]').val();
                                if (variant_image) {
                                    variants.push({
                                        'variant_id': variant_id,
                                        'variant_sku': variant_sku,
                                        'variant_price': variant_price,
                                        'variant_quantity': variant_quantity,
                                        'variant_image': variant_image
                                    });
                                } else {
                                    variants.push({
                                        'variant_id': variant_id,
                                        'variant_sku': variant_sku,
                                        'variant_price': variant_price,
                                        'variant_quantity': variant_quantity
                                    });
                                }
                                if (variant_quantity = '' || variant_quantity < -1 || variant_quantity == 0) {
                                    quantityerror++;
                                }
                                if (variant_price == "" || variant_price <= 0) {
                                    priceerror++;
                                }
                            });
                        }).catch(err => {
                            jQuery("#data-table_processing").hide();
                            $(".error_top").show();
                            $(".error_top").html("");
                            $(".error_top").append("<p>" + err + "</p>");
                            window.scrollTo(0, 0);
                        });
                    }
                    if (attributes.length > 0 && variants.length > 0) {
                        if (quantityerror > 0) {
                            alert('Please add your variants quantity it should be -1 or greater than -1');
                            return false;
                        }
                        if (priceerror > 0) {
                            alert('Please add your variants  Price');
                            return false;
                        }
                        var item_attribute = {'attributes': attributes, 'variants': variants};
                    }
                   
                    if ($.isEmptyObject(product_specification)) {
                        product_specification = null;
                    }
                    jQuery("#data-table_processing").show();
                await storeImageData().then(async (IMG) => { 
                    if (IMG.length > 0) {
                        photo = IMG[0];
                    }
                    var foodIsAvailable = $(".food_is_available").is(":checked");
                    const hasOptions = $(".has_options").is(":checked");
                    let updateData = {
                        'name': name || '',
                        'price': price.toString() || '0',
                        'quantity': parseInt(quantity) || 0,
                        'disPrice': discount || '0',
                        'vendorID': restaurant || '',
                        'categoryID': category || '',
                        'subcategoryID': $("#food_subcategory").val() || '', // Add subcategory
                        'photo': photo || '',
                        'calories': foodCalories || 0,
                        "grams": foodGrams || 0,
                        'proteins': foodProteins || 0,
                        'fats': foodFats || 0,
                        'description': description || '',
                        'publish': foodPublish,
                        'nonveg': nonveg,
                        'veg': veg,
                        'addOnsTitle': addOnesTitle || [],
                        'addOnsPrice': addOnesPrice || [],
                        'takeawayOption': foodTakeaway,
                        'product_specification': product_specification || {},
                        'item_attribute': item_attribute || null,
                        'photos': IMG || [],
                        'isAvailable': foodIsAvailable,
                        'updated_at': firebase.firestore.FieldValue.serverTimestamp()
                    };
                    
                    // Handle options
                    if (hasOptions && optionsList.length > 0) {
                        // Validate options
                        for (let option of optionsList) {
                            if (!option.title || !option.price || option.price <= 0) {
                                alert('Please fill all required fields for all options.');
                                return;
                            }
                        }
                        
                        // Prepare options data
                        const optionsData = optionsList.map((option, index) => ({
                            id: option.id || `option_${Date.now()}_${index}`,
                            option_type: option.type || 'size',
                            option_title: option.title || '',
                            option_subtitle: option.subtitle || '',
                            price: parseFloat(option.price) || 0,
                            original_price: parseFloat(option.original_price) || parseFloat(option.price) || 0,
                            discount_amount: parseFloat(option.discount_amount) || 0,
                            unit_price: parseFloat(option.unit_price) || 0,
                            unit_measure: parseFloat(option.unit_measure) || 100,
                            unit_measure_type: option.unit_measure_type || 'g',
                            quantity: parseFloat(option.quantity) || 0,
                            quantity_unit: option.quantity_unit || 'g',
                            image: option.image || '',
                            is_available: option.is_available !== false,
                            is_featured: option.is_featured === true,
                            sort_order: index + 1,
                            updated_at: new Date().toISOString()
                        }));
                        
                        // Calculate price range
                        const prices = optionsList.map(opt => opt.price);
                        const minPrice = Math.min(...prices);
                        const maxPrice = Math.max(...prices);
                        const defaultOptionId = optionsList.find(opt => opt.is_featured)?.id || optionsList[0]?.id;
                        
                        updateData = {
                            ...updateData,
                            has_options: true,
                            options_enabled: true,
                            options_count: optionsList.length || 0,
                            min_price: minPrice || 0,
                            max_price: maxPrice || 0,
                            price_range: `₹${minPrice || 0} - ₹${maxPrice || 0}`,
                            default_option_id: defaultOptionId || '',
                            options: optionsData
                        };
                    } else {
                        updateData = {
                            ...updateData,
                            has_options: false,
                            options_enabled: false,
                            options_count: 0,
                            options: []
                        };
                    }
                    
                    database.collection('mart_items').doc(id).update(updateData).then(async function (result) {
                        console.log('✅ Mart item updated successfully, now logging activity...');
                        try {
                            if (typeof logActivity === 'function') {
                                console.log('🔍 Calling logActivity for mart item update...');
                                await logActivity('mart_items', 'updated', 'Updated mart item: ' + name);
                                console.log('✅ Activity logging completed successfully');
                            } else {
                                console.error('❌ logActivity function is not available');
                            }
                        } catch (error) {
                            console.error('❌ Error calling logActivity:', error);
                        }
                        <?php if(isset($_GET['eid']) && $_GET['eid'] != ''){?>
                            window.location.href = "{{ route('marts.mart-items',$_GET['eid']) }}";
                        <?php }else{ ?>
                        jQuery("#data-table_processing").hide();
                            window.location.href = '{{ route("mart-items")}}';
                        <?php } ?>
                    });
                }).catch(err => {
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>" + err + "</p>");
                    window.scrollTo(0, 0);
                });
                }
            })
        })
        function handleFileSelect(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            new Compressor(f, {
                quality: <?php echo env('IMAGE_COMPRESSOR_QUALITY', 0.8); ?>,
                success(result) {
                    f = result;
                    reader.onload = (function (theFile) {
                        return function (e) {
                            var filePayload = e.target.result;
                            var val = f.name;
                            var ext = val.split('.')[1];
                            var docName = val.split('fakepath')[1];
                            var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                            var timestamp = Number(new Date());
                            var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                            var uploadTask = storageRef.child(filename).put(theFile);
                            uploadTask.on('state_changed', function (snapshot) {
                                var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                                console.log('Upload is ' + progress + '% done');
                                jQuery("#uploding_image").text("Image is uploading...");
                            }, function (error) {
                            }, function () {
                                uploadTask.snapshot.ref.getDownloadURL().then(function (downloadURL) {
                                    jQuery("#uploding_image").text("Upload is completed");
                                    photo = downloadURL;
                                    $(".item_image").empty()
                                    $(".item_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image">');
                                });
                            });
                        };
                    })(f);
                    reader.readAsDataURL(f);
                },
                error(err) {
                    console.log(err.message);
                },
            });
        }
        function addOneFunction() {
            $("#add_ones_div").show();
            $(".save_add_one_btn").show();
        }
        function saveAddOneFunction() {
            var optiontitle = $(".add_ons_title").val();
            var optionPricevalue = $(".add_ons_price").val();
            var optionPrice = $(".add_ons_price").val();
            $(".add_ons_price").val('');
            $(".add_ons_title").val('');
            if (optiontitle != '' && optionPricevalue != '') {
                addOnesPrice.push(optionPrice.toString());
                addOnesTitle.push(optiontitle);
                var index = addOnesTitle.length - 1;
                $(".add_ons_list").append('<div class="row" style="margin-top:5px;" id="add_ones_list_iteam_' + index + '"><div class="col-5"><input class="form-control" type="text" value="' + optiontitle + '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' + optionPrice + '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick="deleteAddOnesSingle(' + index + ')"><span class="mdi mdi-delete"></span></button></div></div>');
            } else {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_title_and_price_error')}}</p>");
                window.scrollTo(0, 0);
            }
        }
        function deleteAddOnesSingle(index) {
            addOnesTitle.splice(index, 1);
            addOnesPrice.splice(index, 1);
            $("#add_ones_list_iteam_" + index).hide();
        }
        function handleFileSelectProduct(evt) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    var filePayload = e.target.result;
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var timestamp = Number(new Date());
                    var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                    var uploadTask = storageRef.child(filename).put(theFile);
                    uploadTask.on('state_changed', function (snapshot) {
                        var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                        console.log('Upload is ' + progress + '% done');
                        $('.product_image').find(".uploding_image_photos").text("Image is uploading...");
                    }, function (error) {
                    }, function () {
                        uploadTask.snapshot.ref.getDownloadURL().then(function (downloadURL) {
                            jQuery("#uploding_image").text("Upload is completed");
                            if (downloadURL) {
                                productImagesCount++;
                                photos_html = '<span class="image-item" id="photo_' + productImagesCount + '"><span class="remove-btn" data-id="' + productImagesCount + '" data-img="' + downloadURL + '"><i class="fa fa-remove"></i></span><img class="rounded" width="50px" id="" height="auto" src="' + downloadURL + '"></span>'
                                $(".product_image").append(photos_html);
                                photos.push(downloadURL);
                            }
                        });
                    });
                };
            })(f);
            reader.readAsDataURL(f);
        }
        async function storeImageData() {
            var newPhoto = [];
            if (photos.length > 0) {
                newPhoto = photos; 
            }
            if (new_added_photos.length > 0) {
                await Promise.all(new_added_photos.map(async (foodPhoto, index) => {
                    foodPhoto = foodPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(new_added_photos_filename[index]).putString(foodPhoto, 'base64', { contentType: 'image/jpg' });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto.push(downloadURL);
                }));
            }
            if (photosToDelete.length > 0) {
                await Promise.all(photosToDelete.map(async (delImage) => {
                    imageBucket = delImage.bucket;
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";
                    if (imageBucket == envBucket) {
                        await delImage.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===", error);
                        });
                    } else {
                        console.log('Bucket not matched');
                    }
                }));
            }
            return newPhoto;
        }
        $("#product_image").resizeImg({
            callback: function (base64str) {
                var val = $('#product_image').val().toLowerCase();
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = $('#product_image').val().replace(/C:\\fakepath\\/i, '')
                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                productImagesCount++;
                photos_html = '<span class="image-item" id="photo_' + productImagesCount + '"><span class="remove-btn" data-id="' + productImagesCount + '" data-img="' + base64str + '" data-status="new"><i class="fa fa-remove"></i></span><img class="rounded" width="50px" id="" height="auto" src="' + base64str + '"></span>'
                $(".product_image").append(photos_html);
                new_added_photos.push(base64str);
                new_added_photos_filename.push(filename);
                $("#product_image").val('');
            }
        });
        $(document).on("click", ".remove-btn", function () {
            var id = $(this).attr('data-id');
            var photo_remove = $(this).attr('data-img');
            var status=$(this).attr('data-status');
            if(status=="old"){
                 photosToDelete.push(firebase.storage().refFromURL(photo_remove));
            }
            $("#photo_" + id).remove();
            index = photos.indexOf(photo_remove);
            if (index > -1) {
                photos.splice(index, 1); // 2nd parameter means remove one item only
            }
            index = new_added_photos.indexOf(photo_remove);
            if (index > -1) {
                new_added_photos.splice(index, 1); // 2nd parameter means remove one item only
                new_added_photos_filename.splice(index, 1);
            }
        });
        $("#food_restaurant").change(function () {
            $("#attributes_div").show();
            $("#item_attribute_chosen").css({'width': '100%'});
            var selected_vendor = this.value;
        });
        function change_categories(selected_vendor) {
            restaurant_list.forEach((vendor) => {
                if (vendor.id == selected_vendor) {
                    $('#item_category').html('');
                    $('#item_category').append($('<option value="">{{trans("lang.select_category")}}</option>'));
                    categories_list.forEach((data) => {
                        if (vendor.categoryID == data.id) {
                            $('#food_category').html($("<option></option>")
                                .attr("value", data.id)
                                .text(data.title));
                        }
                    })
                }
            });
        }
        function handleVariantFileSelect(evt, vid) {
            var f = evt.target.files[0];
            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    var filePayload = e.target.result;
                    var val = f.name;
                    var ext = val.split('.')[1];
                    var docName = val.split('fakepath')[1];
                    var timestamp = Number(new Date());
                    var filename = (f.name).replace(/C:\\fakepath\\/i, '')
                    var filename = 'variant_' + vid + '_' + timestamp + '.' + ext;
                    variant_filename.push(filename);
                    variant_photos.push(filePayload);
                    variant_vIds.push(vid);
                    $('[id="variant_'+ vid+'_image"]').empty();
                    $('[id="variant_'+ vid+'_image"]').html('<img class="rounded" style="width:50px" src="' + filePayload + '" alt="image"><i class="mdi mdi-delete" data-variant="' + vid + '" data-img="' +filePayload + '" data-file="'+filename +'" data-status="new"></i>');
                    $('#upload_'+vid).attr('data-img',filePayload);
                    $('#upload_'+vid).attr('data-file',filename);
                };
            })(f);
            reader.readAsDataURL(f);
        }
        async function storeVariantImageData() {
            var newPhoto = [];
            if (variant_photos.length > 0) {
                await Promise.all(variant_photos.map(async (variantPhoto, index) => {
                    variantPhoto = variantPhoto.replace(/^data:image\/[a-z]+;base64,/, "");
                    var uploadTask = await storageRef.child(variant_filename[index]).putString(variantPhoto, 'base64', {contentType: 'image/jpg'});
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    $('[id="variant_'+ variant_vIds[index]+'_url"]').val(downloadURL);
                    newPhoto.push(downloadURL);
                }));
            }
            if (variantImageToDelete.length > 0) {
            }
            return newPhoto;
        }
    function selectAttribute(item_attribute = '') {
        if (item_attribute) {
            var item_attribute = $.parseJSON(atob(item_attribute));
        }
        var html = '';
        $("#item_attribute").find('option:selected').each(function () {
            var $this = $(this);
            var selected_options = [];
            if (item_attribute) {
                $.each(item_attribute.attributes, function (index, attribute) {
                    if ($this.val() == attribute.attribute_id) {
                        selected_options.push(attribute.attribute_options);
                    }
                });
            }
            html += '<div class="row" id="attr_' + $this.val() + '">';
            html += '<div class="col-md-3">';
            html += '<label>' + $this.text() + '</label>';
            html += '</div>';
            html += '<div class="col-lg-9">';
            html += '<input type="text" class="form-control" id="attribute_options_' + $this.val() + '" value="' + selected_options + '" placeholder="Add attribute values" data-role="tagsinput" onchange="variants_update(\'' + btoa(JSON.stringify(item_attribute)) + '\')">';
            html += '</div>';
            html += '</div>';
        });
        $("#item_attributes").html(html);
        $("#item_attributes input[data-role=tagsinput]").tagsinput();
        if ($("#item_attribute").val().length == 0) {
            $("#attributes").val('');
            $("#variants").val('');
            $("#item_variants").html('');
        }
    }
    function variants_update(item_attributeX = '') {
        if (item_attributeX) {
            var item_attributeX = $.parseJSON(atob(item_attributeX));
        }
        var html = '';
        var item_attribute = $("#item_attribute").map(function (idx, ele) {
                return $(ele).val();
            }).get();
        if (item_attribute.length > 0) {
            var attributes = [];
            var attributeSet = [];
            $.each(item_attribute, function (index, attribute) {
                var attribute_options = $("#attribute_options_" + attribute).val();
                if (attribute_options) {
                    var attribute_options = attribute_options.split(',');
                    attribute_options = $.map(attribute_options, function (value) {
                        return value.replace(/[^0-9a-zA-Z a]/g, '');
                    });
                    attributeSet.push(attribute_options);
                    attributes.push({'attribute_id': attribute, 'attribute_options': attribute_options});
                }
            });
            $('#attributes').val(JSON.stringify(attributes));
            var variants = getCombinations(attributeSet);
            $('#variants').val(JSON.stringify(variants));
              
            if (attributeSet.length > 0) {  
                html += '<table class="table table-bordered">';
                html += '<thead class="thead-light">';
                html += '<tr>';
                html += '<th class="text-center"><span class="control-label">Variant</span></th>';
                html += '<th class="text-center"><span class="control-label">Variant Price</span></th>';
                html += '<th class="text-center"><span class="control-label">Variant Quantity</span></th>';
                html += '<th class="text-center"><span class="control-label">Variant Image</span></th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';
                $.each(variants, function (index, variant) {
                    var variant_price = 1;
                    var variant_qty = -1;
                    var variant_image = variant_image_url = '';
                    if (item_attributeX) {
                        var variant_info = $.map(item_attributeX.variants, function (v, i) {
                            if (v.variant_sku == variant) {
                                return v;
                            }
                        });
                        if (variant_info[0]) {
                            variant_price = variant_info[0].variant_price;
                            variant_qty = variant_info[0].variant_quantity;
                            if (variant_info[0].variant_image) {
                                variant_image = '<img class="rounded" style="width:50px" src="' + variant_info[0].variant_image + '" alt="image"><i class="mdi mdi-delete" data-variant="' + variant + '" data-status="old"></i>';
                                variant_image_url = variant_info[0].variant_image;
                            }
                        }
                    }
                    html += '<tr>';
                    html += '<td><label for="" class="control-label">' + variant + '</label></td>';
                    html += '<td>';
                    html += '<input type="number" id="price_' + variant + '" value="' + variant_price + '" min="0" class="form-control">';
                    html += '</td>';
                    html += '<td>';
                    html += '<input type="number" id="qty_' + variant + '" value="' + variant_qty + '" min="-1" class="form-control">';
                    html += '</td>';
                    html += '<td>';
                    html += '<div class="variant-image">';
                    html += '<div class="upload">';
                    html += '<div class="image" id="variant_' + variant + '_image">' + variant_image + '</div>';
                    html += '<div class="icon"><i class="mdi mdi-cloud-upload" data-variant="' + variant + '" id="upload_'+variant+'"></i></div>';
                    html += '</div>';
                    html += '<div id="variant_' + variant + '_process"></div>';
                    html += '<div class="input-file">';
                    html += '<input type="file" id="file_' + variant + '" onChange="handleVariantFileSelect(event,\'' + variant + '\')" class="form-control" style="display:none;">';
                    html += '<input type="hidden" id="variant_' + variant + '_url" value="' + variant_image_url + '">';
                    html += '</div>';
                    html += '</div>';
                    html += '</td>';
                    html += '</tr>';
                });
                html += '</tbody>';
                html += '</table>';
            }
        }
        $("#item_variants").html(html);
    }
    function getCombinations(arr) {
        if (arr.length) {
            if (arr.length == 1) {
                return arr[0];
            } else {
                var result = [];
                var allCasesOfRest = getCombinations(arr.slice(1));
                for (var i = 0; i < allCasesOfRest.length; i++) {
                    for (var j = 0; j < arr[0].length; j++) {
                        result.push(arr[0][j] + '-' + allCasesOfRest[i]);
                    }
                }
                return result;
            }
        }
    }
    function uniqid(prefix = "", random = false) {
        const sec = Date.now() * 1000 + Math.random() * 1000;
        const id = sec.toString(16).replace(/\./g, "").padEnd(14, "0");
        return `${prefix}${id}${random ? `.${Math.trunc(Math.random() * 100000000)}` : ""}`;
    }
    function addProductSpecificationFunction() {
        $("#add_product_specification_div").show();
        $(".save_product_specification_btn").show();
    }
    function saveProductSpecificationFunction() {
        var optionlabel = $(".add_label").val();
        var optionvalue = $(".add_value").val();
        $(".add_label").val('');
        $(".add_value").val('');
        if (optionlabel != '' && optionvalue != '') {
            if (product_specification == null) {
                product_specification = {};
            }
            product_specification[optionlabel] = optionvalue;
            $(".product_specification").append('<div class="row add_product_specification_iteam_' + optionlabel + '" style="margin-top:5px;" id="add_product_specification_iteam_' + optionlabel + '"><div class="col-5"><input class="form-control" type="text" value="' + optionlabel + '" disabled ></div><div class="col-5"><input class="form-control" type="text" value="' + optionvalue + '" disabled ></div><div class="col-2"><button class="btn" type="button" onclick=deleteProductSpecificationSingle("' + optionlabel + '")><span class="mdi mdi-delete"></span></button></div></div>');
        } else {
            alert("Please enter Label and Value");
        }
    }
    function deleteProductSpecificationSingle(index) {
        delete product_specification[index];
        $(".add_product_specification_iteam_" + index).addClass('hide');
        delete product_specification[index];
        $("#add_product_specification_iteam_" + index).hide();
    }
    // $(function() {
    //     // Insert search input and tag container if not present
    //     if ($('#food_category_search').length === 0) {
    //         $('#food_category').before('<div id="selected_food_categories" class="mb-2"></div><input type="text" id="food_category_search" class="form-control mb-2" placeholder="Search categories...">');
    //     }
    $(document).ready(function() {
        // 1. Filter dropdown options based on search
        $('#food_category_search').on('keyup', function() {
            var search = $(this).val().toLowerCase();
            $('#food_category option').each(function() {
                if ($(this).val() === "") {
                    $(this).show();
                    return;
                }
                var text = $(this).text().toLowerCase();
                if (text.indexOf(search) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        // 2. When selecting from dropdown, add tag (multi-select support)
        $('#food_category').on('change', function() {
            updateSelectedFoodCategoryTags();
        });

        // 3. Remove tag and unselect in dropdown
        $('#selected_food_categories').on('click', '.remove-tag', function() {
            var value = $(this).parent().data('value');
            $('#food_category option[value="' + value + '"]').prop('selected', false);
            updateSelectedFoodCategoryTags();
        });
    });
    
        // 4. Update tags display
        function updateSelectedFoodCategoryTags() {
        var selected = $('#food_category').val() || [];
        var html = '';
        $('#food_category option:selected').each(function() {
            if ($(this).val() !== "") {
                html += '<span class="selected-category-tag" data-value="' + $(this).val() + '">' +
                    $(this).text() +
                    '<span class="remove-tag">&times;</span></span>';
            }
        });
        $('#selected_categories').html(html);
    }

// Subcategory search and multi-select tag functionality
$(document).ready(function() {
    // 1. Filter dropdown options based on search
    $('#food_subcategory_search').on('keyup', function() {
        var search = $(this).val().toLowerCase();
        $('#food_subcategory option').each(function() {
            if ($(this).val() === "") {
                $(this).show();
                return;
            }
            var text = $(this).text().toLowerCase();
            if (text.indexOf(search) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // 2. When selecting from dropdown, add tag (multi-select support)
    $('#food_subcategory').on('change', function() {
        updateSelectedSubcategoryTags();
    });

    // 3. Remove tag and unselect in dropdown
    $('#selected_subcategories').on('click', '.remove-tag', function() {
        var value = $(this).parent().data('value');
        $('#food_subcategory option[value="' + value + '"]').prop('selected', false);
        updateSelectedSubcategoryTags();
    });
});

function updateSelectedSubcategoryTags() {
    var selected = $('#food_subcategory').val() || [];
    var html = '';
    $('#food_subcategory option:selected').each(function() {
        if ($(this).val() !== "") {
            html += '<span class="selected-subcategory-tag" data-value="' + $(this).val() + '">' +
                $(this).text() +
                '<span class="remove-tag">&times;</span></span>';
        }
    });
    $('#selected_subcategories').html(html);
}

// Options Management Functions
let optionsList = [];
let currentOptionId = null;

// Options loading is now handled after Firebase data is loaded

// Toggle options section
$(document).ready(function() {
    $('.has_options').change(function() {
        if ($(this).is(':checked')) {
            $('#options_config').show();
            $('#options_fieldset').show();
        } else {
            $('#options_config').hide();
            $('#options_fieldset').hide();
            $('.options-list').empty();
            optionsList = [];
            updateOptionsSummary();
        }
    });
});

function loadExistingOption(optionData, optionNumber) {
    console.log('🔧 loadExistingOption called with:', optionData, 'optionNumber:', optionNumber);
    const optionId = optionData.id || 'option_' + Date.now();
    const template = $('#option_template .option-item').clone();
    console.log('🔧 Template found:', template.length > 0);
    
    template.attr('data-option-id', optionId);
    template.find('.option-number').text(optionNumber);
    
    // Populate fields with existing data
    template.find('.option-type').val(optionData.option_type || 'size');
    template.find('.option-title').val(optionData.option_title || '');
    template.find('.option-subtitle').val(optionData.option_subtitle || '');
    template.find('.option-price').val(optionData.price || 0);
    template.find('.option-original-price').val(optionData.original_price || optionData.price || 0);
    template.find('.option-quantity').val(optionData.quantity || optionData.weight || 0);
    template.find('.option-quantity-unit').val(optionData.quantity_unit || 'g');
    template.find('.option-unit-measure').val(optionData.unit_measure || 100);
    template.find('.option-unit-price-display').val(optionData.unit_price_display || '');
    template.find('.option-unit-price').val(optionData.unit_price || 0);
    template.find('.option-discount').val(optionData.discount_amount || 0);
    template.find('.option-available').prop('checked', optionData.is_available !== false);
    template.find('.option-featured').prop('checked', optionData.is_featured === true);
    
    // Load image if exists
    if (optionData.image) {
        template.find('.option-image-preview').html(
            `<img src="${optionData.image}" style="max-width: 100px; max-height: 100px; border-radius: 4px;">`
        );
    }
    
    $('.options-list').append(template);
    template.show();
    console.log('🔧 Option template appended and shown. Total options in DOM:', $('.option-item').length);
    
    // Add to options list
    optionsList.push({
        id: optionId,
        type: optionData.option_type || 'size',
        title: optionData.option_title || '',
        subtitle: optionData.option_subtitle || '',
        price: parseFloat(optionData.price) || 0,
        original_price: parseFloat(optionData.original_price) || parseFloat(optionData.price) || 0,
        quantity: parseFloat(optionData.quantity) || parseFloat(optionData.weight) || 0,
        quantity_unit: optionData.quantity_unit || 'g',
        unit_measure: parseFloat(optionData.unit_measure) || 100,
        unit_price: parseFloat(optionData.unit_price) || 0,
        unit_price_display: optionData.unit_price_display || '',
        discount_amount: parseFloat(optionData.discount_amount) || 0,
        image: optionData.image || '',
        is_available: optionData.is_available !== false,
        is_featured: optionData.is_featured === true
    });
    
    console.log('🔧 Option added to optionsList. Total options:', optionsList.length);
    attachOptionEventListeners(optionId);
}

function addNewOption() {
    const optionId = 'option_' + Date.now();
    const template = $('#option_template .option-item').clone();
    
    template.attr('data-option-id', optionId);
    template.find('.option-number').text(optionsList.length + 1);
    
    $('.options-list').append(template);
    template.show();
    
    optionsList.push({
        id: optionId,
        type: 'size',
        title: '',
        subtitle: '',
        price: 0,
        original_price: 0,
        quantity: 0,
        quantity_unit: 'g',
        unit_measure: 100,
        unit_measure_type: 'g',
        unit_price: 0,
        unit_price_display: '',
        discount_amount: 0,
        image: '',
        is_available: true,
        is_featured: false
    });
    
    attachOptionEventListeners(optionId);
    updateOptionsSummary();
    updateDefaultOptionSelect();
}

function removeOption(button) {
    const optionItem = $(button).closest('.option-item');
    const optionId = optionItem.data('option-id');
    
    // Remove from array
    optionsList = optionsList.filter(opt => opt.id !== optionId);
    
    // Remove from DOM
    optionItem.remove();
    
    updateOptionNumbers();
    updateOptionsSummary();
    updateDefaultOptionSelect();
}

function attachOptionEventListeners(optionId) {
    const optionItem = $(`[data-option-id="${optionId}"]`);
    
    // Initialize checkbox states
    const optionData = optionsList.find(opt => opt.id === optionId);
    if (optionData) {
        optionItem.find('.option-available').prop('checked', optionData.is_available !== false);
        optionItem.find('.option-featured').prop('checked', optionData.is_featured === true);
        
        // Apply initial visual states
        if (optionData.is_available !== false) {
            optionItem.addClass('option-enabled');
        } else {
            optionItem.addClass('option-disabled');
        }
        
        if (optionData.is_featured === true) {
            optionItem.addClass('option-featured-highlight');
        }
    }
    
    // Update optionsList when form fields change
    optionItem.find('.option-type').on('change', function() {
        updateOptionInList(optionId, 'type', $(this).val());
    });
    
    optionItem.find('.option-title').on('input', function() {
        updateOptionInList(optionId, 'title', $(this).val());
        updateOptionsSummary();
        updateDefaultOptionSelect();
    });
    
    optionItem.find('.option-subtitle').on('input', function() {
        updateOptionInList(optionId, 'subtitle', $(this).val());
    });
    
    optionItem.find('.option-price').on('input', function() {
        const price = parseFloat($(this).val()) || 0;
        updateOptionInList(optionId, 'price', price);
        calculateOptionCalculations(optionId);
        updateOptionsSummary();
    });
    
    optionItem.find('.option-original-price').on('input', function() {
        const originalPrice = parseFloat($(this).val()) || 0;
        updateOptionInList(optionId, 'original_price', originalPrice);
        calculateOptionCalculations(optionId);
    });
    
    optionItem.find('.option-quantity').on('input', function() {
        const quantity = parseFloat($(this).val()) || 0;
        updateOptionInList(optionId, 'quantity', quantity);
        calculateOptionCalculations(optionId);
    });
    
    optionItem.find('.option-quantity-unit').on('change', function() {
        const unit = $(this).val();
        updateOptionInList(optionId, 'quantity_unit', unit);
        updateOptionInList(optionId, 'unit_measure_type', unit);
        calculateOptionCalculations(optionId);
    });
    
    optionItem.find('.option-unit-measure').on('input', function() {
        const unitMeasure = parseFloat($(this).val()) || 100;
        updateOptionInList(optionId, 'unit_measure', unitMeasure);
        calculateOptionCalculations(optionId);
    });
    
    optionItem.find('.option-available').on('change', function() {
        const isChecked = $(this).is(':checked');
        console.log('🔍 Option Available changed for', optionId, ':', isChecked);
        updateOptionInList(optionId, 'is_available', isChecked);
        
        // Visual feedback
        if (isChecked) {
            $(this).closest('.option-item').removeClass('option-disabled').addClass('option-enabled');
        } else {
            $(this).closest('.option-item').removeClass('option-enabled').addClass('option-disabled');
        }
    });
    
    optionItem.find('.option-featured').on('change', function() {
        const isFeatured = $(this).is(':checked');
        console.log('🔍 Option Featured changed for', optionId, ':', isFeatured);
        updateOptionInList(optionId, 'is_featured', isFeatured);
        
        if (isFeatured) {
            // Uncheck other featured options
            $('.option-featured').not(this).prop('checked', false);
            optionsList.forEach(opt => {
                if (opt.id !== optionId) {
                    opt.is_featured = false;
                }
            });
            
            // Visual feedback
            $('.option-item').removeClass('option-featured-highlight');
            $(this).closest('.option-item').addClass('option-featured-highlight');
        } else {
            $(this).closest('.option-item').removeClass('option-featured-highlight');
        }
        
        updateOptionsSummary();
        updateDefaultOptionSelect();
    });
    
    // Image upload
    optionItem.find('.option-image-input').change(function() {
        handleOptionImageUpload(this, optionId);
    });
}

function updateOptionInList(optionId, field, value) {
    const optionIndex = optionsList.findIndex(opt => opt.id === optionId);
    if (optionIndex !== -1) {
        optionsList[optionIndex][field] = value;
    }
}

// Smart Auto-generation Functions
function autoGenerateTitle(optionData) {
    const { quantity, quantity_unit, option_type, unit_measure } = optionData;
    
    switch(option_type) {
        case 'pack':
            return `Pack of ${quantity} (${unit_measure}${quantity_unit} each)`;
        case 'bundle':
            return `Bundle - ${quantity} ${quantity_unit}`;
        case 'size':
        case 'volume':
            return `${quantity}${quantity_unit} Pack`;
        case 'quantity':
            return `${quantity} ${quantity_unit}`;
        case 'addon':
            return `Add-on: ${quantity} ${quantity_unit}`;
        case 'variant':
            return `Variant: ${quantity} ${quantity_unit}`;
        default:
            return `${quantity} ${quantity_unit}`;
    }
}

function autoGenerateSubtitle(optionData) {
    const { quantity, quantity_unit, option_type, unit_measure } = optionData;
    
    switch(option_type) {
        case 'pack':
            const totalQuantity = quantity * unit_measure;
            return `${unit_measure}${quantity_unit} × ${quantity} = ${totalQuantity}${quantity_unit} total`;
        case 'bundle':
            return `Mixed items bundle - ${quantity} pieces`;
        case 'size':
        case 'volume':
            return `Single unit: ${quantity}${quantity_unit}`;
        case 'quantity':
            return `Individual pieces`;
        case 'addon':
            return `Extra item`;
        case 'variant':
            return `Special variant`;
        default:
            return `${quantity}${quantity_unit}`;
    }
}

// Smart Validation System
function validateAndEnhanceOption(optionData) {
    const errors = [];
    const warnings = [];
    const autoCorrections = {};
    
    // Price validation
    if (optionData.price > optionData.original_price) {
        errors.push('Price cannot be greater than Original Price');
    }
    
    // Quantity validation
    if (optionData.quantity < 0) {
        errors.push('Quantity cannot be negative');
        autoCorrections.quantity = 0;
    }
    
    // Auto-disable when quantity is 0
    if (optionData.quantity === 0) {
        autoCorrections.is_available = false;
        warnings.push('Option automatically disabled due to zero quantity');
    }
    
    // Low stock warning
    if (optionData.quantity > 0 && optionData.quantity < 10) {
        warnings.push('Low stock warning: Only ' + optionData.quantity + ' units remaining');
    }
    
    // Unit compatibility check
    const compatibleUnits = getCompatibleUnits(optionData.option_type);
    if (!compatibleUnits.includes(optionData.quantity_unit)) {
        warnings.push(`Unit "${optionData.quantity_unit}" may not be ideal for "${optionData.option_type}" type`);
    }
    
    return { errors, warnings, autoCorrections };
}

function getCompatibleUnits(optionType) {
    const unitMap = {
        'size': ['g', 'kg', 'mg'],
        'volume': ['L', 'ml', 'cl'],
        'quantity': ['pcs', 'units'],
        'pack': ['pcs', 'units', 'dozen'],
        'bundle': ['pcs', 'units', 'custom'],
        'addon': ['pcs', 'units', 'custom'],
        'variant': ['pcs', 'units', 'custom']
    };
    return unitMap[optionType] || ['pcs', 'units'];
}

// Smart Defaults System
function getSmartUnitMeasureBase(quantity_unit) {
    const smartDefaults = {
        'g': { base: 100, description: 'per 100g' },
        'kg': { base: 1, description: 'per kg' },
        'mg': { base: 1000, description: 'per 1000mg' },
        'L': { base: 1, description: 'per liter' },
        'ml': { base: 100, description: 'per 100ml' },
        'cl': { base: 10, description: 'per 10cl' },
        'pcs': { base: 1, description: 'per piece' },
        'units': { base: 1, description: 'per unit' },
        'dozen': { base: 12, description: 'per dozen' },
        'custom': { base: 100, description: 'per 100 units' }
    };
    
    return smartDefaults[quantity_unit] || { base: 100, description: 'per 100 units' };
}

function getOptionTypeDefaults(optionType) {
    const defaults = {
        'size': { 
            suggested_units: ['g', 'kg'], 
            default_unit: 'g',
            unit_measure: 100,
            description: 'Weight-based options'
        },
        'volume': { 
            suggested_units: ['L', 'ml'], 
            default_unit: 'ml',
            unit_measure: 100,
            description: 'Volume-based options'
        },
        'quantity': { 
            suggested_units: ['pcs', 'units'], 
            default_unit: 'pcs',
            unit_measure: 1,
            description: 'Count-based options'
        },
        'pack': { 
            suggested_units: ['pcs', 'dozen'], 
            default_unit: 'pcs',
            unit_measure: 1,
            description: 'Packaged options'
        },
        'bundle': { 
            suggested_units: ['pcs', 'custom'], 
            default_unit: 'pcs',
            unit_measure: 1,
            description: 'Mixed item bundles'
        },
        'addon': { 
            suggested_units: ['pcs', 'units'], 
            default_unit: 'pcs',
            unit_measure: 1,
            description: 'Additional items'
        },
        'variant': { 
            suggested_units: ['pcs', 'units'], 
            default_unit: 'pcs',
            unit_measure: 1,
            description: 'Product variants'
        }
    };
    
    return defaults[optionType] || defaults['quantity'];
}

// Enhanced Calculation Engine
function calculateOptionCalculations(optionId) {
    const optionItem = $(`[data-option-id="${optionId}"]`);
    const price = parseFloat(optionItem.find('.option-price').val()) || 0;
    const quantity = parseFloat(optionItem.find('.option-quantity').val()) || 0;
    const originalPrice = parseFloat(optionItem.find('.option-original-price').val()) || 0;
    const unitMeasure = parseFloat(optionItem.find('.option-unit-measure').val()) || 100;
    const quantityUnit = optionItem.find('.option-quantity-unit').val() || 'g';
    const optionType = optionItem.find('.option-type').val();
    
    // Enhanced unit price calculation
    let unitPrice = 0;
    let unitPriceDisplay = '';
    let savingsPercentage = 0;
    let savingsAmount = 0;
    
    if (quantity > 0) {
        switch(optionType) {
            case 'size':
            case 'volume':
                // For size/volume, calculate per unit first
                unitPrice = price / quantity;
                // Show per unit price with unit measure base for display only
                unitPriceDisplay = `₹${unitPrice.toFixed(2)}/${unitMeasure}${quantityUnit}`;
                break;
            case 'quantity':
            case 'pack':
            case 'bundle':
                unitPrice = price / quantity;
                const unitLabel = quantityUnit === 'pcs' ? 'piece' : quantityUnit;
                unitPriceDisplay = `₹${unitPrice.toFixed(2)}/${unitLabel}`;
                break;
            default:
                unitPrice = price / quantity;
                unitPriceDisplay = `₹${unitPrice.toFixed(2)}/${quantityUnit}`;
        }
        
        optionItem.find('.option-unit-price-display').val(unitPriceDisplay);
    }
    
    // Enhanced discount calculation
    if (originalPrice > 0 && originalPrice > price) {
        savingsAmount = originalPrice - price;
        savingsPercentage = ((savingsAmount / originalPrice) * 100);
        
        optionItem.find('.option-discount').val(savingsAmount.toFixed(2));
        
        // Add savings percentage display
        const savingsDisplay = `Save ₹${savingsAmount.toFixed(2)} (${savingsPercentage.toFixed(1)}%)`;
        if (optionItem.find('.option-savings-display').length) {
            optionItem.find('.option-savings-display').val(savingsDisplay);
        }
    }
    
    // Update in optionsList
    updateOptionInList(optionId, 'unit_price', unitPrice);
    updateOptionInList(optionId, 'discount_amount', savingsAmount);
    updateOptionInList(optionId, 'savings_percentage', savingsPercentage);
    
    // Auto-generate title and subtitle
    const optionData = {
        quantity: quantity,
        quantity_unit: quantityUnit,
        option_type: optionType,
        unit_measure: unitMeasure
    };
    
    const autoTitle = autoGenerateTitle(optionData);
    const autoSubtitle = autoGenerateSubtitle(optionData);
    
    // Only auto-fill if fields are empty
    if (!optionItem.find('.option-title').val()) {
        optionItem.find('.option-title').val(autoTitle);
        updateOptionInList(optionId, 'title', autoTitle);
    }
    
    if (!optionItem.find('.option-subtitle').val()) {
        optionItem.find('.option-subtitle').val(autoSubtitle);
        updateOptionInList(optionId, 'subtitle', autoSubtitle);
    }
    
    // Show validation feedback
    showValidationFeedback(optionId, optionData);
}

// Validation Feedback Display
function showValidationFeedback(optionId, optionData) {
    const optionItem = $(`[data-option-id="${optionId}"]`);
    const validation = validateAndEnhanceOption(optionData);
    
    // Clear previous feedback
    optionItem.find('.validation-feedback').remove();
    
    // Create feedback container
    let feedbackHtml = '<div class="validation-feedback mt-2">';
    
    // Show errors
    if (validation.errors.length > 0) {
        feedbackHtml += '<div class="alert alert-danger alert-sm">';
        feedbackHtml += '<i class="mdi mdi-alert-circle"></i> <strong>Errors:</strong><ul class="mb-0">';
        validation.errors.forEach(error => {
            feedbackHtml += `<li>${error}</li>`;
        });
        feedbackHtml += '</ul></div>';
    }
    
    // Show warnings
    if (validation.warnings.length > 0) {
        feedbackHtml += '<div class="alert alert-warning alert-sm">';
        feedbackHtml += '<i class="mdi mdi-alert"></i> <strong>Warnings:</strong><ul class="mb-0">';
        validation.warnings.forEach(warning => {
            feedbackHtml += `<li>${warning}</li>`;
        });
        feedbackHtml += '</ul></div>';
    }
    
    // Show smart suggestions
    const optionType = optionData.option_type;
    if (optionType) {
        const defaults = getOptionTypeDefaults(optionType);
        feedbackHtml += '<div class="alert alert-info alert-sm">';
        feedbackHtml += '<i class="mdi mdi-lightbulb-outline"></i> <strong>Smart Suggestions:</strong>';
        feedbackHtml += `<div>${defaults.description}</div>`;
        feedbackHtml += `<div>Suggested units: ${defaults.suggested_units.join(', ')}</div>`;
        feedbackHtml += '</div>';
    }
    
    feedbackHtml += '</div>';
    
    // Append feedback after the option item
    optionItem.append(feedbackHtml);
    
    // Apply auto-corrections
    Object.keys(validation.autoCorrections).forEach(field => {
        const value = validation.autoCorrections[field];
        if (field === 'is_available') {
            optionItem.find('.option-available').prop('checked', value);
        } else {
            optionItem.find(`.option-${field}`).val(value);
        }
    });
}

function handleOptionImageUpload(input, optionId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const optionItem = $(`[data-option-id="${optionId}"]`);
            optionItem.find('.option-image-preview').html(
                `<img src="${e.target.result}" style="max-width: 100px; max-height: 100px; border-radius: 4px;">`
            );
            
            // Store image data
            const optionIndex = optionsList.findIndex(opt => opt.id === optionId);
            if (optionIndex !== -1) {
                optionsList[optionIndex].image = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}

function updateOptionNumbers() {
    $('.option-item').each(function(index) {
        $(this).find('.option-number').text(index + 1);
    });
}

function updateOptionsSummary() {
    if (optionsList.length > 0) {
        const prices = optionsList.map(opt => opt.price).filter(p => p > 0);
        const minPrice = Math.min(...prices);
        const maxPrice = Math.max(...prices);
        
        $('.options-summary').show();
        $('.summary-content').html(`
            <div class="row">
                <div class="col-md-4">
                    <strong>Price Range:</strong> ₹${minPrice} - ₹${maxPrice}
                </div>
                <div class="col-md-4">
                    <strong>Total Options:</strong> ${optionsList.length}
                </div>
                <div class="col-md-4">
                    <strong>Featured Option:</strong> ${optionsList.find(opt => opt.is_featured)?.title || 'None'}
                </div>
            </div>
        `);
    } else {
        $('.options-summary').hide();
    }
}

function updateDefaultOptionSelect() {
    const select = $('#default_option');
    select.empty();
    select.append('<option value="">Select default option</option>');
    
    optionsList.forEach(option => {
        select.append(`<option value="${option.id}">${option.title}</option>`);
    });
}
</script>
@endsection
