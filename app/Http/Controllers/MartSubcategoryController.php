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
        
        foreach ($rows as $row) {
            $data = array_combine($headers, $row);
            if (empty($data['title']) || empty($data['parent_category_id'])) {
                continue; // Skip incomplete rows
            }
            
            // Get parent category info
            $parentCategory = $this->getParentCategoryInfo($data['parent_category_id'], $firestore);
            if (!$parentCategory) {
                continue; // Skip if parent category not found
            }
            
            // Create document with auto-generated ID
            $docRef = $collection->add([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'photo' => $data['photo'] ?? '',
                'parent_category_id' => $data['parent_category_id'],
                'parent_category_title' => $parentCategory['title'],
                'section' => $parentCategory['section'] ?? 'General',
                'section_order' => intval($parentCategory['section_order'] ?? 1),
                'category_order' => intval($parentCategory['category_order'] ?? 1),
                'subcategory_order' => intval($data['subcategory_order'] ?? 1),
                'publish' => strtolower($data['publish'] ?? '') === 'true',
                'show_in_homepage' => strtolower($data['show_in_homepage'] ?? '') === 'true',
                'review_attributes' => array_filter(array_map('trim', explode(',', $data['review_attributes'] ?? ''))),
                'migratedBy' => 'migrate:mart-subcategories',
            ]);
            
            // Set the internal 'id' field to match the Firestore document ID
            $docRef->set(['id' => $docRef->id()], ['merge' => true]);
            
            // Update parent category sub-category count
            $this->updateParentCategoryCount($data['parent_category_id'], $firestore);
            
            $imported++;
        }
        
        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }
        
        return back()->with('success', "Mart Sub-Categories imported successfully! ($imported rows)");
    }

    /**
     * Download import template for sub-categories
     */
    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/mart_subcategories_import_template.xlsx');
        
        if (!file_exists($filePath)) {
            abort(404, 'Template file not found');
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
}
