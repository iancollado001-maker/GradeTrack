<?php
// generate_records_report.php - HTML Report Generator for Browser Printing
date_default_timezone_set('Asia/Manila');
require_once 'config.php';

// Get filters from URL parameters
$filters = [
    'strand' => isset($_GET['strand']) ? $_GET['strand'] : 'all',
    'program' => isset($_GET['program']) ? $_GET['program'] : 'all',
    'result' => isset($_GET['result']) ? $_GET['result'] : 'all',
    'sex' => isset($_GET['sex']) ? $_GET['sex'] : 'all',
    'student_name' => isset($_GET['student_name']) ? $_GET['student_name'] : '',
    'min_gpa' => isset($_GET['min_gpa']) ? $_GET['min_gpa'] : '',
    'max_gpa' => isset($_GET['max_gpa']) ? $_GET['max_gpa'] : '',
    'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
    'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : '',
    'name_sort' => isset($_GET['name_sort']) && in_array($_GET['name_sort'], ['asc', 'desc']) ? $_GET['name_sort'] : ''
];

// Fetch all records with filters
$records = getAllPredictions(10000, $filters);

// Apply name sort if requested
if ($filters['name_sort'] === 'asc') {
    usort($records, fn($a, $b) => strcasecmp($a['student_name'] ?? '', $b['student_name'] ?? ''));
} elseif ($filters['name_sort'] === 'desc') {
    usort($records, fn($a, $b) => strcasecmp($b['student_name'] ?? '', $a['student_name'] ?? ''));
}

// Calculate statistics
$totalRecords = count($records);
$likelyGraduate = 0;
$unlikelyGraduate = 0;
$totalProbability = 0;
$byProgram = [];
$byStrand = [];
$bySex = [];
$hasProgram = false;

foreach ($records as $record) {
    if ($record['prediction_result'] == 1) {
        $likelyGraduate++;
    } else {
        $unlikelyGraduate++;
    }
    
    $totalProbability += $record['probability'];
    
    if (!empty($record['program']) && $record['program'] !== 'N/A') {
        $hasProgram = true;
        $program = $record['program'];
        if (!isset($byProgram[$program])) {
            $byProgram[$program] = ['total' => 0, 'likely' => 0, 'unlikely' => 0];
        }
        $byProgram[$program]['total']++;
        if ($record['prediction_result'] == 1) {
            $byProgram[$program]['likely']++;
        } else {
            $byProgram[$program]['unlikely']++;
        }
    }
    
    $strand = $record['shs_strand'];
    if (!isset($byStrand[$strand])) {
        $byStrand[$strand] = ['total' => 0, 'likely' => 0, 'unlikely' => 0];
    }
    $byStrand[$strand]['total']++;
    if ($record['prediction_result'] == 1) {
        $byStrand[$strand]['likely']++;
    } else {
        $byStrand[$strand]['unlikely']++;
    }
    
    $sex = $record['sex'];
    if (!isset($bySex[$sex])) {
        $bySex[$sex] = ['total' => 0, 'likely' => 0, 'unlikely' => 0];
    }
    $bySex[$sex]['total']++;
    if ($record['prediction_result'] == 1) {
        $bySex[$sex]['likely']++;
    } else {
        $bySex[$sex]['unlikely']++;
    }
}

$avgProbability = $totalRecords > 0 ? round($totalProbability / $totalRecords, 1) : 0;
$graduationRate = $totalRecords > 0 ? round(($likelyGraduate / $totalRecords) * 100, 1) : 0;

function hasActiveFilters($filters) {
    return ($filters['strand'] !== 'all' ||
            $filters['program'] !== 'all' ||
            $filters['result'] !== 'all' ||
            $filters['sex'] !== 'all' ||
            !empty($filters['student_name']) ||
            !empty($filters['min_gpa']) ||
            !empty($filters['max_gpa']) ||
            !empty($filters['date_from']) ||
            !empty($filters['date_to']));
}

$isFiltered = hasActiveFilters($filters);

// Sort records by probability (descending) for top predictions section only
$sortedByProbability = $records;
usort($sortedByProbability, fn($a, $b) => $b['probability'] - $a['probability']);

