# Mart Category API Documentation

This document provides comprehensive information about the Mart Category API endpoints for managing mart categories in the system.

## Base URL
```
/api/mart/categories
```

## Authentication
- **Public Endpoints**: No authentication required
- **Protected Endpoints**: Require Bearer token authentication via `auth:sanctum` middleware

## Data Structure

### Category Object
```json
{
  "id": "68b16f87cac4e",
  "title": "Groceries",
  "description": "Fresh groceries and household items",
  "photo": "https://firebasestorage.googleapis.com/v0/b/jippymart-27c08.firebasestorage.app/o/images%2Fgroc_1756460123245.jpg?alt=media&token=68038522-7865-40dd-bf5f-0f8bc64db7c1",
  "publish": true,
  "show_in_homepage": true,
  "section": "Grocery & Kitchen",
  "category_order": 1,
  "section_order": 1,
  "review_attributes": ["quality", "freshness"],
  "subcategories_count": 0,
  "created_at": "2024-01-15T10:30:00.000Z",
  "updated_at": "2024-01-15T10:30:00.000Z",
  "created_by": "user_id",
  "updated_by": "user_id"
}
```

## Available Sections
- Grocery & Kitchen
- Fruits & Vegetables
- Dairy, Bread & Eggs
- Packaged Foods & Snacks
- Beverages & Juices
- Zepto Café (Ready-to-Eat)
- Beauty & Personal Care
- Apparel & Fashion
- Electronics & Appliances
- Toys & Baby
- Pet Care
- Pharmacy & Health
- Home & Household Essentials
- Cleaning & Laundry
- Kitchenware & Storage
- Stationery & Books
- Sports & Fitness
- Automotive & Tools

---

## Endpoints

### 1. Get All Categories
**GET** `/api/mart/categories`

**Description**: Retrieve all mart categories with filtering, pagination, and sorting options.

**Query Parameters**:
- `publish` (boolean, optional): Filter by publish status
- `show_in_homepage` (boolean, optional): Filter by homepage visibility
- `section` (string, optional): Filter by section name
- `search` (string, optional): Search in title and description
- `page` (integer, optional, default: 1): Page number for pagination
- `limit` (integer, optional, default: 20, max: 100): Number of items per page
- `sort_by` (string, optional, default: category_order): Sort field (title, category_order, section_order, created_at)
- `sort_order` (string, optional, default: asc): Sort direction (asc, desc)

