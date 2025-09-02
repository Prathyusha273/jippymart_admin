<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Google\Cloud\Firestore\FirestoreClient;

/**
 * MartItemController
 * 
 * Handles CRUD operations for mart items.
 * 
 * Default Fields for New Items:
 * - reviewCount: "0" (string) - Number of reviews
 * - reviewSum: "0" (string) - Sum of review ratings
 * - These fields are automatically set to "0" for all new items
 */
class MartItemController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index($id='')
    {
        return view("martItems.index")->with('id',$id);
    }

    public function edit($id)
    {
        return view('martItems.edit')->with('id',$id);
    }

    public function create($id='')
    {
        return view('martItems.create')->with('id',$id);
    }
    
    public function createItem()
    {
        return view('martItems.create');
    }

    /**
     * Find vendor ID by name (for mart vendors only)
     */
    private function findVendorByName($vendorName, $firestore)
    {
        try {
            $vendors = $firestore->collection('vendors')
                ->where('title', '==', trim($vendorName))
                ->where('vType', '==', 'mart')
                ->limit(1)
                ->documents();

            foreach ($vendors as $vendor) {
                return $vendor->id();
            }
        } catch (\Exception $e) {
            // Log error if needed
        }
        return null;
    }

    /**
     * Find category ID by name
     */
    private function findCategoryByName($categoryName, $firestore)
    {
        try {
            $categories = $firestore->collection('mart_categories')
                ->where('title', '==', trim($categoryName))
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
     * Resolve vendor ID - try direct ID first, then name lookup (for mart vendors only)
     */
    private function resolveVendorID($vendorInput, $firestore)
    {
        // First try as direct ID
        try {
            $vendorDoc = $firestore->collection('vendors')->document($vendorInput)->snapshot();
            if ($vendorDoc->exists()) {
                $vendorData = $vendorDoc->data();
                // Verify it's a mart vendor
                if (isset($vendorData['vType']) && $vendorData['vType'] === 'mart') {
                    return $vendorInput; // Return the ID as-is
                }
            }
        } catch (\Exception $e) {
            // Continue to name lookup
        }

        // If not found as ID, try name lookup
        return $this->findVendorByName($vendorInput, $firestore);
    }

    /**
     * Resolve category ID - try direct ID first, then name lookup
     */
    private function resolveCategoryID($categoryInput, $firestore)
    {
        // First try as direct ID
        try {
            $categoryDoc = $firestore->collection('mart_categories')->document($categoryInput)->snapshot();
            if ($categoryDoc->exists()) {
                return $categoryInput; // Return the ID as-is
            }
        } catch (\Exception $e) {
            // Continue to name lookup
        }

        // If not found as ID, try name lookup
        return $this->findCategoryByName($categoryInput, $firestore);
    }

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

        $collection = $firestore->collection('mart_items');
        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
            $data = array_combine($headers, $row);

            // Skip empty rows
            if (empty($data['name'])) {
                continue;
            }

            try {
                // Validate required fields
                if (empty($data['name']) || empty($data['price']) || empty($data['vendorID']) || empty($data['categoryID'])) {
                    $errors[] = "Row $rowNumber: Missing required fields (name, price, vendorID, categoryID)";
                    continue;
                }

                // Resolve vendor ID (supports both ID and name)
                $resolvedVendorID = $this->resolveVendorID($data['vendorID'], $firestore);
                if (!$resolvedVendorID) {
                    $errors[] = "Row $rowNumber: Vendor '{$data['vendorID']}' not found (neither as ID nor name) or is not a mart vendor";
                    continue;
                }

                // Resolve category ID (supports both ID and name)
                $resolvedCategoryID = $this->resolveCategoryID($data['categoryID'], $firestore);
                if (!$resolvedCategoryID) {
                    $errors[] = "Row $rowNumber: Category '{$data['categoryID']}' not found (neither as ID nor name)";
                    continue;
                }

                // Prepare mart item data
                $itemData = [
                    'name' => trim($data['name']),
                    'price' => (float) $data['price'],
                    'description' => trim($data['description'] ?? ''),
                    'vendorID' => $resolvedVendorID,
                    'categoryID' => $resolvedCategoryID,
                    'disPrice' => !empty($data['disPrice']) ? (float) $data['disPrice'] : '',
                    'publish' => strtolower($data['publish'] ?? 'true') === 'true',
                    'nonveg' => strtolower($data['nonveg'] ?? 'false') === 'true',
                    'veg' => strtolower($data['nonveg'] ?? 'false') === 'true' ? false : true, // Opposite of nonveg
                    'isAvailable' => strtolower($data['isAvailable'] ?? 'true') === 'true',
                    'quantity' => -1, // Auto-generated default
                    'calories' => 0, // Auto-generated default
                    'grams' => 0, // Auto-generated default
                    'proteins' => 0, // Auto-generated default
                    'fats' => 0, // Auto-generated default
                    'photo' => '',
                    'photos' => [],
                    'addOnsTitle' => [], // Fixed spelling to match Firestore
                    'addOnsPrice' => [], // Fixed spelling to match Firestore
                    'sizeTitle' => [],
                    'sizePrice' => [],
                    'attributes' => [],
                    'variants' => [],
                    'product_specification' => null,
                    'item_attribute' => null,
                    'reviewAttributes' => null,
                    // Review fields - automatically set to "0" for new items
                    'reviewCount' => '0', // Default review count as string
                    'reviewSum' => '0', // Default review sum as string
                    'takeawayOption' => false, // Auto-generated default
                    'migratedBy' => 'excel_import',
                    'createdAt' => new \Google\Cloud\Core\Timestamp(new \DateTime()), // Fixed to match Firestore
                    'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                ];

                // Create document with auto-generated ID
                $docRef = $collection->add($itemData);

                // Set the internal 'id' field to match the Firestore document ID
                $docRef->set(['id' => $docRef->id()], ['merge' => true]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }

        $message = "Mart items imported successfully! ($imported rows)";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', $errors);
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/mart_items_import_template.xlsx');

        if (!file_exists($filePath)) {
            abort(404, 'Template file not found');
        }

        return response()->download($filePath, 'mart_items_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="mart_items_import_template.xlsx"'
        ]);
    }

    /**
     * Inline update for mart item prices - ensures data consistency
     */
    public function inlineUpdate(Request $request, $id)
    {
        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $collection = $firestore->collection('mart_items');
            $document = $collection->document($id);
            $snapshot = $document->snapshot();

            if (!$snapshot->exists()) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            $currentData = $snapshot->data();
            $field = $request->input('field');
            $value = $request->input('value');

            // Validate field
            if (!in_array($field, ['price', 'disPrice'])) {
                return response()->json(['success' => false, 'message' => 'Invalid field'], 400);
            }

            // Validate value
            if (!is_numeric($value) || $value < 0) {
                return response()->json(['success' => false, 'message' => 'Invalid price value'], 400);
            }

            // Prepare update data with proper data types (matching edit page)
            $updateData = [];

            if ($field === 'price') {
                $updateData[] = ['path' => 'price', 'value' => (string) $value]; // Convert to string like edit page

                // If discount price is higher than new price, reset it
                if (isset($currentData['disPrice']) && !empty($currentData['disPrice']) && (float)$currentData['disPrice'] > (float)$value) {
                    $updateData[] = ['path' => 'disPrice', 'value' => ''];
                }
            } elseif ($field === 'disPrice') {
                // If setting discount price to 0 or empty, remove it
                if ($value == 0 || empty($value)) {
                    $updateData[] = ['path' => 'disPrice', 'value' => ''];
                } else {
                    $updateData[] = ['path' => 'disPrice', 'value' => (string) $value]; // Convert to string like edit page

                    // Validate discount price is not higher than original price
                    if ((float)$value > (float)$currentData['price']) {
                        return response()->json(['success' => false, 'message' => 'Discount price cannot be higher than original price'], 400);
                    }
                }
            }

            // Update the document with proper Firestore format
            $document->update($updateData);

            // Prepare response message
            $message = 'Price updated successfully';
            $hasDiscountReset = false;

            // Check if discount was reset
            foreach ($updateData as $update) {
                if ($update['path'] === 'disPrice' && $update['value'] === '') {
                    $hasDiscountReset = true;
                    break;
                }
            }

            if ($field === 'price' && $hasDiscountReset) {
                $message .= ' (discount price was reset as it was higher than the new price)';
            }

            // Convert updateData back to associative array for response
            $responseData = [];
            foreach ($updateData as $update) {
                $responseData[$update['path']] = $update['value'];
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

}


