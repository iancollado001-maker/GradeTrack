<?php
// bulk_predict.php - Bulk Prediction via Excel Upload (Simplified 3-Feature Model)
require_once 'config.php';

$model = null;
try {
    $modelPath = __DIR__ . '/../model/model.json';
    if (file_exists($modelPath)) {
        $model = json_decode(file_get_contents($modelPath), true);
    }
} catch (Exception $e) {
    $model = null;
}

$uploadMessage = '';
$uploadError = '';
$downloadFile = '';
$reportData = null;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadError = 'Error uploading file. Please try again.';
    } else {
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, ['xlsx', 'xls', 'csv'])) {
            $uploadError = 'Invalid file format. Please upload an Excel (.xlsx, .xls) or CSV file.';
        } else {
            // Process the file
            require_once 'process_bulk_prediction.php';
            $result = processBulkPrediction($file['tmp_name'], $fileExt, $model);
            
            if ($result['success']) {
                $uploadMessage = $result['message'];
                $downloadFile = $result['download_file'];
                $reportData = $result['report_data'] ?? null;
            } else {
                $uploadError = $result['message'];
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Prediction - Gradetrack</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-body {
            padding-top: 100px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8f0e8 100%);
            min-height: 100vh;
        }

        .bulk-container {
            max-width: 100%;
            margin: 0;
            padding: 10px 30px;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 80px;
            animation: fadeInDown 0.6s ease-out;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--text);
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .page-header h1 i {
            color: var(--primary);
            font-size: 2.8rem;
        }

        .page-header p {
            font-size: 1.1rem;
            color: var(--text-light);
            margin: 0;
        }

        /* Main Grid Layout */
        .bulk-grid {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        /* Cards */
        .bulk-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease-out;
            transition: all 0.3s ease;
        }

        .bulk-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .card-header {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--bg-light);
        }

        .card-header h2 {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            font-size: 1.5rem;
            margin: 0 0 8px 0;
            font-weight: 800;
        }

        .card-header h2 i {
            color: var(--primary);
            font-size: 1.6rem;
        }

        .card-header p {
            color: var(--text-light);
            font-size: 0.95rem;
            margin: 0;
        }

        /* Upload Zone */
        .upload-zone {
            border: 3px dashed var(--border);
            border-radius: 20px;
            padding: 60px 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, rgba(14, 36, 18, 0.02), rgba(45, 90, 58, 0.02));
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .upload-zone::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(14, 36, 18, 0.1), transparent);
            transition: left 0.5s;
        }

        .upload-zone:hover::before {
            left: 100%;
        }

        .upload-zone:hover {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(14, 36, 18, 0.05), rgba(45, 90, 58, 0.05));
            transform: scale(1.02);
        }

        .upload-zone.dragover {
            border-color: var(--success);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            transform: scale(1.05);
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.3);
        }

        .upload-icon {
            font-size: 5rem;
            color: var(--primary);
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        .upload-zone h3 {
            color: var(--text);
            font-size: 1.4rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .upload-zone p {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .file-input {
            display: none;
        }

        .selected-file {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 15px 25px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            color: white;
            font-weight: 600;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(14, 36, 18, 0.3);
            animation: slideInUp 0.4s ease-out;
        }

        .selected-file i {
            font-size: 1.3rem;
        }

        /* Process Button */
        .process-btn {
            width: 100%;
            margin-top: 25px;
            padding: 18px;
            font-size: 1.1rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .process-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(14, 36, 18, 0.4);
        }

        .process-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Sidebar Cards */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }   

        .sidebar-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .sidebar-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            animation: fadeInRight 0.6s ease-out;
        }

        .sidebar-card h3 {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text);
            font-size: 1rem;
            margin: 0 0 12px 0;
            font-weight: 700;
        }

        .sidebar-card h3 i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .sidebar-card ul {
            margin: 0;
            padding-left: 0;
            list-style: none;
        }

        .sidebar-card ul li {
            padding: 8px 0;
            color: var(--text-light);
            font-size: 0.85rem;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            border-bottom: 1px solid var(--bg-light);
        }

        .sidebar-card ul li:last-child {
            border-bottom: none;
        }

        .sidebar-card ul li i {
            color: var(--success);
            margin-top: 2px;
            font-size: 0.8rem;
        }

        /* Template Section */
        .template-section {
            background: linear-gradient(135deg, #0e2412 0%, #2d5a3a 100%);
            padding: 40px 50px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 30px rgba(14, 36, 18, 0.3);
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .template-section h3 {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-size: 1.4rem;
            margin: 0 0 10px 0;
            font-weight: 700;
        }

        .template-section h3 i {
            color: #10b981;
            font-size: 1.5rem;
        }

        .template-section p {
            color: rgba(255, 255, 255, 0.85);
            margin: 0 0 20px 0;
            font-size: 1rem;
        }

        .template-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .btn-template {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 18px 30px;
            background: white;
            border: none;
            border-radius: 12px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 1.05rem;
            transition: all 0.3s ease;
        }

        .btn-template:hover {
            background: #10b981;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        /* Alerts */
        .alert {
            padding: 18px 24px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideInDown 0.4s ease-out;
        }

        .alert i {
            font-size: 1.5rem;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.15));
            border-left: 5px solid var(--success);
            color: var(--success-dark);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.15));
            border-left: 5px solid var(--danger);
            color: var(--danger-dark);
        }

        /* Download Section */
        .download-section {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4);
            animation: fadeInScale 0.6s ease-out;
        }

        .download-section h3 {
            margin: 0 0 10px 0;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .download-section p {
            margin: 0 0 25px 0;
            opacity: 0.95;
            font-size: 1.1rem;
            text-align: center;
        }

        .download-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 18px 30px;
            background: white;
            color: #059669;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-download:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }

        .btn-download.btn-print {
            background: #0e2412;
            color: white;
        }

        .btn-download.btn-print:hover {
            background: #1a3d23;
        }

        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }

        .stat-item {
            background: white;
            padding: 35px 25px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease-out;
        }

        .stat-item i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .stat-item h4 {
            font-size: 1rem;
            color: var(--text-light);
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .stat-item p {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--text);
            margin: 0;
        }

        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            
            #printReport, #printReport * {
                visibility: visible;
            }
            
            #printReport {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .bulk-grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
            
            .bulk-container {
                padding: 10px 30px;
            }
            
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }

            .download-buttons {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .bulk-container {
                padding: 20px 20px;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .page-header h1 i {
                font-size: 2.2rem;
            }

            .bulk-card {
                padding: 25px 20px;
            }

            .upload-zone {
                padding: 40px 20px;
            }

            .upload-icon {
                font-size: 3.5rem;
            }

            .template-buttons {
                grid-template-columns: 1fr;
            }

            .stats-bar {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .bulk-container {
                padding: 15px 15px;
            }
            
            .bulk-card, .sidebar-card {
                padding: 20px 15px;
            }
            
            .template-section {
                padding: 25px 20px;
            }

            .sidebar-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="dashboard-body">
    <!-- Navigation Panel -->
    <nav class="top-nav no-print">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>Gradetrack</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="records.php" class="nav-link">
                    <i class="fas fa-table"></i>
                    <span>Records</span>
                </a>
                <a href="index.php" class="nav-link">
                    <i class="fas fa-brain"></i>
                    <span>Single</span>
                </a>
                <a href="bulk_predict.php" class="nav-link active">
                    <i class="fas fa-file-upload"></i>
                    <span>Bulk</span>
                </a>
                <a href="about.php" class="nav-link">
                    <i class="fas fa-info-circle"></i>
                    <span>About</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="bulk-container">
        <!-- Page Header -->
        <div class="page-header no-print">
            <h1>
                <i class="fas fa-file-upload"></i>
                Bulk Prediction
            </h1>
            <p>Process multiple student predictions efficiently with Excel or CSV files</p>
        </div>

        <!-- Alerts -->
        <?php if ($uploadMessage): ?>
            <div class="alert alert-success no-print">
                <i class="fas fa-check-circle"></i>
                <div><?php echo htmlspecialchars($uploadMessage); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($uploadError): ?>
            <div class="alert alert-error no-print">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo htmlspecialchars($uploadError); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!$model): ?>
            <div class="alert alert-error no-print">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Model Not Found</strong><br>
                    Please ensure the prediction model is available before uploading files.
                </div>
            </div>
        <?php endif; ?>

        <!-- Download Section (if results available) -->
        <?php if ($downloadFile && $reportData): ?>
            <div class="download-section no-print">
                <h3><i class="fas fa-check-circle"></i> Processing Complete!</h3>
                <p>Your predictions have been generated successfully. Download or print the results below.</p>
                <div class="download-buttons">
                    <a href="<?php echo htmlspecialchars($downloadFile); ?>" class="btn-download" download>
                        <i class="fas fa-download"></i>
                        Download Excel File
                    </a>
                    <button onclick="printReport()" class="btn-download btn-print">
                        <i class="fas fa-print"></i>
                        Print Report
                    </button>
                </div>
            </div>

            <!-- Hidden Print Report -->
            <div id="printReport" style="display: none;">
                <?php include 'print_report_template.php'; ?>
            </div>
        <?php endif; ?>

        <!-- Stats Bar -->
        <div class="stats-bar no-print">
            <div class="stat-item">
                <i class="fas fa-users"></i>
                <h4>Max Rows</h4>
                <p>1,000</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-file-excel"></i>
                <h4>Available Formats</h4>
                <p>2</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-columns"></i>
                <h4>Required Columns</h4>
                <p>3</p>
            </div>
            <div class="stat-item">
                <i class="fas fa-clock"></i>
                <h4>Processing</h4>
                <p>Fast</p>
            </div>
        </div>

        <!-- Template Download Section -->
        <div class="template-section no-print">
            <h3>
                <i class="fas fa-file-download"></i>
                Get Started with Templates
            </h3>
            <p>Download a pre-formatted template to ensure your file has the correct structure</p>
            <div class="template-buttons">
                <a href="download_template.php?format=xlsx" class="btn-template">
                    <i class="fas fa-file-excel"></i>
                    Excel Template (.xlsx)
                </a>
                <a href="download_template.php?format=csv" class="btn-template">
                    <i class="fas fa-file-csv"></i>
                    CSV Template (.csv)
                </a>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="bulk-grid no-print">
            <!-- Upload Card -->
            <div class="bulk-card">
                <div class="card-header">
                    <h2>
                        <i class="fas fa-cloud-upload-alt"></i>
                        Upload Your File
                    </h2>
                    <p>Select or drag and drop your prepared file to begin processing</p>
                </div>

                <!-- Upload Form -->
                <form method="post" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-zone" id="uploadZone">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <h3>Drop your file here</h3>
                        <p>or click to browse from your computer</p>
                        <input type="file" name="excel_file" id="fileInput" class="file-input" accept=".xlsx,.xls,.csv" required>
                        <div id="selectedFile"></div>
                    </div>
                    <button type="submit" class="btn btn-primary process-btn" id="processBtn" disabled>
                        <i class="fas fa-cogs"></i>
                        <span>Process Predictions</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-grid">
                    <!-- Required Columns -->
                    <div class="sidebar-card">
                        <h3>
                            <i class="fas fa-list-check"></i>
                            Required Columns
                        </h3>
                        <ul>
                            <li><i class="fas fa-circle-check"></i> Sex (Male/Female)</li>
                            <li><i class="fas fa-circle-check"></i> SHS GPA (0-100)</li>
                            <li><i class="fas fa-circle-check"></i> SHS Strand</li>
                        </ul>
                    </div>

                    <!-- Optional Columns -->
                    <div class="sidebar-card">
                        <h3>
                            <i class="fas fa-circle-info"></i>
                            Optional Columns
                        </h3>
                        <ul>
                            <li><i class="fas fa-check"></i> Student Name (for identification)</li>
                            <li><i class="fas fa-check"></i> Program (for reporting only)</li>
                        </ul>
                        <p style="font-size: 0.8rem; color: var(--text-light); margin-top: 8px; line-height: 1.4;">
                            <em>Note: Program does not affect predictions, only used for organizing results.</em>
                        </p>
                    </div>

                    <!-- Important Notes -->
                    <div class="sidebar-card">
                        <h3>
                            <i class="fas fa-info-circle"></i>
                            Important Notes
                        </h3>
                        <ul>
                            <li><i class="fas fa-check"></i> Column names must match exactly</li>
                            <li><i class="fas fa-check"></i> No empty required fields allowed</li>
                            <li><i class="fas fa-check"></i> Use valid values from template</li>
                            <li><i class="fas fa-check"></i> Maximum 1000 rows per file</li>
                        </ul>
                    </div>

                    <!-- Output Info -->
                    <div class="sidebar-card">
                        <h3>
                            <i class="fas fa-file-export"></i>
                            Output Includes
                        </h3>
                        <ul>
                            <li><i class="fas fa-check"></i> All original data preserved</li>
                            <li><i class="fas fa-check"></i> Prediction Result</li>
                            <li><i class="fas fa-check"></i> Probability Score (%)</li>
                            <li><i class="fas fa-check"></i> Prediction Timestamp</li>
                            <li><i class="fas fa-check"></i> Program breakdown (if provided)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const selectedFile = document.getElementById('selectedFile');
        const processBtn = document.getElementById('processBtn');

        // Click to upload
        uploadZone.addEventListener('click', (e) => {
            if (e.target.closest('.selected-file')) return;
            fileInput.click();
        });

        // File selection
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const fileSize = (file.size / 1024).toFixed(2);
                const fileSizeUnit = fileSize > 1024 ? ((fileSize / 1024).toFixed(2) + ' MB') : (fileSize + ' KB');
                
                selectedFile.innerHTML = `
                    <div class="selected-file">
                        <i class="fas fa-file-excel"></i>
                        <span>${file.name}</span>
                        <span style="opacity: 0.9;">(${fileSizeUnit})</span>
                    </div>
                `;
                processBtn.disabled = false;
            }
        });

        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', (e) => {
            if (e.target === uploadZone) {
                uploadZone.classList.remove('dragover');
            }
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });

        // Form submission
        document.getElementById('uploadForm').addEventListener('submit', (e) => {
            processBtn.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                <span>Processing Predictions...</span>
            `;
            processBtn.disabled = true;
        });

        // Print function
        function printReport() {
            const printContent = document.getElementById('printReport');
            if (printContent) {
                printContent.style.display = 'block';
                window.print();
                printContent.style.display = 'none';
            }
        }
    </script>
</body>
</html>