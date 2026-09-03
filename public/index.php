<?php
  // index.php
  require_once 'config.php';
  
  $model = null;
  try {
      $model = json_decode(file_get_contents(__DIR__ . '/../model/model.json'), true);
  } catch (Exception $e) {
      $model = null;
  }

  // Check if editing mode
  $isEditing = false;
  $editData = null;
  if (isset($_GET['edit']) && !empty($_GET['edit'])) {
      $editId = intval($_GET['edit']);
      $editData = getPredictionById($editId);
      if ($editData) {
          $isEditing = true;
      }
  }

  // Available programs
  $programs = [
      'BSCS' => 'Bachelor of Science in Computer Science (BSCS)',
      'BSIT' => 'Bachelor of Science in Information Technology (BSIT)',
      'BSIS' => 'Bachelor of Science in Information Systems (BSIS)',
      'BSCpE' => 'Bachelor of Science in Computer Engineering (BSCpE)',
      'BSECE' => 'Bachelor of Science in Electronics Engineering (BSECE)'
  ];
  
  // Available SHS Strands
  $strands = ['STEM', 'ABM', 'HUMSS', 'GAS', 'TVL-IA', 'TVL-HE', 'TVL-ICT'];
  
  // Non-applicable strands (shown with warning)
  $nonApplicableStrands = ['Sports Track', 'TVL-AFA', 'Arts and Design'];
  ?>
  <!doctype html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditing ? 'Edit Record' : 'Gradetrack Predictor'; ?> - AI-Powered Graduation Prediction</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      /* Modern Minimalist Design System */
      :root {
        --spacing-xs: 8px;
        --spacing-sm: 12px;
        --spacing-md: 20px;
        --spacing-lg: 32px;
        --spacing-xl: 48px;
        --spacing-2xl: 64px;
        
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 24px;
        
        --shadow-subtle: 0 1px 3px rgba(0, 0, 0, 0.04);
        --shadow-soft: 0 2px 8px rgba(0, 0, 0, 0.06);
        --shadow-medium: 0 4px 16px rgba(0, 0, 0, 0.08);
        --shadow-elevated: 0 8px 32px rgba(0, 0, 0, 0.12);
      }

      /* Clean body layout */
      body {
        background: #fafbfa;
        padding: 0;
        display: block;
        min-height: 100vh;
        font-size: 15px;
        line-height: 1.6;
      }

      .background-animation {
        display: none;
      }

      /* Spacious dashboard container */
      .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: var(--spacing-2xl) var(--spacing-lg);
        padding-top: var(--spacing-md);
      }

      /* Clean container reset */
      .container {
        background: transparent;
        backdrop-filter: none;
        padding: 0;
        border-radius: 0;
        box-shadow: none;
        width: 100%;
        max-width: none;
        margin-top: 0;
        max-height: none;
        overflow-y: visible;
        animation: none;
      }

      .header-section {
        display: none;
      }

      /* Edit Mode Banner */
      .edit-mode-banner {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1));
        border: 2px solid #3b82f6;
        padding: 20px 25px;
        border-radius: var(--radius-lg);
        margin-bottom: var(--spacing-lg);
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        animation: slideDown 0.3s ease;
      }

      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .edit-mode-banner i {
        color: #2563eb;
        font-size: 24px;
      }

      .edit-mode-banner-content h3 {
        color: #1e40af;
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 4px 0;
      }

      .edit-mode-banner-content p {
        color: #3b82f6;
        font-size: 14px;
        margin: 0;
      }

      /* Modern minimalist form card */
      .form-card {
        background: white;
        padding: var(--spacing-xl);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-soft);
        border: 1px solid rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.3s ease;
      }

      .form-card:hover {
        box-shadow: var(--shadow-medium);
      }

      /* Refined header */
      .form-card-header {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f1f0;
      }

      .form-card-header h2 {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        color: #1a1f1a;
        font-size: 28px;
        margin: 0 0 var(--spacing-xs) 0;
        font-weight: 700;
        letter-spacing: -0.02em;
      }

      .form-card-header h2 i {
        color: var(--primary);
        font-size: 28px;
        opacity: 0.9;
      }

      .form-card-header p {
        color: #6b7566;
        font-size: 15px;
        margin: 0;
        font-weight: 400;
        letter-spacing: -0.01em;
      }

      /* Status badge - minimal redesign */
      .status-badge-container {
        display: flex;
        justify-content: flex-end;
        margin-bottom: var(--spacing-lg);
      }

      .status-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
        padding: var(--spacing-xs) var(--spacing-md);
        border-radius: 100px;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: -0.01em;
        border: 1px solid;
      }

      .status-active {
        background: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
      }

      .status-active i {
        color: #16a34a;
        font-size: 8px;
      }

      .status-error {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
      }

      .status-error i {
        color: #dc2626;
        font-size: 8px;
      }

      /* Clean error banner */
      .error-banner {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
        display: flex;
        gap: var(--spacing-md);
        align-items: flex-start;
      }

      .error-banner i {
        color: #dc2626;
        font-size: 20px;
        margin-top: 2px;
        opacity: 0.9;
      }

      .error-banner strong {
        display: block;
        color: #991b1b;
        margin-bottom: 4px;
        font-size: 14px;
        font-weight: 600;
      }

      .error-banner p {
        color: #7f1d1d;
        margin: 0;
        font-size: 14px;
        line-height: 1.5;
      }

      /* Warning banner for non-applicable strands */
      .warning-banner {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
        display: none;
        gap: var(--spacing-md);
        align-items: flex-start;
      }

      .warning-banner.show {
        display: flex;
      }

      .warning-banner i {
        color: #f59e0b;
        font-size: 20px;
        margin-top: 2px;
        opacity: 0.9;
      }

      .warning-banner strong {
        display: block;
        color: #92400e;
        margin-bottom: 4px;
        font-size: 14px;
        font-weight: 600;
      }

      .warning-banner p {
        color: #78350f;
        margin: 0;
        font-size: 14px;
        line-height: 1.5;
      }

      /* Validation error banner */
      .validation-error-banner {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        margin-bottom: var(--spacing-lg);
        display: none;
        gap: var(--spacing-md);
        align-items: flex-start;
        animation: slideDown 0.3s ease;
      }

      .validation-error-banner.show {
        display: flex;
      }

      .validation-error-banner i {
        color: #dc2626;
        font-size: 20px;
        margin-top: 2px;
        opacity: 0.9;
      }

      .validation-error-banner strong {
        display: block;
        color: #991b1b;
        margin-bottom: 4px;
        font-size: 14px;
        font-weight: 600;
      }

      .validation-error-banner p {
        color: #7f1d1d;
        margin: 0;
        font-size: 14px;
        line-height: 1.5;
      }

      .validation-error-list {
        margin: 8px 0 0 0;
        padding-left: 20px;
        color: #7f1d1d;
        font-size: 13px;
      }

      .validation-error-list li {
        margin: 4px 0;
      }

      /* Simplified 2-column grid */
      .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
      }

      .full-width {
        grid-column: span 2;
      }

      /* Modern form groups */
      .form-group {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
      }

      .form-group.has-error input,
      .form-group.has-error select {
        border-color: #dc2626;
        background: #fef2f2;
      }

      .form-group.has-error label {
        color: #dc2626;
      }

      .form-group label {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        font-weight: 500;
        color: #2d3a2d;
        font-size: 14px;
        letter-spacing: -0.01em;
      }

      .form-group label i {
        color: var(--primary);
        font-size: 16px;
        opacity: 0.8;
      }

      .required-star {
        color: #dc2626;
        margin-left: auto;
        font-size: 14px;
      }

      .optional-tag {
        margin-left: auto;
        font-size: 11px;
        background: #f5f6f5;
        color: #6b7566;
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 500;
        letter-spacing: 0;
      }

      /* Clean input styling */
      .form-group input,
      .form-group select {
        width: 100%;
        padding: 13px 16px;
        border: 1.5px solid #e5e7e5;
        border-radius: var(--radius-md);
        font-size: 15px;
        transition: all 0.2s ease;
        background: white;
        font-family: inherit;
        color: #1a1f1a;
        letter-spacing: -0.01em;
      }

      .form-group input:hover,
      .form-group select:hover {
        border-color: #d1d5d1;
      }

      .form-group input:focus,
      .form-group select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 4px rgba(14, 36, 18, 0.06);
      }

      .form-group input::placeholder {
        color: #9ca3af;
        font-size: 14px;
      }

      /* Non-applicable option styling */
      .non-applicable-option {
        color: #9ca3af;
        font-style: italic;
      }

      .strand-divider {
        border-top: 1px solid #e5e7e5;
        margin: 4px 0;
        padding-top: 4px;
      }

      /* Refined select wrapper */
      .select-wrapper {
        position: relative;
      }

      .select-wrapper select {
        appearance: none;
        padding-right: 42px;
        cursor: pointer;
      }

      .select-arrow {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7566;
        pointer-events: none;
        transition: color 0.2s ease;
        font-size: 14px;
      }

      .select-wrapper:hover .select-arrow {
        color: var(--primary);
      }

      /* Subtle field hints */
      .field-hint {
        font-size: 13px;
        color: #6b7566;
        margin-top: 4px;
        line-height: 1.4;
        letter-spacing: -0.01em;
      }

      .field-error {
        font-size: 13px;
        color: #dc2626;
        margin-top: 4px;
        line-height: 1.4;
        letter-spacing: -0.01em;
        display: none;
      }

      .form-group.has-error .field-error {
        display: block;
      }

      /* Minimal progress bar */
      .progress-bar-container {
        width: 100%;
        height: 4px;
        background: #f0f1f0;
        border-radius: 10px;
        overflow: hidden;
        margin-top: var(--spacing-xs);
      }

      .progress-bar {
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, #16a34a, #15803d);
        border-radius: 10px;
        transition: width 0.3s ease, background 0.3s ease;
      }

      /* Clean button container */
      .button-container {
        padding-top: var(--spacing-lg);
        border-top: 1px solid #f0f1f0;
        display: flex;
        gap: var(--spacing-sm);
      }

      /* Modern primary button */
      .btn-primary {
        flex: 1;
        padding: 16px 24px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        transition: all 0.3s ease;
        box-shadow: var(--shadow-subtle);
        letter-spacing: -0.01em;
      }

      .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-elevated);
      }

      .btn-primary:active {
        transform: translateY(0);
      }

      .btn-primary i:first-child {
        font-size: 18px;
      }

      .btn-primary i:last-child {
        font-size: 14px;
        margin-left: auto;
      }

      /* Cancel button for edit mode */
      .btn-cancel {
        flex: 0 0 auto;
        padding: 16px 24px;
        background: white;
        color: var(--text);
        border: 2px solid #e5e7e5;
        border-radius: var(--radius-md);
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        transition: all 0.3s ease;
        text-decoration: none;
        letter-spacing: -0.01em;
      }

      .btn-cancel:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
      }

      /* Error animation */
      .error-shake {
        animation: shake 0.4s ease;
      }

      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-8px); }
        75% { transform: translateX(8px); }
      }

      /* Confirmation Modal Styles */
      .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        animation: fadeIn 0.3s ease;
      }

      .modal-overlay.active {
        display: flex;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
        }
        to {
          opacity: 1;
        }
      }

      .modal-content {
        background: white;
        border-radius: var(--radius-xl);
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-elevated);
        animation: slideUp 0.3s ease;
      }

      @keyframes slideUp {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .modal-header {
        padding: var(--spacing-lg);
        border-bottom: 1px solid #f0f1f0;
      }

      .modal-header h3 {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        color: #1a1f1a;
        font-size: 22px;
        margin: 0 0 var(--spacing-xs) 0;
        font-weight: 700;
        letter-spacing: -0.02em;
      }

      .modal-header h3 i {
        color: #f59e0b;
        font-size: 24px;
      }

      .modal-header p {
        color: #6b7566;
        font-size: 14px;
        margin: 0;
      }

      .modal-body {
        padding: var(--spacing-lg);
      }

      .confirmation-grid {
        display: grid;
        gap: var(--spacing-md);
      }

      .confirmation-item {
        background: #f9fafb;
        padding: var(--spacing-md);
        border-radius: var(--radius-md);
        border: 1px solid #e5e7e5;
        transition: all 0.2s ease;
      }

      .confirmation-item:hover {
        background: #f3f4f6;
        border-color: #d1d5d1;
      }

      .confirmation-item.highlight {
        background: #fef3c7;
        border-color: #fbbf24;
      }

      .confirmation-label {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        font-size: 12px;
        font-weight: 600;
        color: #6b7566;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
      }

      .confirmation-label i {
        font-size: 14px;
        opacity: 0.7;
      }

      .confirmation-value {
        color: #1a1f1a;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: -0.01em;
      }

      .confirmation-value.empty {
        color: #9ca3af;
        font-style: italic;
        font-weight: 400;
      }

      .modal-footer {
        padding: var(--spacing-lg);
        border-top: 1px solid #f0f1f0;
        display: flex;
        gap: var(--spacing-sm);
      }

      .btn-confirm {
        flex: 1;
        padding: 16px 24px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        transition: all 0.3s ease;
        box-shadow: var(--shadow-subtle);
        letter-spacing: -0.01em;
      }

      .btn-confirm:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-elevated);
      }

      .btn-back {
        flex: 0 0 auto;
        padding: 16px 24px;
        background: white;
        color: var(--text);
        border: 2px solid #e5e7e5;
        border-radius: var(--radius-md);
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-sm);
        transition: all 0.3s ease;
        letter-spacing: -0.01em;
      }

      .btn-back:hover {
        border-color: var(--primary);
        color: var(--primary);
      }

      /* Responsive adjustments */
      @media (max-width: 768px) {
        .dashboard-container {
          padding: var(--spacing-lg) var(--spacing-md);
          padding-top: 80px;
        }

        .form-card {
          padding: var(--spacing-lg);
          border-radius: var(--radius-lg);
        }

        .form-card-header h2 {
          font-size: 24px;
        }

        .form-grid {
          grid-template-columns: 1fr;
          gap: var(--spacing-md);
        }
        
        .full-width {
          grid-column: span 1;
        }

        .btn-primary {
          padding: 14px 20px;
        }

        .button-container {
          flex-direction: column;
        }

        .btn-cancel {
          flex: 1;
        }

        .modal-content {
          width: 95%;
        }

        .modal-footer {
          flex-direction: column-reverse;
        }

        .btn-back {
          flex: 1;
        }
      }

      @media (max-width: 480px) {
        .dashboard-container {
          padding: var(--spacing-md) var(--spacing-sm);
          padding-top: 70px;
        }

        .form-card {
          padding: var(--spacing-md);
        }

        .form-card-header {
          margin-bottom: var(--spacing-lg);
          padding-bottom: var(--spacing-md);
        }

        .form-card-header h2 {
          font-size: 22px;
        }

        .form-card-header p {
          font-size: 14px;
        }
      }
    </style>
  </head>
  <body class="dashboard-body">
    <!-- Navigation Panel -->
    <nav class="top-nav">
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
                <a href="index.php" class="nav-link active">
                    <i class="fas fa-brain"></i>
                    <span>Single</span>
                </a>
                <a href="bulk_predict.php" class="nav-link">
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

    <div class="dashboard-container">
      <div class="container">
        <?php if ($isEditing): ?>
        <!-- Edit Mode Banner -->
        <div class="edit-mode-banner">
          <i class="fas fa-edit"></i>
          <div class="edit-mode-banner-content">
            <h3>Edit Mode</h3>
            <p>Update the prediction details and save changes</p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card">
          <div class="form-card-header">
            <h2>
              <i class="fas <?php echo $isEditing ? 'fa-edit' : 'fa-brain'; ?>"></i>
              <?php echo $isEditing ? 'Edit Prediction' : 'Single Prediction'; ?>
            </h2>
            <p><?php echo $isEditing ? 'Modify student information and update prediction' : 'Enter student information to predict graduation likelihood'; ?></p>
          </div>

          <div class="status-badge-container">
            <div class="status-badge <?php echo $model ? 'status-active' : 'status-error'; ?>">
              <i class="fas fa-circle"></i>
              <span><?php echo $model ? 'Model Active' : 'Model Not Found'; ?></span>
            </div>
          </div>

          <?php if (!$model): ?>
            <div class="error-banner">
              <i class="fas fa-exclamation-triangle"></i>
              <div>
                <strong>Model Not Found</strong>
                <p>Please run the Python trainer and ensure model/model.json is available.</p>
              </div>
            </div>
          <?php endif; ?>

          <!-- Validation error banner -->
          <div class="validation-error-banner" id="validationError">
            <i class="fas fa-exclamation-circle"></i>
            <div>
              <strong>Please Complete All Required Fields</strong>
              <p>The following fields need your attention:</p>
              <ul class="validation-error-list" id="validationErrorList"></ul>
            </div>
          </div>

          <!-- Warning banner for non-applicable strands -->
          <div class="warning-banner" id="strandWarning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
              <strong>Strand Not Applicable</strong>
              <p>The selected strand (Sports Track, TVL-AFA, or Arts and Design) may not be applicable for this analysis/prediction. Results may not be accurate for these tracks.</p>
            </div>
          </div>

          <form method="post" action="<?php echo $isEditing ? 'update_prediction.php' : 'predict.php'; ?>" id="predictionForm">
            <?php if ($isEditing): ?>
              <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($editData['id']); ?>">
            <?php endif; ?>
            
            <div class="form-grid">
              <div class="form-group full-width">
                <label for="student_name">
                  <i class="fas fa-user"></i>
                  Student Name
                  <span class="optional-tag">optional</span>
                </label>
                <input type="text" id="student_name" name="student_name" 
                       placeholder="Enter full name (Lastname_Firstname_Middlename)" 
                       maxlength="100"
                       value="<?php echo $isEditing ? htmlspecialchars($editData['student_name']) : ''; ?>">
                <div class="field-hint">Optional field for record keeping</div>
              </div>

              <div class="form-group" id="programGroup">
                <label for="program_choice">
                  <i class="fas fa-graduation-cap"></i>
                  Program Choice
                  <span class="required-star">*</span>
                </label>
                <div class="select-wrapper">
                  <select name="program_choice" id="program_choice" required>
                    <option value="">Select program...</option>
                    <?php foreach($programs as $code => $name): ?>
                      <option value="<?php echo htmlspecialchars($code); ?>" 
                              <?php echo ($isEditing && $editData['program'] === $code) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <i class="fas fa-chevron-down select-arrow"></i>
                </div>
                <div class="field-hint">Choose your degree program</div>
                <div class="field-error">Please select a program</div>
              </div>

              <div class="form-group" id="strandGroup">
                <label for="SHS_Strand">
                  <i class="fas fa-book-open"></i>
                  SHS Strand
                  <span class="required-star">*</span>
                </label>
                <div class="select-wrapper">
                  <select name="SHS_Strand" id="SHS_Strand" required>
                    <option value="">Select strand...</option>
                    <?php foreach($strands as $s): ?>
                      <option value="<?php echo htmlspecialchars($s); ?>"
                              <?php echo ($isEditing && $editData['shs_strand'] === $s) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s); ?>
                      </option>
                    <?php endforeach; ?>
                    <option disabled class="strand-divider">────────────────</option>
                    <?php foreach($nonApplicableStrands as $nas): ?>
                      <option value="<?php echo htmlspecialchars($nas); ?>" 
                              class="non-applicable-option"
                              <?php echo ($isEditing && $editData['shs_strand'] === $nas) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($nas); ?> (Not Applicable)
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <i class="fas fa-chevron-down select-arrow"></i>
                </div>
                <div class="field-hint">STEM and TVL-ICT show higher rates</div>
                <div class="field-error">Please select a strand</div>
              </div>

              <div class="form-group" id="gpaGroup">
                <label for="SHS_GPA">
                  <i class="fas fa-chart-line"></i>
                  SHS GPA
                  <span class="required-star">*</span>
                </label>
                <input required type="number" id="SHS_GPA" name="SHS_GPA" 
                       min="0" max="100" step="0.1" 
                       placeholder="Enter GPA (0-100)"
                       value="<?php echo $isEditing ? htmlspecialchars($editData['shs_gpa']) : ''; ?>">
                <div class="progress-bar-container">
                  <div class="progress-bar" id="gpaProgress"></div>
                </div>
                <div class="field-hint">General average from senior high school (0-100)</div>
                <div class="field-error">Please enter a valid GPA between 0 and 100</div>
              </div>

              <div class="form-group" id="sexGroup">
                <label for="Sex">
                  <i class="fas fa-venus-mars"></i>
                  Sex
                  <span class="required-star">*</span>
                </label>
                <div class="select-wrapper">
                  <select name="Sex" id="Sex" required>
                    <option value="">Select...</option>
                    <option value="Male" <?php echo ($isEditing && $editData['sex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($isEditing && $editData['sex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                  </select>
                  <i class="fas fa-chevron-down select-arrow"></i>
                </div>
                <div class="field-hint">Biological sex</div>
                <div class="field-error">Please select sex</div>
              </div>
            </div>
            
            <div class="button-container">
              <?php if ($isEditing): ?>
                <a href="records.php" class="btn-cancel">
                  <i class="fas fa-times"></i>
                  <span>Cancel</span>
                </a>
              <?php endif; ?>
              <button class="btn btn-primary" type="button" id="reviewButton">
                <i class="fas <?php echo $isEditing ? 'fa-save' : 'fa-brain'; ?>"></i>
                <span><?php echo $isEditing ? 'Review & Update' : 'Review & Predict'; ?></span>
                <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirmationModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>
            <i class="fas fa-check-circle"></i>
            Confirm Your Information
          </h3>
          <p>Please verify all details are correct before submitting</p>
        </div>
        <div class="modal-body">
          <div class="confirmation-grid">
            <div class="confirmation-item" id="confirmName">
              <div class="confirmation-label">
                <i class="fas fa-user"></i>
                Student Name
              </div>
              <div class="confirmation-value" id="confirmNameValue">-</div>
            </div>
            <div class="confirmation-item highlight">
              <div class="confirmation-label">
                <i class="fas fa-graduation-cap"></i>
                Program Choice
              </div>
              <div class="confirmation-value" id="confirmProgramValue">-</div>
            </div>
            <div class="confirmation-item highlight">
              <div class="confirmation-label">
                <i class="fas fa-book-open"></i>
                SHS Strand
              </div>
              <div class="confirmation-value" id="confirmStrandValue">-</div>
            </div>
            <div class="confirmation-item highlight">
              <div class="confirmation-label">
                <i class="fas fa-chart-line"></i>
                SHS GPA
              </div>
              <div class="confirmation-value" id="confirmGPAValue">-</div>
            </div>
            <div class="confirmation-item highlight">
              <div class="confirmation-label">
                <i class="fas fa-venus-mars"></i>
                Sex
              </div>
              <div class="confirmation-value" id="confirmSexValue">-</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-back" id="backButton">
            <i class="fas fa-arrow-left"></i>
            <span>Go Back & Edit</span>
          </button>
          <button type="button" class="btn-confirm" id="confirmSubmit">
            <i class="fas fa-check"></i>
            <span><?php echo $isEditing ? 'Confirm & Update' : 'Confirm & Predict'; ?></span>
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>

    <script>
      // Program names mapping for display
      const programNames = {
        'BSCS': 'Bachelor of Science in Computer Science (BSCS)',
        'BSIT': 'Bachelor of Science in Information Technology (BSIT)',
        'BSIS': 'Bachelor of Science in Information Systems (BSIS)',
        'BSCpE': 'Bachelor of Science in Computer Engineering (BSCpE)',
        'BSECE': 'Bachelor of Science in Electronics Engineering (BSECE)'
      };

      // Non-applicable strands
      const nonApplicableStrands = ['Sports Track', 'TVL-AFA', 'Arts and Design'];

      // Check for non-applicable strand and show warning
      function checkStrandApplicability() {
        const strandSelect = document.getElementById('SHS_Strand');
        const warningBanner = document.getElementById('strandWarning');
        const selectedStrand = strandSelect.value;

        if (nonApplicableStrands.includes(selectedStrand)) {
          warningBanner.classList.add('show');
        } else {
          warningBanner.classList.remove('show');
        }
      }

      // Add event listener to strand select
      document.getElementById('SHS_Strand').addEventListener('change', checkStrandApplicability);

      // Check on page load (for edit mode)
      window.addEventListener('load', checkStrandApplicability);

      // GPA Progress Bar and Validation
      const gpaInput = document.getElementById('SHS_GPA');
      const gpaProgress = document.getElementById('gpaProgress');
      
      function updateGPAProgress() {
        let value = parseFloat(gpaInput.value) || 0;
        
        // Enforce min/max constraints
        if (value > 100) {
          gpaInput.value = 100;
          value = 100;
        } else if (value < 0) {
          gpaInput.value = 0;
          value = 0;
        }
        
        const percentage = Math.min((value / 100) * 100, 100);
        gpaProgress.style.width = percentage + '%';
        
        if (value < 75) {
          gpaProgress.style.background = 'linear-gradient(90deg, #dc2626, #b91c1c)';
        } else if (value < 85) {
          gpaProgress.style.background = 'linear-gradient(90deg, #f59e0b, #d97706)';
        } else {
          gpaProgress.style.background = 'linear-gradient(90deg, #16a34a, #15803d)';
        }
      }
      
      gpaInput.addEventListener('input', updateGPAProgress);
      gpaInput.addEventListener('blur', updateGPAProgress); // Also validate on blur
      
      // Initialize progress bar on page load (for edit mode)
      window.addEventListener('load', updateGPAProgress);

      // Validation function
      function validateForm() {
        const validationError = document.getElementById('validationError');
        const validationErrorList = document.getElementById('validationErrorList');
        const errors = [];
        
        // Clear previous error states
        document.querySelectorAll('.form-group.has-error').forEach(group => {
          group.classList.remove('has-error');
        });
        
        // Validate Program Choice
        const programChoice = document.getElementById('program_choice');
        if (!programChoice.value) {
          errors.push('Program Choice is required');
          document.getElementById('programGroup').classList.add('has-error');
        }
        
        // Validate SHS Strand
        const shsStrand = document.getElementById('SHS_Strand');
        if (!shsStrand.value) {
          errors.push('SHS Strand is required');
          document.getElementById('strandGroup').classList.add('has-error');
        }
        
        // Validate SHS GPA
        const shsGPA = document.getElementById('SHS_GPA');
        const gpaValue = parseFloat(shsGPA.value);
        if (!shsGPA.value || isNaN(gpaValue) || gpaValue < 0 || gpaValue > 100) {
          errors.push('Valid SHS GPA (0-100) is required');
          document.getElementById('gpaGroup').classList.add('has-error');
        }
        
        // Validate Sex
        const sex = document.getElementById('Sex');
        if (!sex.value) {
          errors.push('Sex is required');
          document.getElementById('sexGroup').classList.add('has-error');
        }
        
        // Display errors if any
        if (errors.length > 0) {
          validationErrorList.innerHTML = errors.map(error => `<li>${error}</li>`).join('');
          validationError.classList.add('show');
          
          // Scroll to top to show error message
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
          
          // Shake the first error field
          const firstErrorGroup = document.querySelector('.form-group.has-error');
          if (firstErrorGroup) {
            firstErrorGroup.classList.add('error-shake');
            setTimeout(() => firstErrorGroup.classList.remove('error-shake'), 400);
          }
          
          return false;
        }
        
        validationError.classList.remove('show');
        return true;
      }

      // Modal functionality
      const modal = document.getElementById('confirmationModal');
      const reviewButton = document.getElementById('reviewButton');
      const backButton = document.getElementById('backButton');
      const confirmSubmit = document.getElementById('confirmSubmit');
      const form = document.getElementById('predictionForm');

      // Review button click - validate and show modal
      reviewButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
          return;
        }

        // Populate confirmation modal
        const studentName = document.getElementById('student_name').value;
        const programChoice = document.getElementById('program_choice').value;
        const shsStrand = document.getElementById('SHS_Strand').value;
        const shsGPA = document.getElementById('SHS_GPA').value;
        const sex = document.getElementById('Sex').value;

        // Update confirmation values
        const nameValueEl = document.getElementById('confirmNameValue');
        if (studentName) {
          nameValueEl.textContent = studentName;
          nameValueEl.classList.remove('empty');
        } else {
          nameValueEl.textContent = 'Not provided';
          nameValueEl.classList.add('empty');
        }

        document.getElementById('confirmProgramValue').textContent = programNames[programChoice] || programChoice;
        
        // Add warning indicator for non-applicable strands
        const confirmStrandValueEl = document.getElementById('confirmStrandValue');
        if (nonApplicableStrands.includes(shsStrand)) {
          confirmStrandValueEl.innerHTML = shsStrand + ' <span style="color: #f59e0b; font-size: 12px;">(⚠ Not Applicable)</span>';
        } else {
          confirmStrandValueEl.textContent = shsStrand;
        }
        
        document.getElementById('confirmGPAValue').textContent = shsGPA;
        document.getElementById('confirmSexValue').textContent = sex;

        // Show modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      });

      // Back button - close modal
      backButton.addEventListener('click', function() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      });

      // Close modal on overlay click
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          modal.classList.remove('active');
          document.body.style.overflow = '';
        }
      });

      // Confirm and submit
      confirmSubmit.addEventListener('click', function() {
        // Submit the form
        form.submit();
      });

      // ESC key to close modal
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
          modal.classList.remove('active');
          document.body.style.overflow = '';
        }
      });

      // Clear error state when user starts typing/selecting
      document.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('input', function() {
          const formGroup = this.closest('.form-group');
          if (formGroup) {
            formGroup.classList.remove('has-error');
          }
          
          // Hide validation error banner if all errors are cleared
          const hasErrors = document.querySelectorAll('.form-group.has-error').length > 0;
          if (!hasErrors) {
            document.getElementById('validationError').classList.remove('show');
          }
        });
      });
    </script>
  </body>
  </html>