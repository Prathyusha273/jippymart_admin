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
                <li class="breadcrumb-item"><a href="{{route('mart-categories')}}">Mart Categories</a></li>
                <li class="breadcrumb-item active">Sub-Categories</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section"> 
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/category.png') }}"></span>
                        <h3 class="mb-0">Mart Sub-Categories Table</h3>
                        <span class="counter ml-3 subcategory_count"></span>
                        <span class="parent-category-info ml-3"></span>
                    </div>
                    <div class="d-flex top-title-right align-self-center">
                        <div class="select-box pl-3">
                        </div>
                    </div>
                </div>
            </div>
        </div> 
      
       </div>
       @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row mb-4">
    <div class="col-12">
        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-0">
                <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">Bulk Import Mart Sub-Categories</h3>
                    <p class="mb-0 text-dark-2">Upload Excel file to import multiple mart sub-categories at once</p>
                </div>
                <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3">
                        <a href="{{ route('mart-subcategories.download-template') }}" class="btn btn-outline-primary rounded-full">
                            <i class="mdi mdi-download mr-2"></i>Download Template
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('mart-subcategories.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="importFile" class="control-label">Select Excel File (.xls/.xlsx)</label>
                                <input type="file" name="file" id="importFile" accept=".xls,.xlsx" class="form-control" required>
                                <div class="form-text text-muted">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    File should contain: title, description, photo, parent_category_id, subcategory_order, publish, show_in_homepage, review_attributes
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary rounded-full">
                                <i class="mdi mdi-upload mr-2"></i>Import Mart Sub-Categories
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
       <div class="table-list">
       <div class="row">
           <div class="col-12">
               <div class="card border">
                 <div class="card-header d-flex justify-content-between align-items-center border-0">
                   <div class="card-header-title">
                    <h3 class="text-dark-2 mb-2 h4">Mart Sub-Categories Table</h3>
                    <p class="mb-0 text-dark-2">Manage all mart sub-categories in the system</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 
                        <?php if (in_array('mart-subcategories.create', json_decode(@session('user_permissions'),true))) { ?>
                        <a href="{{ route('mart-subcategories.create', ['category_id' => $categoryId]) }}" class="btn btn-primary rounded-full">
                            <i class="mdi mdi-plus mr-2"></i>Create Sub-Category
                        </a>
                        <?php } ?>
                        <a href="{{ route('mart-categories') }}" class="btn btn-secondary rounded-full">
                            <i class="mdi mdi-arrow-left mr-2"></i>Back to Categories
                        </a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                     <div class="row mb-3">
                         <div class="col-md-6">
                             <div class="form-group">
                                 <label class="font-weight-bold">Parent Category: <span id="parentCategoryTitle" class="text-primary">Loading...</span></label>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="form-group">
                                 <label class="font-weight-bold">Section: <span id="sectionInfo" class="text-info">Loading...</span></label>
                             </div>
                         </div>
                     </div>
                         <div class="table-responsive m-t-10">
                            <table id="subcategoriesTable" class="display nowrap table table-hover table-striped table-bordered table table-striped" cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('mart-subcategories.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active">
                                            <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>Sub-Category Name</th>
                                    <th>Parent Category</th>
                                    <th>Section</th>
                                    <th>Order</th>
                                    <th>Mart Items</th>
                                    <th>Published</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    var database = firebase.firestore();
    var categoryId = "{{ $categoryId }}";
    var ref = database.collection('mart_subcategories').where('parent_category_id', '==', categoryId).orderBy('subcategory_order');
    var placeholderImage = '';
    var user_permissions = '<?php echo @session("user_permissions")?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;
    if ($.inArray('mart-subcategories.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    $(document).ready(function () {
        // Load parent category info
        loadParentCategoryInfo();
        
        jQuery("#data-table_processing").show();
        var placeholder = database.collection('settings').doc('placeHolderImage');
        placeholder.get().then(async function (snapshotsimage) {
            var placeholderImageData = snapshotsimage.data();
            placeholderImage = placeholderImageData.image;
        });

        const table = $('#subcategoriesTable').DataTable({
            pageLength: 10,
            processing: false,
            serverSide: true,
            responsive: true,
            ajax: function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = (checkDeletePermission) ? ['','title', 'parent_category_title', 'section', 'subcategory_order', 'totalProducts','',''] : ['title', 'parent_category_title', 'section', 'subcategory_order', 'totalProducts','',''];
                const orderByField = orderableColumns[orderColumnIndex];
                
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#data-table_processing').show();
                }
                
                ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        $('.subcategory_count').text(0);    
                        console.log("No sub-categories found for this category.");
                        $('#data-table_processing').hide();
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                        return;
                    }

                    let records = [];
                    let filteredRecords = [];    

                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData = doc.data();
                        childData.id = doc.id;
                        childData.totalProducts = await getProductTotal(childData.id);
                        
                        if (searchValue) {
                            if (
                                (childData.title && childData.title.toString().toLowerCase().includes(searchValue)) ||
                                (childData.description && childData.description.toString().toLowerCase().includes(searchValue)) ||
                                (childData.section && childData.section.toString().toLowerCase().includes(searchValue))
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));

                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        if (orderByField === 'totalProducts' || orderByField === 'subcategory_order') {
                            aValue = a[orderByField] ? parseInt(a[orderByField]) : 0;
                            bValue = b[orderByField] ? parseInt(b[orderByField]) : 0;
                        }                        
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });

                    const totalRecords = filteredRecords.length;
                    $('.subcategory_count').text(totalRecords);    

                    filteredRecords.slice(start, start + length).forEach(function (childData) {
                        var id = childData.id;
                        var route1 = '{{route("mart-subcategories.edit",":id")}}';
                        route1 = route1.replace(':id', id);
                        var url = '{{url("mart-items?subcategoryID=id")}}';
                        url = url.replace("id", id);
                        var ImageHtml = childData.photo == '' || childData.photo == null ? '<img alt="" width="100%" style="width:70px;height:70px;" src="' + placeholderImage + '" alt="image">' : '<img onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="" width="100%" style="width:70px;height:70px;" src="' + childData.photo + '" alt="image">'
                        
                        records.push([
                            checkDeletePermission ? '<td class="delete-all"><input type="checkbox" id="is_open_' + childData.id + '" class="is_open" dataId="' + childData.id + '"><label class="col-3 control-label"\n' + 'for="is_open_' + childData.id + '" ></label></td>' : '',
                            ImageHtml+'<a href="' + route1 + '">' + childData.title + '</a>',
                            '<span class="badge badge-info">' + (childData.parent_category_title || 'Unknown') + '</span>',
                            '<span class="badge badge-secondary">' + (childData.section || 'General') + '</span>',
                            '<span class="badge badge-light">' + (childData.subcategory_order || 1) + '</span>',
                            '<a href="' + url + '">'+childData.totalProducts+'</a>',
                            childData.publish ? '<label class="switch"><input type="checkbox" checked id="' + childData.id + '" name="isSwitch"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" id="' + childData.id + '" name="isSwitch"><span class="slider round"></span></label>',
                            '<span class="action-btn"><a href="' + route1 + '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a><?php if(in_array('mart-subcategories.delete', json_decode(@session('user_permissions'),true))){ ?> <a id="' + childData.id + '" name="subcategory-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a><?php } ?></span>'                           
                        ]);
                    });

                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords,
                        recordsFiltered: totalRecords,
                        data: records
                    });
                }).catch(function (error) {
                    console.error("Error fetching sub-categories:", error);
                    $('#data-table_processing').hide();
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: []
                    });
                });
            },           
            order: (checkDeletePermission) ? [4, 'asc'] : [3,'asc'],
            columnDefs: [
                { orderable: false, targets: (checkDeletePermission) ? [0,5,6] : [4, 5] },
            ],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": ""
            },
        });
        table.columns.adjust().draw();
    });

    // Load parent category information
    function loadParentCategoryInfo() {
        database.collection('mart_categories').doc(categoryId).get().then(function(doc) {
            if (doc.exists) {
                var data = doc.data();
                $('#parentCategoryTitle').text(data.title);
                $('#sectionInfo').text(data.section || 'General');
                $('.parent-category-info').html('<span class="badge badge-info">Section: ' + (data.section || 'General') + '</span>');
            }
        }).catch(function(error) {
            console.error('Error loading parent category:', error);
        });
    }

    // Get product count for sub-category
    async function getProductTotal(subcategoryId) {
        try {
            const querySnapshot = await database.collection('mart_items')
                .where('subcategoryID', '==', subcategoryId)
                .get();
            return querySnapshot.size;
        } catch (error) {
            console.error('Error getting product count:', error);
            return 0;
        }
    }

    $(document).on("click", "a[name='subcategory-delete']", async function (e) {
        var id = this.id;
        var subcategoryTitle = '';
        try {
            var doc = await database.collection('mart_subcategories').doc(id).get();
            if (doc.exists) {
                subcategoryTitle = doc.data().title || 'Unknown';
            }
        } catch (error) {
            console.error('Error getting subcategory title:', error);
        }
        await deleteDocumentWithImage('mart_subcategories',id,'photo');
        console.log('✅ Mart Sub-Category deleted successfully, now logging activity...');
        try {
            if (typeof logActivity === 'function') {
                console.log('🔍 Calling logActivity for mart subcategory deletion...');
                await logActivity('mart_subcategories', 'deleted', 'Deleted mart sub-category: ' + subcategoryTitle);
                console.log('✅ Activity logging completed successfully');
            } else {
                console.error('❌ logActivity function is not available');
            }
        } catch (error) {
            console.error('❌ Error calling logActivity:', error);
        }
        updateParentCategoryCount();
        window.location.reload();
    });

    $("#is_active").click(function () {
        $("#subcategoriesTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(async function () {
        if ($('#subcategoriesTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                jQuery("#data-table_processing").show();
                var selectedSubcategories = [];
                for (let i = 0; i < $('#subcategoriesTable .is_open:checked').length; i++) {
                    var dataId = $('#subcategoriesTable .is_open:checked').eq(i).attr('dataId');
                    try {
                        var doc = await database.collection('mart_subcategories').doc(dataId).get();
                        if (doc.exists) {
                            selectedSubcategories.push(doc.data().title || 'Unknown');
                        }
                    } catch (error) {
                        console.error('Error getting subcategory title:', error);
                    }
                }
                for (let i = 0; i < $('#subcategoriesTable .is_open:checked').length; i++) {
                    var dataId = $('#subcategoriesTable .is_open:checked').eq(i).attr('dataId');
                    await deleteDocumentWithImage('mart_subcategories',dataId,'photo');
                }
                console.log('✅ Bulk mart subcategory deletion completed, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for bulk mart subcategory deletion...');
                        await logActivity('mart_subcategories', 'bulk_deleted', 'Bulk deleted mart sub-categories: ' + selectedSubcategories.join(', '));
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
                updateParentCategoryCount();
                window.location.reload();
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    $(document).on("click", "input[name='isSwitch']", async function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        var subcategoryTitle = '';
        try {
            var doc = await database.collection('mart_subcategories').doc(id).get();
            if (doc.exists) {
                subcategoryTitle = doc.data().title || 'Unknown';
            }
        } catch (error) {
            console.error('Error getting subcategory title:', error);
        }
        if (ischeck) {
            database.collection('mart_subcategories').doc(id).update({'publish': true}).then(async function (result) {
                console.log('✅ Mart Sub-Category published successfully, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for mart subcategory publish...');
                        await logActivity('mart_subcategories', 'published', 'Published mart sub-category: ' + subcategoryTitle);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
            });
        } else {
            database.collection('mart_subcategories').doc(id).update({'publish': false}).then(async function (result) {
                console.log('✅ Mart Sub-Category unpublished successfully, now logging activity...');
                try {
                    if (typeof logActivity === 'function') {
                        console.log('🔍 Calling logActivity for mart subcategory unpublish...');
                        await logActivity('mart_subcategories', 'unpublished', 'Unpublished mart sub-category: ' + subcategoryTitle);
                        console.log('✅ Activity logging completed successfully');
                    } else {
                        console.error('❌ logActivity function is not available');
                    }
                } catch (error) {
                    console.error('❌ Error calling logActivity:', error);
                }
            });
        }
    });

    // Update parent category sub-category count
    function updateParentCategoryCount() {
        database.collection('mart_subcategories')
            .where('parent_category_id', '==', categoryId)
            .get().then(function(querySnapshot) {
                database.collection('mart_categories').doc(categoryId).update({
                    subcategories_count: querySnapshot.size,
                    has_subcategories: querySnapshot.size > 0
                });
            });
    }
</script>
@endsection
