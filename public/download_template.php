<?php
// download_template.php - Generate template for 3-feature model

$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php'
];

$autoloadFound = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    die('<h2>Error: Composer dependencies not installed</h2>
         <p><strong>Please run:</strong> composer install</p>');
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$format = isset($_GET['format']) ? $_GET['format'] : 'xlsx';

// Create new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headers (3 features + optional student name and program)
$headers = [
    'A' => 'Student Name',
    'B' => 'Program',
    'C' => 'Sex',
    'D' => 'SHS GPA',
    'E' => 'SHS Strand'
];

// Set header row
foreach ($headers as $col => $header) {
    $sheet->setCellValue($col . '1', $header);
}

// Style header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 12
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '0E2412']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];

$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

// Add sample data rows
$sampleData = [
    [
        'Juan Dela Cruz',
        'BSCS',
        'Male',
        '92.5',
        'STEM'
    ],
    [
        'Maria Santos',
        'BSIT',
        'Female',
        '88.3',
        'ABM'
    ],
    [
        'Jose Rizal',
        'BSCpE',
        'Male',
        '95.0',
        'TVL-ICT'
    ],
    [
        'Ana Reyes',
        'BSIS',
        'Female',
        '85.7',
        'HUMSS'
    ],
    [
        'Pedro Garcia',
        'BSECE',
        'Female',
        '88',
        'GAS'
    ]
];

// Add sample rows
$row = 2;
foreach ($sampleData as $data) {
    $col = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }
    $row++;
}

// Style data rows
$dataStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'CCCCCC']
        ]
    ]
];

$sheet->getStyle('A2:E' . ($row - 1))->applyFromArray($dataStyle);

// Auto-size columns
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set row height for header
$sheet->getRowDimension('1')->setRowHeight(25);

// Add instructions sheet
$instructionsSheet = $spreadsheet->createSheet();
$instructionsSheet->setTitle('Instructions');

$instructions = [
    ['BULK PREDICTION TEMPLATE - INSTRUCTIONS (Simplified Model)'],
    [''],
    ['REQUIRED COLUMNS (must be present in this exact order):'],
    ['1. Student Name - Optional, any text (for identification only)'],
    ['2. Program - Optional (e.g., BSCS, BSIT, BSIS, BSCpE, BSECE) - for reporting purposes only'],
    ['3. Sex - Either: Male or Female (REQUIRED)'],
    ['4. SHS GPA - Number between 0-100 (e.g., 92.5) (REQUIRED)'],
    ['5. SHS Strand - One of: STEM, TVL-ICT, ABM, HUMSS, GAS, TVL-IA, TVL-HE, TVL-AFA (REQUIRED)'],
    [''],
    ['IMPORTANT NOTES:'],
    ['• Only 3 columns are REQUIRED for prediction: Sex, SHS GPA, SHS Strand'],
    ['• Student Name and Program are OPTIONAL - used only for identification and reporting'],
    ['• Column names must match EXACTLY (case-sensitive)'],
    ['• Sex: Use "Male" or "Female" only'],
    ['• SHS GPA: Decimal numbers are allowed (e.g., 92.5, 88.3)'],
    ['• SHS Strand: Must be one of the valid strand codes listed above'],
    ['• No empty fields for Sex, SHS GPA, or SHS Strand'],
    ['• Maximum 1000 rows per upload'],
    ['• Delete sample rows before uploading your actual data'],
    [''],
    ['MODEL INFORMATION:'],
    ['This prediction uses only 3 key factors:'],
    ['1. Sex (Male/Female)'],
    ['2. SHS GPA (Senior High School Grade Point Average)'],
    ['3. SHS Strand (with STEM and TVL-ICT being favorable strands)'],
    [''],
    ['NOTE: Program column is optional and does NOT affect predictions.'],
    ['It is included only for organizing and reporting purposes.'],
    [''],
    ['FAVORABLE STRANDS:'],
    ['Students from STEM and TVL-ICT strands typically have higher'],
    ['graduation probabilities for CCS programs.'],
    [''],
    ['SAMPLE FORMAT:'],
    ['See the "Sheet1" tab for sample data format'],
    [''],
    ['After processing, you will receive:'],
    ['• All your input data'],
    ['• Prediction Result (Likely to Graduate / Unlikely to Graduate)'],
    ['• Probability Score (0-100%)'],
    ['• Prediction timestamp'],
    ['• Breakdown by Program (if Program column is provided)'],
    [''],
    ['VALID STRAND VALUES:'],
    ['STEM - Science, Technology, Engineering, and Mathematics'],
    ['TVL-ICT - Technical-Vocational-Livelihood (ICT Track)'],
    ['ABM - Accountancy, Business, and Management'],
    ['HUMSS - Humanities and Social Sciences'],
    ['GAS - General Academic Strand'],
    ['TVL-IA - TVL Industrial Arts'],
    ['TVL-HE - TVL Home Economics'],
    ['TVL-AFA - TVL Agri-Fishery Arts']
];

$instructionsSheet->fromArray($instructions, null, 'A1');

// Style instructions
$instructionsSheet->getStyle('A1')->applyFromArray([
    'font' => [
        'bold' => true,
        'size' => 14,
        'color' => ['rgb' => '0E2412']
    ]
]);

$instructionsSheet->getStyle('A3')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12]
]);

$instructionsSheet->getStyle('A9')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12]
]);

$instructionsSheet->getStyle('A19')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12]
]);

$instructionsSheet->getStyle('A24')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '059669']]
]);

$instructionsSheet->getStyle('A29')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12]
]);

$instructionsSheet->getStyle('A33')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12]
]);

$instructionsSheet->getStyle('A40')->applyFromArray([
    'font' => ['bold' => true, 'size' => 12]
]);

// Auto-size column A in instructions
$instructionsSheet->getColumnDimension('A')->setWidth(80);

// Set active sheet back to first sheet
$spreadsheet->setActiveSheetIndex(0);

// Generate file based on format
if ($format === 'csv') {
    // For CSV, only export the main sheet
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="gradetrack_template.csv"');
    header('Cache-Control: max-age=0');
    
    $writer = new Csv($spreadsheet);
    $writer->save('php://output');
} else {
    // For Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="gradetrack_template.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

exit;
?>