**Example Request**:
```bash
GET /api/mart/categories?publish=true&section=Grocery%20%26%20Kitchen&page=1&limit=10&sort_by=category_order&sort_order=asc
```

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "68b16f87cac4e",
      "title": "Groceries",
      "description": "Fresh groceries and household items",
      "photo": "https://example.com/image.jpg",
      "publish": true,
      "show_in_homepage": true,
      "section": "Grocery & Kitchen",
      "category_order": 1,
      "section_order": 1,
      "review_attributes": ["quality", "freshness"],
      "subcategories_count": 0,
      "created_at": "2024-01-15T10:30:00.000Z",
      "updated_at": "2024-01-15T10:30:00.000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 25,
    "has_more": true,
    "filters_applied": {
      "publish": true,
      "section": "Grocery & Kitchen"
    },
    "sort_by": "category_order",
    "sort_order": "asc"
  }
}
```

---

### 2. Get Category by ID
**GET** `/api/mart/categories/{category_id}`

**Description**: Retrieve a specific mart category by its ID.

**Path Parameters**:
- `category_id` (string, required): The unique identifier of the category

**Example Request**:
```bash
GET /api/mart/categories/68b16f87cac4e
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "id": "68b16f87cac4e",
    "title": "Groceries",
    "description": "Fresh groceries and household items",
    "photo": "https://example.com/image.jpg",
    "publish": true,
    "show_in_homepage": true,
    "section": "Grocery & Kitchen",
    "category_order": 1,
    "section_order": 1,
    "review_attributes": ["quality", "freshness"],
    "subcategories_count": 0,
    "created_at": "2024-01-15T10:30:00.000Z",
    "updated_at": "2024-01-15T10:30:00.000Z"
  }
}
```

---

### 3. Create Category
**POST** `/api/mart/categories`

**Description**: Create a new mart category. **Requires authentication.**

**Request Body**:
```json
{
  "title": "Fresh Vegetables",
  "description": "Fresh and organic vegetables",
  "photo": "https://example.com/vegetables.jpg",
  "publish": true,
  "show_in_homepage": false,
  "section": "Fruits & Vegetables",
  "category_order": 2,
  "section_order": 1,
  "review_attributes": ["freshness", "quality", "organic"]
}
```

**Validation Rules**:
- `title` (required, string, max: 100): Category title
- `description` (required, string, max: 500): Category description
- `photo` (optional, string, url): Category image URL
- `publish` (optional, boolean): Publish status
- `show_in_homepage` (optional, boolean): Homepage visibility (max 5 categories)
- `section` (optional, string, max: 100): Section name
- `category_order` (optional, integer, min: 1): Display order within section
- `section_order` (optional, integer, min: 1): Section display order
- `review_attributes` (optional, array): Array of review attribute IDs

**Example Response**:
```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "id": "68b16f87cac4f",
    "title": "Fresh Vegetables",
    "description": "Fresh and organic vegetables",
    "photo": "https://example.com/vegetables.jpg",
    "publish": true,
    "show_in_homepage": false,
    "section": "Fruits & Vegetables",
    "category_order": 2,
    "section_order": 1,
    "review_attributes": ["freshness", "quality", "organic"],
    "subcategories_count": 0,
    "created_at": "2024-01-15T10:30:00.000Z",
    "updated_at": "2024-01-15T10:30:00.000Z",
    "created_by": "user_id"
  }
}
```

---

### 4. Update Category
**PUT** `/api/mart/categories/{category_id}`

**Description**: Update an existing mart category. **Requires authentication.**

**Path Parameters**:
- `category_id` (string, required): The unique identifier of the category

**Request Body** (all fields optional):
```json
{
  "title": "Updated Groceries",
  "description": "Updated description",
  "photo": "https://example.com/updated-image.jpg",
  "publish": false,
  "show_in_homepage": true,
  "section": "Grocery & Kitchen",
  "category_order": 3,
  "section_order": 2,
  "review_attributes": ["quality", "freshness", "organic"]
}
```

**Example Response**:
```json
{
  "success": true,
  "message": "Category updated successfully",
  "data": {
    "id": "68b16f87cac4e",
    "title": "Updated Groceries",
    "description": "Updated description",
    "photo": "https://example.com/updated-image.jpg",
    "publish": false,
    "show_in_homepage": true,
    "section": "Grocery & Kitchen",
    "category_order": 3,
    "section_order": 2,
    "review_attributes": ["quality", "freshness", "organic"],
    "subcategories_count": 0,
    "created_at": "2024-01-15T10:30:00.000Z",
    "updated_at": "2024-01-15T11:00:00.000Z",
    "updated_by": "user_id"
  }
}
```

---

### 5. Delete Category
**DELETE** `/api/mart/categories/{category_id}`

**Description**: Delete a mart category. **Requires authentication.**

**Path Parameters**:
- `category_id` (string, required): The unique identifier of the category

**Constraints**:
- Cannot delete categories that have subcategories

**Example Request**:
```bash
DELETE /api/mart/categories/68b16f87cac4e
```

**Example Response**:
```json
{
  "success": true,
  "message": "Category deleted successfully"
}
```

---

### 6. Get Homepage Categories
**GET** `/api/mart/categories/homepage`

**Description**: Retrieve categories that are published and marked for homepage display.

**Query Parameters**:
- `limit` (integer, optional, default: 10, max: 20): Number of categories to return

**Example Request**:
```bash
GET /api/mart/categories/homepage?limit=5
```

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "68b16f87cac4e",
      "title": "Groceries",
      "description": "Fresh groceries and household items",
      "photo": "https://example.com/image.jpg",
      "publish": true,
      "show_in_homepage": true,
      "section": "Grocery & Kitchen",
      "category_order": 1,
      "section_order": 1,
      "review_attributes": ["quality", "freshness"],
      "subcategories_count": 0
    }
  ],
  "meta": {
    "total": 5,
    "limit": 5
  }
}
```

---

### 7. Get Categories with Subcategories
**GET** `/api/mart/categories/with-subcategories`

**Description**: Retrieve categories that have subcategories.

**Query Parameters**:
- `publish` (boolean, optional): Filter by publish status
- `page` (integer, optional, default: 1): Page number
- `limit` (integer, optional, default: 20, max: 50): Items per page

**Example Request**:
```bash
GET /api/mart/categories/with-subcategories?publish=true&page=1&limit=10
```

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "68b16f87cac4e",
      "title": "Groceries",
      "description": "Fresh groceries and household items",
      "photo": "https://example.com/image.jpg",
      "publish": true,
      "show_in_homepage": true,
      "section": "Grocery & Kitchen",
      "category_order": 1,
      "section_order": 1,
      "review_attributes": ["quality", "freshness"],
      "subcategories_count": 3
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 15,
    "has_more": true
  }
}
```

---

### 8. Search Categories
**POST** `/api/mart/categories/search`

**Description**: Search categories by title or description.

**Request Body**:
```json
{
  "query": "groceries",
  "publish": true,
  "section": "Grocery & Kitchen",
  "page": 1,
  "limit": 20
}
```

**Validation Rules**:
- `query` (required, string, min: 2, max: 100): Search term
- `publish` (optional, boolean): Filter by publish status
- `section` (optional, string, max: 100): Filter by section
- `page` (optional, integer, min: 1): Page number
- `limit` (optional, integer, min: 1, max: 50): Items per page

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "68b16f87cac4e",
      "title": "Groceries",
      "description": "Fresh groceries and household items",
      "photo": "https://example.com/image.jpg",
      "publish": true,
      "show_in_homepage": true,
      "section": "Grocery & Kitchen",
      "category_order": 1,
      "section_order": 1,
      "review_attributes": ["quality", "freshness"],
      "subcategories_count": 0
    }
  ],
  "meta": {
    "query": "groceries",
    "current_page": 1,
    "per_page": 20,
    "total": 1,
    "has_more": false
  }
}
```

