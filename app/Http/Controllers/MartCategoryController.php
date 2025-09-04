<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Storage;

class MartCategoryController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view("martCategories.index");
    }

    public function edit($id)
    {
        return view('martCategories.edit')->with('id', $id);
    }

    public function create()
    {
        return view('martCategories.create');
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
        
        $collection = $firestore->collection('mart_categories');
        $imported = 0;
        foreach ($rows as $row) {
            $data = array_combine($headers, $row);
            if (empty($data['title']) || empty($data['photo'])) {
                continue; // Skip incomplete rows
            }
            
            // Create document with auto-generated ID
            $docRef = $collection->add([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'photo' => $data['photo'],
                'publish' => strtolower($data['publish'] ?? '') === 'true',
                'show_in_homepage' => strtolower($data['show_in_homepage'] ?? '') === 'true',
                'mart_id' => $data['mart_id'] ?? '',
                'section' => $data['section'] ?? 'General',
                'section_order' => intval($data['section_order'] ?? 1),
                'category_order' => intval($data['category_order'] ?? 1),
                'has_subcategories' => false,
                'subcategories_count' => 0,
                'review_attributes' => array_filter(array_map('trim', explode(',', $data['review_attributes'] ?? ''))),
                'migratedBy' => 'migrate:mart-categories',
            ]);
            
            // Set the internal 'id' field to match the Firestore document ID
            $docRef->set(['id' => $docRef->id()], ['merge' => true]);
            
            $imported++;
        }
        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }
        return back()->with('success', "Mart Categories imported successfully! ($imported rows)");
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/mart_categories_import_template.xlsx');

        // Create template directory if it doesn't exist
        $templateDir = dirname($filePath);
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }
        
        return response()->download($filePath, 'mart_categories_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="mart_categories_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for mart categories import
     */
    private function generateTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => 'title',
                'B1' => 'description',
                'C1' => 'section',
                'D1' => 'category_order',
                'E1' => 'publish',
                'F1' => 'show_in_homepage'
            ];

            // Set header values
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Add sample data row
            $sampleData = [
                'A2' => 'Sample Category',
                'B2' => 'This is a sample category description',
                'C2' => 'Essentials & Daily Needs',
                'D2' => '1',
                'E2' => 'true',
                'F2' => 'true'
            ];

            foreach ($sampleData as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Auto-size columns
            foreach (range('A', 'F') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

        } catch (\Exception $e) {
            throw new \Exception('Failed to generate template: ' . $e->getMessage());
        }
    }
}



