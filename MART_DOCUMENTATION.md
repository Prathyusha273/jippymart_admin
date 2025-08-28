# Mart System Documentation

## Overview

The Mart system is a comprehensive e-commerce management module within the JippyMart admin platform. It manages mart vendors, their products (mart items), and categories, providing a complete solution for grocery/convenience store operations.

## System Architecture

### Technology Stack
- **Backend**: Laravel 10.x (PHP)
- **Database**: Firebase Firestore (NoSQL)
- **Frontend**: Blade templates with jQuery and DataTables
- **Authentication**: Laravel Auth with role-based permissions
- **File Storage**: Firebase Storage for images

### Project Structure
```
app/Http/Controllers/
├── MartController.php          # Main mart management
├── MartItemController.php      # Mart products management
└── MartCategoryController.php  # Mart categories management

resources/views/
├── mart/                       # Mart vendor views
├── martItems/                  # Mart products views
└── martCategories/             # Mart categories views

database/seeders/
├── MartPermissionsSeeder.php   # Mart permissions
└── MartItemsPermissionsSeeder.php # Mart items permissions
```

## Firestore Collections

### 1. `vendors` Collection
**Purpose**: Stores mart vendor information
**Key Fields**:
- `id`: Unique vendor identifier
- `title`: Mart name
- `vType`: Must be "mart" or "Mart" (case-sensitive)
- `author`: User ID of the mart owner
- `authorName`: Owner's name
- `phonenumber`: Contact number
- `email`: Contact email
- `photo`: Mart logo/image
- `location`: Geographic coordinates
- `zoneId`: Zone assignment
- `enabledDelivery`: Boolean for delivery service
- `isActive`: Operational status
- `adminCommission`: Commission structure
- `createdAt`: Registration timestamp
- `publish`: Visibility status

**Query Filters**:
```javascript
// Get all mart vendors
database.collection('vendors').where('vType', '==', 'mart')

// Get active mart vendors in specific zone
database.collection('vendors')
  .where('vType', '==', 'mart')
  .where('zoneId', '==', zoneId)
  .where('isActive', '==', true)
```

### 2. `mart_items` Collection
**Purpose**: Stores individual products sold by marts
**Key Fields**:
- `id`: Unique product identifier
- `name`: Product name
- `price`: Regular price (string)
- `disPrice`: Discount price (string, optional)
- `description`: Product description
- `vendorID`: Reference to vendor document
- `categoryID`: Reference to mart_categories document
- `photo`: Product image
- `photos`: Array of additional images
- `publish`: Visibility status
- `nonveg`: Boolean for non-vegetarian items
- `veg`: Boolean for vegetarian items
- `isAvailable`: Stock availability
- `quantity`: Stock quantity (-1 for unlimited)
- `calories`, `grams`, `proteins`, `fats`: Nutritional info
- `addOnsTitle`, `addOnsPrice`: Customization options
- `sizeTitle`, `sizePrice`: Size variants
- `attributes`, `variants`: Product attributes
- `reviewsCount`, `reviewsSum`: Rating data
- `takeawayOption`: Takeaway availability
- `migratedBy`: Import source tracking
- `createdAt`, `updated_at`: Timestamps

**Query Filters**:
```javascript
// Get items by vendor
database.collection('mart_items').where('vendorID', '==', vendorId)

// Get items by category
database.collection('mart_items').where('categoryID', '==', categoryId)

// Get vegetarian items
database.collection('mart_items').where('nonveg', '==', false)
```

### 3. `mart_categories` Collection
**Purpose**: Organizes mart products into categories
**Key Fields**:
- `id`: Unique category identifier
- `title`: Category name
- `description`: Category description
- `photo`: Category image
- `publish`: Visibility status
- `show_in_homepage`: Featured category flag
- `mart_id`: Associated mart (optional)
- `review_attributes`: Array of review criteria
- `migratedBy`: Import source tracking

