<?php
// print_report_template.php - Printable Report for 3-Feature Model

// Set timezone to Philippines (UTC+8)
date_default_timezone_set('Asia/Manila');
?>
<style>
    @page {
        size: A4;
        margin: 15mm;
    }

    .print-report {
        font-family: 'Arial', sans-serif;
        max-width: 210mm;
        margin: 0 auto;
        background: white;
        padding: 20px;
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

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
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

    .summary-box.success .value {
        color: #059669;
    }

    .summary-box.warning {
        background: #fef3c7;
        border-color: #f59e0b;
    }

    .summary-box.warning .value {
        color: #d97706;
    }

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

    .breakdown-table tr:hover {
        background: #f9fafb;
    }

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

    .progress-fill.low {
        background: linear-gradient(90deg, #ef4444, #dc2626);
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-favorable {
        background: #dcfce7;
        color: #166534;
    }

    .footer {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 2px solid #e5e7eb;
        text-align: center;
        color: #999;
        font-size: 11px;
    }

    .page-break {
        page-break-after: always;
    }

    .top-predictions {
        margin-top: 15px;
    }

    .prediction-item {
        padding: 10px;
        border-left: 4px solid #10b981;
        background: #f9fafb;
        margin-bottom: 8px;
        border-radius: 4px;
    }

    .prediction-item.unlikely {
        border-left-color: #ef4444;
    }

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

    .model-info h3 {
        color: #065f46;
        margin: 0 0 10px 0;
        font-size: 16px;
    }

    .model-info ul {
        margin: 0;
        padding-left: 20px;
        color: #166534;
    }

    .model-info li {
        margin: 5px 0;
    }

    /* Styles for detailed records table */
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
        position: sticky;
        top: 0;
    }

    .records-table td {
        color: #4b5563;
        font-size: 11px;
    }

    .records-table tr:nth-child(even) {
        background: #f9fafb;
    }

    .records-table tr:hover {
        background: #f3f4f6;
    }

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

    .records-header p {
        color: #666;
        font-size: 13px;
        margin: 0;
    }
</style>

<div class="print-report">
    <!-- Report Header -->
    <div class="report-header">
        <h1>Bulk Prediction Report</h1>
        <div class="subtitle">Gradetrack Student Graduation Prediction System</div>
        <div class="subtitle">Simplified 3-Feature Model</div>
        <div class="timestamp">Generated: <?php echo htmlspecialchars($reportData['timestamp']); ?></div>
    </div>

    <!-- Model Information -->
    <div class="model-info">
        <h3>🎓 Model Information</h3>
        <p style="margin: 0 0 10px 0; color: #166534;">This prediction uses a simplified 3-feature machine learning model:</p>
        <ul>
            <li><strong>Sex</strong> - Male or Female</li>
            <li><strong>SHS GPA</strong> - Senior High School Grade Point Average</li>
            <li><strong>SHS Strand</strong> - Favorable strands: STEM, TVL-ICT</li>
        </ul>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-grid">
        <div class="summary-box">
            <h3>Total Records</h3>
            <div class="value"><?php echo number_format($reportData['total_records']); ?></div>
            <div class="label">Students Analyzed</div>
        </div>

        <div class="summary-box success">
            <h3>Likely to Graduate</h3>
            <div class="value"><?php echo number_format($reportData['likely_graduate']); ?></div>
            <div class="label"><?php echo $reportData['graduation_rate']; ?>% Success Rate</div>
        </div>

        <div class="summary-box warning">
            <h3>Unlikely to Graduate</h3>
            <div class="value"><?php echo number_format($reportData['unlikely_graduate']); ?></div>
            <div class="label">Needs Intervention</div>
        </div>

        <div class="summary-box">
            <h3>Avg Probability</h3>
            <div class="value"><?php echo $reportData['avg_probability']; ?>%</div>
            <div class="label">Confidence Score</div>
        </div>
    </div>

    <!-- Breakdown by Program (if available) -->
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
                                     style="width: <?php echo $successRate; ?>%">
                                </div>
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
                                     style="width: <?php echo $successRate; ?>%">
                                </div>
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
                                     style="width: <?php echo $successRate; ?>%">
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Page Break for Second Page -->
    <div class="page-break"></div>

    <!-- Top 10 Predictions (Highest Probability) -->
    <div class="section">
        <h2>Top 10 Highest Confidence Predictions</h2>
        <div class="top-predictions">
            <?php
            // Sort predictions by probability (descending)
            $sortedPredictions = $reportData['predictions'];
            usort($sortedPredictions, function($a, $b) {
                return $b['probability'] - $a['probability'];
            });
            
            // Show top 10
            $topPredictions = array_slice($sortedPredictions, 0, 10);
            foreach ($topPredictions as $pred):
            ?>
                <div class="prediction-item <?php echo $pred['result'] === 'Unlikely to Graduate' ? 'unlikely' : ''; ?>">
                    <h4><?php echo htmlspecialchars($pred['name']); ?> - <?php echo $pred['probability']; ?>%</h4>
                    <p>
                        <?php if (isset($reportData['has_program']) && $reportData['has_program'] && !empty($pred['program']) && $pred['program'] !== 'N/A'): ?>
                        <strong>Program:</strong> <?php echo htmlspecialchars($pred['program']); ?> | 
                        <?php endif; ?>
                        <strong>Sex:</strong> <?php echo htmlspecialchars($pred['sex']); ?> | 
                        <strong>GPA:</strong> <?php echo $pred['gpa']; ?> | 
                        <strong>Strand:</strong> <?php echo htmlspecialchars($pred['strand']); ?> |
                        <strong>Result:</strong> <?php echo $pred['result']; ?>
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
            <?php if ($reportData['errors'] > 0): ?>
                <li style="color: #dc2626;">Errors encountered: <strong><?php echo $reportData['errors']; ?> records</strong></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Page Break for Detailed Records -->
    <div class="page-break"></div>

    <!-- Detailed Records Section -->
    <div class="records-header">
        <h1>Complete Prediction Records</h1>
        <p>All <?php echo $reportData['processed']; ?> processed student records with their prediction results</p>
        <p style="color: #999; font-size: 12px; margin-top: 8px;">
            Generated on: <?php echo date('F d, Y \a\t h:i A'); ?>
        </p>

        <table class="records-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Student Name</th>
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
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Gradetrack</strong> - Student Graduation Prediction System</p>
        <p>Simplified 3-Feature Model (Sex, SHS GPA, SHS Strand)</p>
        <p>This report is generated automatically based on machine learning predictions.</p>
        <p>Report ID: BULK-<?php echo date('Ymd-His'); ?></p>
    </div>
</div>