---

### 9. Bulk Update Categories
**POST** `/api/mart/categories/bulk-update`

**Description**: Update multiple categories at once. **Requires authentication.**

**Request Body**:
```json
{
  "category_ids": ["68b16f87cac4e", "68b16f87cac4f"],
  "updates": {
    "publish": false,
    "show_in_homepage": true,
    "section": "Grocery & Kitchen",
    "category_order": 5,
    "section_order": 1
  }
}
```

**Validation Rules**:
- `category_ids` (required, array, min: 1): Array of category IDs
- `category_ids.*` (string): Valid category ID
- `updates` (required, array): Fields to update
- `updates.publish` (optional, boolean): Publish status
- `updates.show_in_homepage` (optional, boolean): Homepage visibility
- `updates.section` (optional, string, max: 100): Section name
- `updates.category_order` (optional, integer, min: 1): Category order
- `updates.section_order` (optional, integer, min: 1): Section order

**Example Response**:
```json
{
  "success": true,
  "message": "Categories updated successfully",
  "data": {
    "updated_count": 2,
    "failed_count": 0,
    "failed_ids": []
  }
}
```

---

### 10. Get Available Sections
**GET** `/api/mart/categories/sections`

**Description**: Retrieve list of available sections for categories.

**Example Request**:
```bash
GET /api/mart/categories/sections
```

**Example Response**:
```json
{
  "success": true,
  "data": [
    "Grocery & Kitchen",
    "Fruits & Vegetables",
    "Dairy, Bread & Eggs",
    "Packaged Foods & Snacks",
    "Beverages & Juices",
    "Zepto Café (Ready-to-Eat)",
    "Beauty & Personal Care",
    "Apparel & Fashion",
    "Electronics & Appliances",
    "Toys & Baby",
    "Pet Care",
    "Pharmacy & Health",
    "Home & Household Essentials",
    "Cleaning & Laundry",
    "Kitchenware & Storage",
    "Stationery & Books",
    "Sports & Fitness",
    "Automotive & Tools"
  ]
}
```

---

### 11. Get Categories by Section
**GET** `/api/mart/categories/section/{section}`

**Description**: Retrieve categories filtered by a specific section.

**Path Parameters**:
- `section` (string, required): Section name

**Query Parameters**:
- `publish` (boolean, optional): Filter by publish status
- `page` (integer, optional, default: 1): Page number
- `limit` (integer, optional, default: 20, max: 50): Items per page

**Example Request**:
```bash
GET /api/mart/categories/section/Grocery%20%26%20Kitchen?publish=true&page=1&limit=10
```

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": "68b16f87cac4e",
      "title": "Groceries",
      "description": "Fresh groceries and household items",
      "photo": "https://example.com/image.jpg",
      "publish": true,
      "show_in_homepage": true,
      "section": "Grocery & Kitchen",
      "category_order": 1,
      "section_order": 1,
      "review_attributes": ["quality", "freshness"],
      "subcategories_count": 0
    }
  ],
  "meta": {
    "section": "Grocery & Kitchen",
    "current_page": 1,
    "per_page": 10,
    "total": 5,
    "has_more": false
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "description": ["The description field is required."]
  }
}
```

### Authentication Error (401)
```json
{
  "success": false,
  "message": "User not authenticated"
}
```

### Not Found Error (404)
```json
{
  "success": false,
  "message": "Category not found"
}
```

### Business Logic Error (400)
```json
{
  "success": false,
  "message": "Already 5 mart categories are active for show in homepage"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to get categories: Database connection error"
}
```

---

## Business Rules

1. **Homepage Limit**: Maximum 5 categories can be marked for homepage display
2. **Subcategory Protection**: Categories with subcategories cannot be deleted
3. **Order Management**: Categories are ordered by `category_order` within sections
4. **Section Organization**: Categories are grouped by sections for better organization
5. **Publish Control**: Only published categories are visible in public endpoints
6. **Review Attributes**: Categories can have multiple review attributes for quality assessment

---

## Rate Limiting

- Public endpoints: 100 requests per minute per IP
- Protected endpoints: 1000 requests per minute per authenticated user

---

## Notes

- All timestamps are in ISO 8601 format (UTC)
- Image URLs should be publicly accessible
- Review attributes are referenced by their IDs
- The system automatically generates unique IDs for new categories
- Bulk operations are atomic - either all succeed or all fail
- Search is case-insensitive and supports partial matching