**Query Filters**:
```javascript
// Get published categories
database.collection('mart_categories').where('publish', '==', true)

// Get categories ordered by title
database.collection('mart_categories').orderBy('title')
```

### 4. `users` Collection
**Purpose**: Stores user accounts including mart owners and vendors
**Key Fields**:
- `id`: Unique user identifier
- `firstName`, `lastName`: User's full name
- `email`: User's email address
- `password`: Hashed password
- `role`: User role ('vendor', 'customer', 'admin', etc.)
- `vType`: Vendor type - must be "mart" for mart vendors
- `vendorID`: Reference to vendor document (set when user is assigned to a mart)
- `active`: Account status (true/false)
- `profilePictureURL`: User profile image
- `phoneNumber`: Contact number
- `zoneId`: Geographic zone assignment
- `subscriptionPlanId`: Business plan reference
- `createdAt`: Account creation timestamp
- `migratedBy`: Import source tracking

**Mart Vendor Filtering**:
```javascript
// Get all mart vendors (users with vType='mart' AND role='vendor')
database.collection('users')
  .where('vType', '==', 'mart')
  .where('role', '==', 'vendor')

// Get available mart vendors (not assigned to any mart)
database.collection('users')
  .where('vType', '==', 'mart')
  .where('role', '==', 'vendor')
  .where('vendorID', '==', '') // or null/undefined
```

### 5. Supporting Collections
- `zone`: Geographic zones for delivery
- `currencies`: Currency configuration
- `settings`: System settings including placeholder images
- `subscription_plans`: Business model plans
- `restaurant_orders`: Order management (shared with restaurants)
- `payouts`: Financial transactions

## Workflow

### 1. Mart Vendor Management

#### User Account Creation Workflow
1. **Access**: Navigate to `/vendors/create` or user management
2. **Authentication**: Requires `vendors.create` permission
3. **Data Entry**: Fill user details including:
   - Personal info (name, email, phone)
   - Account settings (password, role)
   - Vendor type selection (`vType: 'mart'`)
   - Zone assignment
4. **Validation**: Server-side validation of required fields
5. **Storage**: Data saved to `users` collection with `role: 'vendor'` and `vType: 'mart'`
6. **Confirmation**: User account created and ready for mart assignment

#### Mart Creation Workflow
1. **Access**: Navigate to `/marts/create`
2. **Authentication**: Requires `marts.create` permission
3. **Vendor Selection**: Choose from available mart vendors (users with `vType: 'mart'` and `role: 'vendor'`)
4. **Data Entry**: Fill mart details including:
   - Basic info (name, contact, location)
   - Business settings (delivery, commission)
   - Zone assignment
   - Category assignments
5. **Validation**: Server-side validation of required fields
6. **User Assignment**: Update selected user's `vendorID` field with new mart ID
7. **Storage**: Data saved to `vendors` collection with `vType: 'mart'`
8. **Confirmation**: Redirect to mart list with success message

#### Management Workflow
1. **Listing**: `/marts` - Displays all mart vendors with filtering options
2. **Filtering**: By zone, delivery type, business model, category
3. **Actions**: View, Edit, Delete (with permissions)
4. **Status Management**: Toggle active/inactive status
5. **Analytics**: Dashboard with counts and statistics

### 2. Mart Items Management

#### Creation Workflow
1. **Access**: Navigate to `/mart-items/create` or `/mart-items/create/{vendorId}`
2. **Authentication**: Requires `mart-items.create` permission
3. **Data Entry**: Product details including:
   - Basic info (name, description, price)
   - Category assignment
   - Vendor assignment
   - Nutritional information
   - Images and variants
4. **Validation**: Price validation, category/vendor existence
5. **Storage**: Data saved to `mart_items` collection
6. **Confirmation**: Redirect to items list

