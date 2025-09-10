<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Google\Cloud\Firestore\FirestoreClient;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("brands.index");
    }

    public function edit($id)
    {
        return view('brands.edit')->with('id', $id);
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $collection = $firestore->collection('brands');

            // Generate slug if not provided
            $slug = $request->slug;
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->name)));
            }

            // Handle logo upload
            $logoUrl = '';
            if ($request->hasFile('logo')) {
                $logoUrl = $this->uploadLogo($request->file('logo'));
            }

            $brandData = [
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description ?? '',
                'status' => (bool) $request->status,
                'logo_url' => $logoUrl,
                'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
            ];

            // Create document with auto-generated ID
            $docRef = $collection->add($brandData);

            // Set the internal 'id' field to match the Firestore document ID
            $docRef->set(['id' => $docRef->id()], ['merge' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $document = $firestore->collection('brands')->document($id);
            $snapshot = $document->snapshot();

            if (!$snapshot->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found'
                ], 404);
            }

            $currentData = $snapshot->data();

            // Generate slug if not provided
            $slug = $request->slug;
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->name)));
            }

            // Handle logo upload
            $logoUrl = $currentData['logo_url'] ?? '';
            if ($request->hasFile('logo')) {
                $logoUrl = $this->uploadLogo($request->file('logo'));
            }

            $updateData = [
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description ?? '',
                'status' => (bool) $request->status,
                'logo_url' => $logoUrl,
                'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
            ];

            $document->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            // Check if brand is being used by any items
            $itemsQuery = $firestore->collection('mart_items')
                ->where('brand_id', '==', $id)
                ->limit(1);
            
            $items = $itemsQuery->documents();
            if (!$items->isEmpty()) {
                return redirect()->route('brands')->with('error', 'Cannot delete brand. It is being used by one or more items.');
            }

            // Delete the brand document
            $firestore->collection('brands')->document($id)->delete();

            return redirect()->route('brands')->with('success', 'Brand deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('brands')->with('error', 'Error deleting brand: ' . $e->getMessage());
        }
    }

    public function getData(Request $request)
    {
        try {
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            $collection = $firestore->collection('brands');
            $query = $collection->orderBy('created_at', 'DESC');

            // Apply search filter
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                // Note: Firestore doesn't support full-text search, so we'll filter in PHP
            }

            $documents = $query->documents();
            $data = [];

            foreach ($documents as $document) {
                if ($document->exists()) {
                    $docData = $document->data();
                    
                    // Apply search filter in PHP (since Firestore doesn't support full-text search)
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $searchValue = strtolower($request->search['value']);
                        $searchableFields = [
                            strtolower($docData['name'] ?? ''),
                            strtolower($docData['slug'] ?? ''),
                            strtolower($docData['description'] ?? '')
                        ];
                        
                        if (!str_contains(implode(' ', $searchableFields), $searchValue)) {
                            continue;
                        }
                    }

                    $data[] = [
                        'id' => $document->id(),
                        'name' => $docData['name'] ?? '',
                        'slug' => $docData['slug'] ?? '',
                        'logo_url' => $docData['logo_url'] ?? '',
                        'description' => $docData['description'] ?? '',
                        'status' => $docData['status'] ?? false,
                        'created_at' => isset($docData['created_at']) ? $docData['created_at']->format('Y-m-d H:i:s') : '',
                    ];
                }
            }

            // Apply pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $totalRecords = count($data);
            $filteredData = array_slice($data, $start, $length);

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $filteredData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching data: ' . $e->getMessage()
            ]);
        }
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

        $collection = $firestore->collection('brands');
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
                if (empty($data['name'])) {
                    $errors[] = "Row $rowNumber: Missing required field (name)";
                    continue;
                }

                // Generate slug if not provided
                $slug = $data['slug'] ?? '';
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
                }

                // Prepare brand data
                $brandData = [
                    'name' => trim($data['name']),
                    'slug' => $slug,
                    'description' => trim($data['description'] ?? ''),
                    'status' => strtolower($data['status'] ?? 'true') === 'true',
                    'logo_url' => trim($data['logo_url'] ?? ''),
                    'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                    'updated_at' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                ];

                // Create document with auto-generated ID
                $docRef = $collection->add($brandData);

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

        $message = "Brands imported successfully! ($imported rows)";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', $errors);
        }

        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/brands_import_template.xlsx');
        $templateDir = dirname($filePath);
        
        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }

        return response()->download($filePath, 'brands_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="brands_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for brand import
     */
    private function generateTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = [
                'A1' => 'name',
                'B1' => 'slug', 
                'C1' => 'description',
                'D1' => 'status',
                'E1' => 'logo_url'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Add sample data
            $sampleData = [
                'A2' => 'Nike',
                'B2' => 'nike',
                'C2' => 'Sportswear and footwear brand',
                'D2' => 'true',
                'E2' => 'https://example.com/nike-logo.png'
            ];
            
            foreach ($sampleData as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Auto-size columns
            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Upload logo to storage
     */
    private function uploadLogo($file)
    {
        try {
            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store file in public storage
            $path = $file->storeAs('brands/logos', $filename, 'public');
            
            // Return full URL
            return asset('storage/' . $path);
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload logo: ' . $e->getMessage());
        }
    }
}
