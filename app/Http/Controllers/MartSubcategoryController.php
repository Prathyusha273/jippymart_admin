<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Storage;

class MartSubcategoryController extends Controller
{   
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display sub-categories for a specific parent category
     */
    public function index($categoryId)
    {
        return view("martSubcategories.index")->with('categoryId', $categoryId);
    }

    /**
     * Show the form for creating a new sub-category
     */
    public function create($categoryId)
    {
        return view('martSubcategories.create')->with('categoryId', $categoryId);
    }

    /**
     * Show the form for editing a sub-category
     */
    public function edit($id)
    {
        return view('martSubcategories.edit')->with('id', $id);
    }

    /**
     * Bulk import sub-categories from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $spreadsheet = IOFactory::load($request->file('file'));
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

        $headers = array_map('trim', array_shift($rows));
        
        // Initialize Firestore client
        $firestore = new FirestoreClient([
            'projectId' => config('firestore.project_id'),
            'keyFilePath' => config('firestore.credentials'),
        ]);
        
        $collection = $firestore->collection('mart_subcategories');
        $imported = 0;
        $errors = [];
        
        foreach ($rows as $index => $row) {
            $data = array_combine($headers, $row);
            $rowNumber = $index + 2; // +2 because we removed header row and arrays are 0-indexed
            
            // Validate required fields
            if (empty($data['title'])) {
                $errors[] = "Row $rowNumber: Title is required";
                continue;
            }
            
            // Process parent category - handle both ID and name lookup
            $parentCategoryId = $this->resolveParentCategoryId($data['parent_category_id'] ?? '', $firestore);
            if (!$parentCategoryId) {
                $errors[] = "Row $rowNumber: Parent category '{$data['parent_category_id']}' not found. Please use category ID or name";
                continue;
            }
            
            // Get parent category info
            $parentCategory = $this->getParentCategoryInfo($parentCategoryId, $firestore);
            if (!$parentCategory) {
                $errors[] = "Row $rowNumber: Parent category data not found";
                continue;
            }
            
            // Process review attributes - handle both comma-separated IDs and names
            $reviewAttributes = [];
            if (!empty($data['review_attributes'])) {
                $reviewAttributeInputs = array_filter(array_map('trim', explode(',', $data['review_attributes'])));
                foreach ($reviewAttributeInputs as $input) {
                    $attributeId = $this->resolveReviewAttributeId($input, $firestore);
                    if ($attributeId) {
                        $reviewAttributes[] = $attributeId;
                    } else {
                        $errors[] = "Row $rowNumber: Review attribute '$input' not found";
                    }
                }
            }
            
            // Also check for additional review attribute columns (I, J, K, etc.)
            $additionalAttributes = [];
            for ($col = 'I'; $col <= 'Z'; $col++) {
                if (isset($data[$col]) && !empty(trim($data[$col]))) {
                    $additionalAttributes[] = trim($data[$col]);
                }
            }
            
            // Process additional review attributes
            foreach ($additionalAttributes as $input) {
                $attributeId = $this->resolveReviewAttributeId($input, $firestore);
                if ($attributeId) {
                    $reviewAttributes[] = $attributeId;
                } else {
                    $errors[] = "Row $rowNumber: Review attribute '$input' not found";
                }
            }
            
            // Process photo - handle media module integration
            $photoUrl = '';
            if (!empty($data['photo'])) {
                $photoInput = trim($data['photo']);
                
                // Check if it's already a URL (existing media)
                if (filter_var($photoInput, FILTER_VALIDATE_URL)) {
                    $photoUrl = $photoInput;
                } else {
                    // Try to find media by name or slug
                    $mediaId = $this->resolveMediaId($photoInput, $firestore);
                    if ($mediaId) {
                        $mediaDoc = $firestore->collection('media')->document($mediaId)->snapshot();
                        if ($mediaDoc->exists()) {
                            $mediaData = $mediaDoc->data();
                            $photoUrl = $mediaData['image_path'] ?? '';
                        }
                    }
                    
                    // If media not found, just use the input as-is (might be a placeholder or will be handled later)
                    if (empty($photoUrl)) {
                        $photoUrl = $photoInput; // Use the input as-is, don't throw error
                    }
                }
            }
            
            // Create document with auto-generated ID - matching create form structure
            $docRef = $collection->add([
                'id' => '', // Will be set to doc ID after creation
                'title' => trim($data['title']),
                'description' => trim($data['description'] ?? ''),
                'photo' => $photoUrl,
                'parent_category_id' => $parentCategoryId,
                'parent_category_title' => $parentCategory['title'],
                'section' => $parentCategory['section'] ?? 'General',
                'section_order' => 1, // Fixed value like in create form
                'category_order' => 1, // Fixed value like in create form
                'subcategory_order' => intval($data['subcategory_order'] ?? 1),
                'mart_id' => '', // Empty string like in create form
                'review_attributes' => $reviewAttributes,
                'publish' => strtolower($data['publish'] ?? 'false') === 'true',
                'show_in_homepage' => strtolower($data['show_in_homepage'] ?? 'false') === 'true',
                'migratedBy' => 'bulk_import',
            ]);
            
            // Set the internal 'id' field to match the Firestore document ID
            $docRef->set(['id' => $docRef->id()], ['merge' => true]);
            
            // Update parent category sub-category count
            $this->updateParentCategoryCount($parentCategoryId, $firestore);
            
            $imported++;
        }
        
        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }
        
        $message = "Mart Sub-Categories imported successfully! ($imported rows)";
        if (!empty($errors)) {
            $message .= "\n\nWarnings:\n" . implode("\n", $errors);
        }
        
        return back()->with('success', $message);
    }

    /**
     * Download import template for sub-categories
     */
    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/mart_subcategories_import_template.xlsx');