#### Bulk Import Workflow
1. **Template Download**: `/mart-items/download-template`
2. **Data Preparation**: Excel file with required columns
3. **Upload**: `/mart-items/import` with file validation
4. **Processing**: 
   - Vendor ID/name resolution
   - Category ID/name resolution
   - Data validation and transformation
   - Batch insertion to Firestore
5. **Results**: Success/error reporting

#### Management Workflow
1. **Listing**: `/mart-items` or `/mart-items/{vendorId}`
2. **Filtering**: By vendor, category, food type (veg/non-veg)
3. **Inline Editing**: Price updates without page reload
4. **Bulk Operations**: Status changes, deletions
5. **Export**: Excel, PDF, CSV formats

### 3. Mart Categories Management

#### Creation Workflow
1. **Access**: Navigate to `/mart-categories/create`
2. **Authentication**: Requires `mart-categories.create` permission
3. **Data Entry**: Category details including:
   - Title and description
   - Image upload
   - Visibility settings
   - Review attributes
4. **Validation**: Required field validation
5. **Storage**: Data saved to `mart_categories` collection
6. **Confirmation**: Redirect to categories list

#### Bulk Import Workflow
1. **Template Download**: `/mart-categories/download-template`
2. **Data Preparation**: Excel file with category data
3. **Upload**: `/mart-categories/import`
4. **Processing**: Batch insertion with validation
5. **Results**: Success/error reporting

## Permissions System

### Permission Structure
```php
// Mart Vendors
'marts' => [
    'marts',           // View mart list
    'marts.create',    // Create new mart
    'marts.edit',      // Edit existing mart
    'marts.view',      // View mart details
    'marts.delete'     // Delete mart
]

// Mart Items
'mart-items' => [
    'mart-items',           // View items list
    'mart-items.create',    // Create new item
    'mart-items.edit',      // Edit existing item
    'mart-items.delete'     // Delete item
]

// Mart Categories
'mart-categories' => [
    'mart-categories',           // View categories list
    'mart-categories.create',    // Create new category
    'mart-categories.edit',      // Edit existing category
    'mart-categories.delete'     // Delete category
]
```

### Role-Based Access
- **Super Administrator**: Full access to all mart operations
- **Custom Roles**: Granular permission assignment
- **Menu Visibility**: Dynamic menu rendering based on permissions

## Data Relationships

### Entity Relationships
```
users (mart vendors)
├── 1:1 vendors (mart store) - via vendorID field
└── 1:1 users (owner account) - self-reference

vendors (mart) 
├── 1:N mart_items (products)
├── 1:N mart_categories (categories)
└── 1:1 users (owner account) - via author field

mart_categories
└── 1:N mart_items (products in category)

mart_items
├── N:1 vendors (selling mart)
└── N:1 mart_categories (product category)
```

### Data Integrity
- **Vendor Validation**: Mart items must reference valid mart vendors
- **Category Validation**: Mart items must reference valid categories
- **Owner Validation**: Mart vendors must have valid owner accounts
- **Zone Validation**: Mart vendors must be assigned to valid zones
- **User-Vendor Linkage**: Users with `vType: 'mart'` and `role: 'vendor'` must have valid `vendorID` when assigned to a mart
- **Unique Assignment**: Each mart vendor user can only be assigned to one mart store

## API Endpoints

### Mart Vendors
- `GET /marts` - List all marts
- `GET /marts/create` - Create mart form
- `POST /marts` - Store new mart
- `GET /marts/edit/{id}` - Edit mart form
- `PUT /marts/{id}` - Update mart
- `GET /marts/view/{id}` - View mart details
- `GET /marts/foods/{id}` - View mart items
- `GET /marts/orders/{id}` - View mart orders

### User Management (Mart Vendors)
- `GET /vendors` - List all vendors (including mart vendors)
- `GET /vendors/create` - Create vendor user account
- `POST /vendors` - Store new vendor user
- `GET /vendors/edit/{id}` - Edit vendor user
- `PUT /vendors/{id}` - Update vendor user
- `GET /users` - List all users (including mart vendors)
- `GET /users/create` - Create user account
- `GET /users/edit/{id}` - Edit user account

