<?php
// process_bulk_prediction.php - Simplified for 3-Feature Model with Improved Excel Formatting

// Set timezone to Philippines (UTC+8)
date_default_timezone_set('Asia/Manila');

// Try to find vendor/autoload.php in different locations
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
         <p><strong>Please run the following commands:</strong></p>
         <pre style="background: #f5f5f5; padding: 15px; border-radius: 5px;">
cd C:\xampp\htdocs\gradetrack
composer install
mkdir public\downloads</pre>
         <p><strong>Current directory:</strong> ' . __DIR__ . '</p>');
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function processBulkPrediction($filePath, $fileExt, $model) {
    if (!$model) {
        return [
            'success' => false,
            'message' => 'Prediction model not available. Please ensure model.json exists.'
        ];
    }

    try {
        // Load the spreadsheet
        if ($fileExt === 'csv') {
            $reader = IOFactory::createReader('Csv');
        } else {
            $reader = IOFactory::createReader('Xlsx');
        }
        
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();

        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'The uploaded file is empty.'
            ];
        }

        // Get headers (first row)
        $headers = array_map('trim', $data[0]);
        
        // Required columns (only 3 features + optional program)
        $requiredColumns = [
            'Sex',
            'SHS GPA',
            'SHS Strand'
        ];
        
        // Optional column
        $hasProgram = in_array('Program', $headers);

        // Validate headers
        foreach ($requiredColumns as $required) {
            if (!in_array($required, $headers)) {
                return [
                    'success' => false,
                    'message' => "Missing required column: {$required}"
                ];
            }
        }

        // Get column indices
        $colIndices = [];
        foreach ($headers as $index => $header) {
            $colIndices[$header] = $index;
        }

        // Check row limit
        if (count($data) - 1 > 1000) {
            return [
                'success' => false,
                'message' => 'Maximum 1000 rows allowed. Your file has ' . (count($data) - 1) . ' rows.'
            ];
        }

        // Add new columns for results
        $headers[] = 'Prediction Result';
        $headers[] = 'Probability (%)';
        $headers[] = 'Prediction Date';
        
        $worksheet->fromArray([$headers], null, 'A1');
        
        // Apply uniform header styling to ALL columns
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
                'name' => 'Arial'
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0E2412']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        
        $lastCol = count($headers);
        $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);
        $worksheet->getStyle('A1:' . $lastColLetter . '1')->applyFromArray($headerStyle);
        
        // Set header row height
        $worksheet->getRowDimension(1)->setRowHeight(25);

        // Initialize report data
        $reportData = [
            'total_records' => count($data) - 1,
            'processed' => 0,
            'errors' => 0,
            'likely_graduate' => 0,
            'unlikely_graduate' => 0,
            'avg_probability' => 0,
            'by_program' => [],
            'by_strand' => [],
            'by_sex' => [],
            'predictions' => [],
            'has_program' => $hasProgram,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $probabilities = [];
        
        // Define uniform data row styling
        $dataStyle = [
            'font' => [
                'name' => 'Arial',
                'size' => 10
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDDDDD']
                ]
            ]
        ];
        
        // Process each row
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            
            try {
                // Extract data (only 3 features + optional program)
                $studentData = [
                    'student_name' => isset($colIndices['Student Name']) ? $row[$colIndices['Student Name']] : "Student " . $i,
                    'program' => $hasProgram && isset($colIndices['Program']) ? $row[$colIndices['Program']] : 'N/A',
                    'Sex' => trim($row[$colIndices['Sex']]),
                    'SHS_GPA' => floatval($row[$colIndices['SHS GPA']]),
                    'SHS_Strand' => trim($row[$colIndices['SHS Strand']])
                ];

                // Validate data
                if (empty($studentData['Sex']) ||
                    $studentData['SHS_GPA'] <= 0 ||
                    empty($studentData['SHS_Strand'])) {
                    throw new Exception('Invalid or missing data');
                }

                // Make prediction
                $prediction = makePrediction($studentData, $model);
                
                if ($prediction) {
                    // Add results to row
                    $resultText = $prediction['result'] ? 'Likely to Graduate' : 'Unlikely to Graduate';
                    $probability = round($prediction['probability'] * 100, 2);
                    
                    $rowIndex = $i + 1;
                    
                    // Write original row data back
                    foreach ($row as $colIdx => $cellValue) {
                        $worksheet->setCellValueByColumnAndRow($colIdx + 1, $rowIndex, $cellValue);
                    }
                    
                    // Add prediction results to the last 3 columns
                    $resultCol = count($headers) - 2;
                    $probCol = count($headers) - 1;
                    $dateCol = count($headers);
                    
                    $worksheet->setCellValueByColumnAndRow($resultCol, $rowIndex, $resultText);
                    $worksheet->setCellValueByColumnAndRow($probCol, $rowIndex, $probability);
                    
                    // Set datetime value properly for Excel
                    $dateTimeValue = date('Y-m-d H:i:s');
                    $worksheet->setCellValueByColumnAndRow($dateCol, $rowIndex, $dateTimeValue);
                    
                    // Format the date/time column to display correctly
                    $dateColLetter = Coordinate::stringFromColumnIndex($dateCol);
                    $worksheet->getStyle($dateColLetter . $rowIndex)
                             ->getNumberFormat()
                             ->setFormatCode('yyyy-mm-dd hh:mm:ss');
                    
                    // Apply uniform styling to the entire row
                    $worksheet->getStyle('A' . $rowIndex . ':' . $lastColLetter . $rowIndex)->applyFromArray($dataStyle);
                    
                    // Center align specific columns
                    $worksheet->getStyle(Coordinate::stringFromColumnIndex($colIndices['Sex'] + 1) . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle(Coordinate::stringFromColumnIndex($resultCol) . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle(Coordinate::stringFromColumnIndex($probCol) . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $worksheet->getStyle(Coordinate::stringFromColumnIndex($dateCol) . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Color code prediction results
                    if ($prediction['result']) {
                        // Green background for "Likely to Graduate"
                        $worksheet->getStyle(Coordinate::stringFromColumnIndex($resultCol) . $rowIndex)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('D4EDDA');
                        $worksheet->getStyle(Coordinate::stringFromColumnIndex($resultCol) . $rowIndex)->getFont()
                            ->getColor()->setRGB('155724');
                    } else {
                        // Red background for "Unlikely to Graduate"
                        $worksheet->getStyle(Coordinate::stringFromColumnIndex($resultCol) . $rowIndex)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8D7DA');
                        $worksheet->getStyle(Coordinate::stringFromColumnIndex($resultCol) . $rowIndex)->getFont()
                            ->getColor()->setRGB('721C24');
                    }
                    
                    // Update report statistics
                    $reportData['processed']++;
                    $probabilities[] = $probability;
                    
                    if ($prediction['result']) {
                        $reportData['likely_graduate']++;
                    } else {
                        $reportData['unlikely_graduate']++;
                    }

                    // Track by program (if available)
                    if ($hasProgram && !empty($studentData['program']) && $studentData['program'] !== 'N/A') {
                        $program = $studentData['program'];
                        if (!isset($reportData['by_program'][$program])) {
                            $reportData['by_program'][$program] = [
                                'total' => 0,
                                'likely' => 0,
                                'unlikely' => 0
                            ];
                        }
                        $reportData['by_program'][$program]['total']++;
                        if ($prediction['result']) {
                            $reportData['by_program'][$program]['likely']++;
                        } else {
                            $reportData['by_program'][$program]['unlikely']++;
                        }
                    }

                    // Track by strand
                    $strand = $studentData['SHS_Strand'];
                    if (!isset($reportData['by_strand'][$strand])) {
                        $reportData['by_strand'][$strand] = [
                            'total' => 0,
                            'likely' => 0,
                            'unlikely' => 0
                        ];
                    }
                    $reportData['by_strand'][$strand]['total']++;
                    if ($prediction['result']) {
                        $reportData['by_strand'][$strand]['likely']++;
                    } else {
                        $reportData['by_strand'][$strand]['unlikely']++;
                    }

                    // Track by sex
                    $sex = $studentData['Sex'];
                    if (!isset($reportData['by_sex'][$sex])) {
                        $reportData['by_sex'][$sex] = [
                            'total' => 0,
                            'likely' => 0,
                            'unlikely' => 0
                        ];
                    }
                    $reportData['by_sex'][$sex]['total']++;
                    if ($prediction['result']) {
                        $reportData['by_sex'][$sex]['likely']++;
                    } else {
                        $reportData['by_sex'][$sex]['unlikely']++;
                    }

                    // Store detailed prediction for report
                    $reportData['predictions'][] = [
                        'name' => $studentData['student_name'],
                        'program' => $studentData['program'],
                        'gpa' => $studentData['SHS_GPA'],
                        'sex' => $studentData['Sex'],
                        'strand' => $studentData['SHS_Strand'],
                        'result' => $resultText,
                        'probability' => $probability
                    ];
                    
                } else {
                    throw new Exception('Prediction failed');
                }
                
            } catch (Exception $e) {
                // Mark row as error with uniform styling
                $rowIndex = $i + 1;
                $resultCol = count($headers) - 2;
                $worksheet->setCellValueByColumnAndRow($resultCol, $rowIndex, 'ERROR: ' . $e->getMessage());
                
                // Apply error styling
                $worksheet->getStyle('A' . $rowIndex . ':' . $lastColLetter . $rowIndex)->applyFromArray($dataStyle);
                $worksheet->getStyle(Coordinate::stringFromColumnIndex($resultCol) . $rowIndex . ':' . $lastColLetter . $rowIndex)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFF3CD');
                
                $reportData['errors']++;
            }
        }

        // Auto-size all columns to fit content exactly
        foreach (range(1, $lastCol) as $colNum) {
            $colLetter = Coordinate::stringFromColumnIndex($colNum);
            $worksheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        
        // Calculate column widths and apply them
        $worksheet->calculateColumnWidths();
        
        // Adjust specific columns for better appearance
        foreach (range(1, $lastCol) as $colNum) {
            $colLetter = Coordinate::stringFromColumnIndex($colNum);
            $calculatedWidth = $worksheet->getColumnDimension($colLetter)->getWidth();
            
            // Add small padding to calculated width for better readability
            if ($calculatedWidth !== -1) {
                $worksheet->getColumnDimension($colLetter)->setWidth($calculatedWidth + 2);
            }
        }
        
        // Ensure minimum widths for specific columns
        if (isset($colIndices['Student Name'])) {
            $col = Coordinate::stringFromColumnIndex($colIndices['Student Name'] + 1);
            $currentWidth = $worksheet->getColumnDimension($col)->getWidth();
            if ($currentWidth < 20) {
                $worksheet->getColumnDimension($col)->setWidth(20);
            }
        }
        
        // Prediction Result column - ensure adequate width
        $predResultCol = Coordinate::stringFromColumnIndex($lastCol - 2);
        $currentWidth = $worksheet->getColumnDimension($predResultCol)->getWidth();
        if ($currentWidth < 22) {
            $worksheet->getColumnDimension($predResultCol)->setWidth(22);
        }
        
        // Probability column
        $probCol = Coordinate::stringFromColumnIndex($lastCol - 1);
        $worksheet->getColumnDimension($probCol)->setWidth(15);
        
        // Date column - ensure proper width for datetime display
        $dateCol = Coordinate::stringFromColumnIndex($lastCol);
        $currentWidth = $worksheet->getColumnDimension($dateCol)->getWidth();
        if ($currentWidth < 20) {
            $worksheet->getColumnDimension($dateCol)->setWidth(20);
        }

        // Calculate average probability
        if (count($probabilities) > 0) {
            $reportData['avg_probability'] = round(array_sum($probabilities) / count($probabilities), 2);
        }

        // Calculate graduation rate
        $reportData['graduation_rate'] = $reportData['processed'] > 0 
            ? round(($reportData['likely_graduate'] / $reportData['processed']) * 100, 2)
            : 0;

        // Save the output file
        $outputDir = __DIR__ . '/downloads/';
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $timestamp = date('Y-m-d_His');
        $outputFile = $outputDir . 'predictions_' . $timestamp . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFile);

        $message = "Successfully processed {$reportData['processed']} predictions.";
        if ($reportData['errors'] > 0) {
            $message .= " {$reportData['errors']} rows had errors.";
        }

        return [
            'success' => true,
            'message' => $message,
            'download_file' => 'downloads/predictions_' . $timestamp . '.xlsx',
            'processed' => $reportData['processed'],
            'errors' => $reportData['errors'],
            'report_data' => $reportData
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error processing file: ' . $e->getMessage()
        ];
    }
}

function makePrediction($data, $model) {
    // Preprocess inputs like in functions.php
    $shs_gpa = floatval($data['SHS_GPA']);
    
    // Sex: Male=1, Female=0
    $sex = ($data['Sex'] === 'Male' || $data['Sex'] === 1) ? 1 : 0;
    
    // Strand_Favorable: STEM or TVL-ICT = 1, others = 0
    $strand_favorable = in_array($data['SHS_Strand'], ['STEM', 'TVL-ICT']) ? 1 : 0;
    
    // Get coefficients
    $coef = $model['coefficients'];
    $intercept = floatval($model['intercept']);
    
    // Calculate linear combination (z)
    $z = $intercept;
    $z += $coef['SHS_GPA'] * $shs_gpa;
    $z += $coef['Sex'] * $sex;
    $z += $coef['Strand_Favorable'] * $strand_favorable;
    
    // Apply sigmoid function
    $probability = 1.0 / (1.0 + exp(-$z));
    $result = ($probability >= 0.5) ? 1 : 0;
    
    return [
        'result' => $result,
        'probability' => $probability
    ];
}
?>