        // Create template directory if it doesn't exist
        $templateDir = dirname($filePath);
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }
        
        return response()->download($filePath, 'mart_subcategories_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="mart_subcategories_import_template.xlsx"'
        ]);
    }

    /**
     * Get parent category information
     */
    private function getParentCategoryInfo($parentId, $firestore)
    {
        try {
            $doc = $firestore->collection('mart_categories')->document($parentId)->snapshot();
            if ($doc->exists()) {
                return $doc->data();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }
        return null;
    }

    /**
     * Update parent category sub-category count
     */
    private function updateParentCategoryCount($parentId, $firestore)
    {
        try {
            $subcategories = $firestore->collection('mart_subcategories')
                ->where('parent_category_id', '==', $parentId)
                ->documents();
            
            $count = 0;
            foreach ($subcategories as $doc) {
                $count++;
            }
            
            $firestore->collection('mart_categories')->document($parentId)->update([
                'subcategories_count' => $count,
                'has_subcategories' => $count > 0
            ]);
        } catch (\Exception $e) {
            // Log error if needed
        }
    }

    /**
     * Resolve parent category ID from input (can be ID or name)
     */
    private function resolveParentCategoryId($input, $firestore)
    {
        if (empty($input)) {
            return null;
        }

        // First try as direct ID
        try {
            $categoryDoc = $firestore->collection('mart_categories')->document($input)->snapshot();
            if ($categoryDoc->exists()) {
                return $input; // Return the ID as-is
            }
        } catch (\Exception $e) {
            // Continue to name lookup
        }

        // If not found as ID, try name lookup
        try {
            $categories = $firestore->collection('mart_categories')
                ->where('title', '==', trim($input))
                ->limit(1)
                ->documents();

            foreach ($categories as $category) {
                return $category->id();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    /**
     * Resolve review attribute ID from input (can be ID or name)
     */
    private function resolveReviewAttributeId($input, $firestore)
    {
        if (empty($input)) {
            return null;
        }

        // First try as direct ID
        try {
            $attributeDoc = $firestore->collection('review_attributes')->document($input)->snapshot();
            if ($attributeDoc->exists()) {
                return $input; // Return the ID as-is
            }
        } catch (\Exception $e) {
            // Continue to name lookup
        }

        // If not found as ID, try name lookup
        try {
            $attributes = $firestore->collection('review_attributes')
                ->where('title', '==', trim($input))
                ->limit(1)
                ->documents();

            foreach ($attributes as $attribute) {
                return $attribute->id();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    /**
     * Resolve media ID from input (can be ID, name, or slug)
     */
    private function resolveMediaId($input, $firestore)
    {
        if (empty($input)) {
            return null;
        }

        // First try as direct ID
        try {
            $mediaDoc = $firestore->collection('media')->document($input)->snapshot();
            if ($mediaDoc->exists()) {
                return $input; // Return the ID as-is
            }
        } catch (\Exception $e) {
            // Continue to name/slug lookup
        }

        // Try name lookup
        try {
            $mediaDocs = $firestore->collection('media')
                ->where('name', '==', trim($input))
                ->limit(1)
                ->documents();

            foreach ($mediaDocs as $media) {
                return $media->id();
            }
        } catch (\Exception $e) {
            // Continue to slug lookup
        }

        // Try slug lookup
        try {
            $mediaDocs = $firestore->collection('media')
                ->where('slug', '==', trim($input))
                ->limit(1)
                ->documents();

            foreach ($mediaDocs as $media) {
                return $media->id();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return null;
    }

    /**
     * Generate Excel template for mart sub-categories import
     */
    private function generateTemplate($filePath)
    {
        try {
            // Create new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            // Remove default worksheet and create a new one
            $spreadsheet->removeSheetByIndex(0);
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Mart Sub-Categories Import');
            
            // Set headers with proper formatting
            $headers = [
                'A1' => 'title',
                'B1' => 'description', 
                'C1' => 'photo',
                'D1' => 'parent_category_id',
                'E1' => 'subcategory_order',
                'F1' => 'publish',
                'G1' => 'show_in_homepage',
                'H1' => 'review_attributes'
            ];

            // Set header values with bold formatting
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }

            // Add sample data rows - using actual parent categories from database
            $sampleData = [
                // Row 2
                'A2' => 'Sample Sub-Category 1',
                'B2' => 'Sample description for sub-category 1',
                'C2' => 'sample-media-slug',
                'D2' => 'Groceries',
                'E2' => '1',
                'F2' => 'TRUE',
                'G2' => 'FALSE',
                'H2' => 'quality,freshness',
                // Row 3
                'A3' => 'Sample Sub-Category 2',
                'B3' => 'Sample description for sub-category 2',
                'C3' => 'sample-media-slug',
                'D3' => 'Medicine',
                'E3' => '2',
                'F3' => 'TRUE',
                'G3' => 'FALSE',
                'H3' => 'quality,freshness'
            ];

            foreach ($sampleData as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Set column widths manually for better compatibility
            $sheet->getColumnDimension('A')->setWidth(20); // title
            $sheet->getColumnDimension('B')->setWidth(25); // description
            $sheet->getColumnDimension('C')->setWidth(20); // photo
            $sheet->getColumnDimension('D')->setWidth(25); // parent_category_id
            $sheet->getColumnDimension('E')->setWidth(15); // subcategory_order
            $sheet->getColumnDimension('F')->setWidth(10); // publish
            $sheet->getColumnDimension('G')->setWidth(15); // show_in_homepage
            $sheet->getColumnDimension('H')->setWidth(25); // review_attributes

            // Add borders to header row
            $sheet->getStyle('A1:H1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Create writer with proper options
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->setIncludeCharts(false);
            
            // Ensure directory exists
            $dir = dirname($filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Save the file
            $writer->save($filePath);
            
            // Verify file was created and has content
            if (!file_exists($filePath) || filesize($filePath) < 1000) {
                throw new \Exception('Generated file is too small or corrupted');
            }

        } catch (\Exception $e) {
            // Clean up any partial file
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            throw new \Exception('Failed to generate template: ' . $e->getMessage());
        }
    }
}