### Mart Items
- `GET /mart-items` - List all items
- `GET /mart-items/{vendorId}` - List items by vendor
- `GET /mart-item/create` - Create item form
- `POST /mart-items` - Store new item
- `GET /mart-items/edit/{id}` - Edit item form
- `PATCH /mart-items/inline-update/{id}` - Quick price update
- `POST /mart-items/import` - Bulk import
- `GET /mart-items/download-template` - Download import template

### Mart Categories
- `GET /mart-categories` - List all categories
- `GET /mart-categories/create` - Create category form
- `POST /mart-categories` - Store new category
- `GET /mart-categories/edit/{id}` - Edit category form
- `POST /mart-categories/import` - Bulk import
- `GET /mart-categories/download-template` - Download import template

## Key Features

### 1. Real-time Data Management
- Firebase Firestore integration for real-time updates
- Live data synchronization across all views
- Optimistic UI updates with error handling

### 2. User-Vendor Management
- Two-step mart creation process (user account → mart assignment)
- Automatic vendor availability filtering
- User-vendor linkage validation
- Bulk user import with mart vendor support

### 3. Advanced Filtering
- Multi-criteria filtering (zone, category, status)
- Search functionality with debounced input
- Dynamic query building based on filter selections

### 4. Bulk Operations
- Excel import/export for mass data management
- Bulk status updates
- Template-based data import with validation

### 5. Financial Management
- Commission tracking per vendor
- Payout history and wallet management
- Order revenue tracking

### 6. Analytics Dashboard
- Real-time statistics (total, active, inactive, new marts)
- Performance metrics
- Trend analysis

### 7. Image Management
- Firebase Storage integration
- Automatic image optimization
- Placeholder image fallbacks

## Security Considerations

### Authentication & Authorization
- Laravel middleware protection on all routes
- Role-based permission system
- Session-based authentication

### Data Validation
- Server-side validation for all inputs
- Firestore security rules
- SQL injection prevention through Eloquent ORM

### File Upload Security
- File type validation
- Size restrictions
- Secure file storage in Firebase

## Performance Optimization

### Database Optimization
- Indexed queries on frequently accessed fields
- Pagination for large datasets
- Efficient Firestore query patterns

### Frontend Optimization
- Lazy loading of images
- Debounced search inputs
- Optimized DataTables configuration
- CDN integration for static assets

## Monitoring & Logging

### Activity Tracking
- Global activity logger for all operations
- User action tracking
- Error logging and monitoring

### Error Handling
- Comprehensive error catching
- User-friendly error messages
- Detailed logging for debugging

## Future Enhancements

### Planned Features
1. **Inventory Management**: Real-time stock tracking
2. **Advanced Analytics**: Detailed reporting and insights
3. **Multi-language Support**: Internationalization
4. **Mobile App Integration**: API endpoints for mobile apps
5. **Automated Scheduling**: Business hours management
6. **Advanced Search**: Elasticsearch integration

### Scalability Considerations
- Horizontal scaling with load balancers
- Database sharding strategies
- Caching layer implementation
- Microservices architecture migration

## Troubleshooting

### Common Issues
1. **Firebase Connection**: Check credentials and project configuration
2. **Permission Errors**: Verify user roles and permissions
3. **Import Failures**: Validate Excel file format and data
4. **Image Upload Issues**: Check Firebase Storage permissions

### Debug Tools
- Browser console logging
- Laravel debug mode
- Firebase console monitoring
- Network request inspection

## Conclusion

The Mart system provides a comprehensive solution for managing e-commerce operations within the JippyMart platform. Its modular architecture, real-time data management, and robust permission system make it suitable for scaling from small convenience stores to large retail chains.

The integration with Firebase Firestore ensures real-time data synchronization, while the Laravel backend provides robust security and validation. The system's flexibility allows for easy customization and extension to meet specific business requirements.