// $records is already sorted by name (if name_sort is set) — use it for the full table
$predictions = array_map(function($pred) {
    return [
        'name' => $pred['student_name'] ?? 'Anonymous',
        'program' => $pred['program'] ?? 'N/A',
        'sex' => $pred['sex'],
        'gpa' => $pred['shs_gpa'],
        'strand' => $pred['shs_strand'],
        'result' => $pred['prediction_result'] == 1 ? 'Likely to Graduate' : 'Unlikely to Graduate',
        'probability' => number_format($pred['probability'], 1)
    ];
}, $records);

$reportData = [
    'timestamp' => date('F d, Y \a\t h:i A'),
    'total_records' => $totalRecords,
    'likely_graduate' => $likelyGraduate,
    'unlikely_graduate' => $unlikelyGraduate,
    'graduation_rate' => $graduationRate,
    'avg_probability' => $avgProbability,
    'processed' => $totalRecords,
    'errors' => 0,
    'by_program' => $byProgram,
    'by_strand' => $byStrand,
    'by_sex' => $bySex,
    'has_program' => $hasProgram,
    'predictions' => $predictions,
    'name_sort' => $filters['name_sort']
];

// Human-readable sort label for the report
$nameSortLabel = '';
if ($filters['name_sort'] === 'asc') {
    $nameSortLabel = 'Sorted by Name: A → Z';
} elseif ($filters['name_sort'] === 'desc') {
    $nameSortLabel = 'Sorted by Name: Z → A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records Report - Gradetrack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f8faf9;
            padding: 20px;
        }

        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-print {
            background: linear-gradient(135deg, #0e2412, #2d5a3a);
            color: white;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .btn-back {
            background: white;
            color: #1f2937;
            border: 2px solid #e5e7eb;
        }

        .btn-back:hover {
            border-color: #0e2412;
            color: #0e2412;
        }

        .print-report {
            max-width: 210mm;
            margin: 20px auto 0;
            background: white;
            padding: 40px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 4px solid #0e2412;
            padding-bottom: 20px;
        }

        .report-header h1 {
            color: #0e2412;
            font-size: 28px;
            margin: 0 0 10px 0;
            font-weight: 900;
        }

        .report-header .subtitle {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }

        .report-header .timestamp {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }

        /* Name sort indicator badge */
        .sort-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            background: #f0fdf4;
            border: 1px solid #10b981;
            color: #065f46;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
        }

        .sort-indicator i {
            font-size: 11px;
        }

        .summary-grid {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: separate;
            border-spacing: 15px 0;
        }

        .summary-box {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .summary-box h3 {
            color: #6b7280;
            font-size: 12px;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-box .value {
            color: #0e2412;
            font-size: 32px;
            font-weight: 900;
            margin: 0;
        }

        .summary-box .label {
            color: #999;
            font-size: 11px;
            margin-top: 5px;
        }

        .summary-box.success {
            background: #f0fdf4;
            border-color: #10b981;
        }

        .summary-box.success .value { color: #059669; }

        .summary-box.warning {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .summary-box.warning .value { color: #d97706; }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section h2 {
            color: #0e2412;
            font-size: 18px;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 700;
        }

        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .breakdown-table th,
        .breakdown-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .breakdown-table th {
            background: #f9fafb;
            color: #374151;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
        }

        .breakdown-table td {
            color: #4b5563;
            font-size: 13px;
        }

        .breakdown-table tr:hover { background: #f9fafb; }

        .progress-bar {
            background: #e5e7eb;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }

        .progress-fill.low { background: linear-gradient(90deg, #ef4444, #dc2626); }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-favorable { background: #dcfce7; color: #166534; }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #999;
            font-size: 11px;
        }

        .page-break { page-break-after: always; }

        .top-predictions { margin-top: 15px; }

        .prediction-item {
            padding: 10px;
            border-left: 4px solid #10b981;
            background: #f9fafb;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .prediction-item.unlikely { border-left-color: #ef4444; }

        .prediction-item h4 {
            margin: 0 0 5px 0;
            color: #0e2412;
            font-size: 14px;
        }

        .prediction-item p {
            margin: 0;
            color: #6b7280;
            font-size: 12px;
        }

        .model-info {
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }

        .model-info h3 { color: #065f46; margin: 0 0 10px 0; font-size: 16px; }
        .model-info ul { margin: 0; padding-left: 20px; color: #166534; }
        .model-info li { margin: 5px 0; }

        .records-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }

        .records-table th,
        .records-table td {
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #e5e7eb;
        }

        .records-table th {
            background: #0e2412;
            color: white;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
        }

        /* Sort indicator on the Name column header in the table */
        .records-table th.sorted {
            background: #1a3d20;
        }

        .records-table th.sorted .sort-arrow {
            display: inline-block;
            margin-left: 4px;
            font-size: 9px;
            opacity: 0.9;
        }

        .records-table td { color: #4b5563; font-size: 11px; }
        .records-table tr:nth-child(even) { background: #f9fafb; }
        .records-table tr:hover { background: #f3f4f6; }

        .records-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0e2412;
        }

        .records-header h1 {
            color: #0e2412;
            font-size: 24px;
            margin: 0 0 8px 0;
            font-weight: 900;
        }

        .records-header p { color: #666; font-size: 13px; margin: 0; }

        @media print {
            body { background: white; padding: 0; }

            .print-controls { display: none !important; }

            .print-report {
                max-width: 100%;
                margin: 0;
                padding: 20px;
                box-shadow: none;
                border-radius: 0;
            }

            .page-break { page-break-after: always; }
            .section { page-break-inside: avoid; }

            @page {
                size: A4 portrait;
                margin: 15mm;
            }
        }

        @media (max-width: 768px) {
            .print-report { padding: 20px; margin: 20px 10px 20px; }

            .print-controls {
                position: static;
                justify-content: center;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <a href="records.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Records
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i>
            Print Report
        </button>
    </div>

    <div class="print-report">
        <!-- Report Header -->
        <div class="report-header">
            <h1>Student Graduation Prediction Report</h1>
            <div class="subtitle">Gradetrack Student Graduation Prediction System</div>
            <div class="subtitle">Simplified 3-Feature Model</div>
            <div class="timestamp">Generated: <?php echo htmlspecialchars($reportData['timestamp']); ?></div>
            <?php if ($nameSortLabel): ?>
            <div>
                <span class="sort-indicator">
                    <i class="fas <?php echo $filters['name_sort'] === 'asc' ? 'fa-sort-alpha-down' : 'fa-sort-alpha-up'; ?>"></i>
                    <?php echo $nameSortLabel; ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Model Information -->
        <div class="model-info">
            <h3>Model Information</h3>
            <p style="margin: 0 0 10px 0; color: #166534;">This prediction uses a simplified 3-feature machine learning model:</p>
            <ul>
                <li><strong>Sex</strong> - Male or Female</li>
                <li><strong>SHS GPA</strong> - Senior High School Grade Point Average</li>
                <li><strong>SHS Strand</strong> - Favorable strands: STEM, TVL-ICT</li>
            </ul>
        </div>

        <!-- Summary Statistics -->
        <table class="summary-grid">
            <tr>
                <td class="summary-box">
                    <h3>Total Records</h3>
                    <div class="value"><?php echo number_format($reportData['total_records']); ?></div>
                    <div class="label">Students Analyzed</div>
                </td>
                <td class="summary-box success">
                    <h3>Likely to Graduate</h3>
                    <div class="value"><?php echo number_format($reportData['likely_graduate']); ?></div>
                    <div class="label"><?php echo $reportData['graduation_rate']; ?>% Success Rate</div>
                </td>
                <td class="summary-box warning">
                    <h3>Unlikely to Graduate</h3>
                    <div class="value"><?php echo number_format($reportData['unlikely_graduate']); ?></div>
                    <div class="label">Needs Intervention</div>
                </td>
                <td class="summary-box">
                    <h3>Avg Probability</h3>
                    <div class="value"><?php echo $reportData['avg_probability']; ?>%</div>
                    <div class="label">Confidence Score</div>
                </td>
            </tr>
        </table>

        <!-- Breakdown by Program -->
        <?php if (isset($reportData['has_program']) && $reportData['has_program'] && !empty($reportData['by_program'])): ?>
        <div class="section">
            <h2>Breakdown by Program</h2>
            <p style="color: #6b7280; font-size: 13px; margin-bottom: 15px;">
                <em>Note: Program does not affect predictions. This breakdown is for reporting purposes only.</em>
            </p>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Total</th>
                        <th>Likely</th>
                        <th>Unlikely</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData['by_program'] as $program => $stats): ?>
                        <?php $successRate = round(($stats['likely'] / $stats['total']) * 100, 1); ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($program); ?></strong></td>
                            <td><?php echo $stats['total']; ?></td>
                            <td><span class="badge badge-success"><?php echo $stats['likely']; ?></span></td>
                            <td><span class="badge badge-danger"><?php echo $stats['unlikely']; ?></span></td>
                            <td>
                                <div><?php echo $successRate; ?>%</div>
                                <div class="progress-bar">
                                    <div class="progress-fill <?php echo $successRate < 50 ? 'low' : ''; ?>" 
                                         style="width: <?php echo $successRate; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Breakdown by SHS Strand -->
        <div class="section">
            <h2>Breakdown by SHS Strand</h2>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Strand</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Likely</th>
                        <th>Unlikely</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData['by_strand'] as $strand => $stats): ?>
                        <?php 
                        $successRate = round(($stats['likely'] / $stats['total']) * 100, 1);
                        $isFavorable = in_array($strand, ['STEM', 'TVL-ICT']);
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($strand); ?></strong></td>
                            <td>
                                <?php if ($isFavorable): ?>
                                    <span class="badge badge-favorable">Favorable</span>
                                <?php else: ?>
                                    <span class="badge">Non-Favorable</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $stats['total']; ?></td>
                            <td><span class="badge badge-success"><?php echo $stats['likely']; ?></span></td>
                            <td><span class="badge badge-danger"><?php echo $stats['unlikely']; ?></span></td>
                            <td>
                                <div><?php echo $successRate; ?>%</div>
                                <div class="progress-bar">
                                    <div class="progress-fill <?php echo $successRate < 50 ? 'low' : ''; ?>" 
                                         style="width: <?php echo $successRate; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Breakdown by Gender -->
        <div class="section">
            <h2>Breakdown by Gender</h2>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Gender</th>
                        <th>Total</th>
                        <th>Likely</th>
                        <th>Unlikely</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData['by_sex'] as $sex => $stats): ?>
                        <?php $successRate = round(($stats['likely'] / $stats['total']) * 100, 1); ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($sex); ?></strong></td>
                            <td><?php echo $stats['total']; ?></td>
                            <td><span class="badge badge-success"><?php echo $stats['likely']; ?></span></td>
                            <td><span class="badge badge-danger"><?php echo $stats['unlikely']; ?></span></td>
                            <td>
                                <div><?php echo $successRate; ?>%</div>
                                <div class="progress-bar">
                                    <div class="progress-fill <?php echo $successRate < 50 ? 'low' : ''; ?>" 
                                         style="width: <?php echo $successRate; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="page-break"></div>

        <!-- Top 10 Predictions (Highest Probability) -->
        <div class="section">
            <h2>Top 10 Highest Confidence Predictions</h2>
            <div class="top-predictions">
                <?php
                $topPredictions = array_slice($sortedByProbability, 0, 10);
                foreach ($topPredictions as $pred):
                ?>
                    <div class="prediction-item <?php echo ($pred['prediction_result'] != 1) ? 'unlikely' : ''; ?>">
                        <h4><?php echo htmlspecialchars($pred['student_name'] ?? 'Anonymous'); ?> - <?php echo number_format($pred['probability'], 1); ?>%</h4>
                        <p>
                            <?php if (isset($reportData['has_program']) && $reportData['has_program'] && !empty($pred['program']) && $pred['program'] !== 'N/A'): ?>
                            <strong>Program:</strong> <?php echo htmlspecialchars($pred['program']); ?> | 
                            <?php endif; ?>
                            <strong>Sex:</strong> <?php echo htmlspecialchars($pred['sex']); ?> | 
                            <strong>GPA:</strong> <?php echo $pred['shs_gpa']; ?> | 
                            <strong>Strand:</strong> <?php echo htmlspecialchars($pred['shs_strand']); ?> |
                            <strong>Result:</strong> <?php echo ($pred['prediction_result'] == 1) ? 'Likely to Graduate' : 'Unlikely to Graduate'; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary Insights -->
        <div class="section">
            <h2>Summary Insights</h2>
            <ul style="color: #4b5563; line-height: 1.8;">
                <li>Overall graduation success rate: <strong><?php echo $reportData['graduation_rate']; ?>%</strong></li>
                <li>Total students analyzed: <strong><?php echo $reportData['processed']; ?></strong></li>
                <li>Students likely to graduate: <strong><?php echo $reportData['likely_graduate']; ?></strong></li>
                <li>Students requiring intervention: <strong><?php echo $reportData['unlikely_graduate']; ?></strong></li>
                <li>Average prediction confidence: <strong><?php echo $reportData['avg_probability']; ?>%</strong></li>
            </ul>
        </div>

        <div class="page-break"></div>

        <!-- Detailed Records Section -->
        <div class="records-header">
            <h1>Complete Prediction Records</h1>
            <p>All <?php echo $reportData['processed']; ?> processed student records with their prediction results</p>
            <?php if ($nameSortLabel): ?>
            <p style="margin-top: 6px;">
                <span class="sort-indicator">
                    <i class="fas <?php echo $filters['name_sort'] === 'asc' ? 'fa-sort-alpha-down' : 'fa-sort-alpha-up'; ?>"></i>
                    <?php echo $nameSortLabel; ?>
                </span>
            </p>
            <?php endif; ?>
            <p style="color: #999; font-size: 12px; margin-top: 8px;">
                Generated on: <?php echo date('F d, Y \a\t h:i A'); ?>
            </p>
        </div>

        <table class="records-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th class="<?php echo $filters['name_sort'] ? 'sorted' : ''; ?>" style="width: 20%;">
                        Student Name
                        <?php if ($filters['name_sort'] === 'asc'): ?>
                            <span class="sort-arrow">▲ A-Z</span>
                        <?php elseif ($filters['name_sort'] === 'desc'): ?>
                            <span class="sort-arrow">▼ Z-A</span>
                        <?php endif; ?>
                    </th>
                    <?php if (isset($reportData['has_program']) && $reportData['has_program']): ?>
                    <th style="width: 15%;">Program</th>
                    <?php endif; ?>
                    <th style="width: 10%;">Sex</th>
                    <th style="width: 10%;">SHS GPA</th>
                    <th style="width: 15%;">SHS Strand</th>
                    <th style="width: 15%;">Prediction Result</th>
                    <th style="width: 10%;">Probability (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                foreach ($reportData['predictions'] as $pred): 
                ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><strong><?php echo htmlspecialchars($pred['name']); ?></strong></td>
                        <?php if (isset($reportData['has_program']) && $reportData['has_program']): ?>
                        <td><?php echo htmlspecialchars($pred['program']); ?></td>
                        <?php endif; ?>
                        <td><?php echo htmlspecialchars($pred['sex']); ?></td>
                        <td><?php echo $pred['gpa']; ?></td>
                        <td><?php echo htmlspecialchars($pred['strand']); ?></td>
                        <td>
                            <?php if ($pred['result'] === 'Likely to Graduate'): ?>
                                <span class="badge badge-success"><?php echo $pred['result']; ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?php echo $pred['result']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo $pred['probability']; ?>%</strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="footer">
            <p><strong>Gradetrack</strong> - Student Graduation Prediction System</p>
            <p>Simplified 3-Feature Model (Sex, SHS GPA, SHS Strand)</p>
            <p>This report is generated automatically based on machine learning predictions.</p>
            <p>Report ID: SINGLE-<?php echo date('Ymd-His'); ?></p>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>