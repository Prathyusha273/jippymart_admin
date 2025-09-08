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
            
            // Create document with auto-generated ID - matching create form structure
            $docRef = $collection->add([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'photo' => $data['photo'] ?? '',
                'section' => $data['section'] ?? 'General',
                'category_order' => intval($data['category_order'] ?? 1),
                'section_order' => intval($data['category_order'] ?? 1), // Same as category_order
                'publish' => strtolower($data['publish'] ?? 'false') === 'true',
                'show_in_homepage' => strtolower($data['show_in_homepage'] ?? 'false') === 'true',
                'mart_id' => '', // Empty string like in create form
                'has_subcategories' => false,
                'subcategories_count' => 0,
                'review_attributes' => array_filter(array_map('trim', explode(',', $data['review_attributes'] ?? ''))),
                'migratedBy' => 'bulk_import',
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
            // Create new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            
            // Remove default worksheet and create a new one
            $spreadsheet->removeSheetByIndex(0);
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Mart Categories Import');
            
            // Set headers with proper formatting
            $headers = [
                'A1' => 'title',
                'B1' => 'description',
                'C1' => 'photo',
                'D1' => 'section',
                'E1' => 'category_order',
                'F1' => 'publish',
                'G1' => 'show_in_homepage',
                'H1' => 'review_attributes'
            ];

            // Set header values with bold formatting
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }

            // Add sample data rows
            $sampleData = [
                // Row 2
                'A2' => 'Sample Category 1',
                'B2' => 'This is a sample category description',
                'C2' => 'sample-media-slug',
                'D2' => 'Essentials & Daily Needs',
                'E2' => '1',
                'F2' => 'true',
                'G2' => 'true',
                'H2' => 'quality,value,service',
                // Row 3
                'A3' => 'Sample Category 2',
                'B3' => 'Another sample category description',
                'C3' => 'another-media-slug',
                'D3' => 'Health & Wellness',
                'E3' => '2',
                'F3' => 'false',
                'G3' => 'false',
                'H3' => 'freshness,organic'
            ];

            foreach ($sampleData as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Set column widths manually for better compatibility
            $sheet->getColumnDimension('A')->setWidth(20); // title
            $sheet->getColumnDimension('B')->setWidth(25); // description
            $sheet->getColumnDimension('C')->setWidth(20); // photo
            $sheet->getColumnDimension('D')->setWidth(25); // section
            $sheet->getColumnDimension('E')->setWidth(15); // category_order
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



