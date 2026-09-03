<?php
// predict.php
require_once 'functions.php';
require_once 'config.php';

try {
    $model = load_model(__DIR__ . '/../model/model.json');
} catch (Exception $e) {
    die("Model load error: " . $e->getMessage());
}

// Collect POST inputs - Only the fields we need
$input = [
    'student_name' => $_POST['student_name'] ?? '',
    'program_choice' => $_POST['program_choice'] ?? '',
    'Sex' => $_POST['Sex'] ?? '',
    'SHS_GPA' => $_POST['SHS_GPA'] ?? '',
    'SHS_Strand' => $_POST['SHS_Strand'] ?? ''
];

// Program names mapping
$programNames = [
    'BSCS' => 'Bachelor of Science in Computer Science',
    'BSIT' => 'Bachelor of Science in Information Technology',
    'BSIS' => 'Bachelor of Science in Information Systems',
    'BSCpE' => 'Bachelor of Science in Computer Engineering',
    'BSECE' => 'Bachelor of Science in Electronics Engineering'
];

// Use the simplified prediction functions
$prob = predict_prob($model, $input);
$class = classify($model, $input);
$probPercent = round($prob * 100, 2);

// Save to database - FIXED: Use 'program' instead of 'program_choice'
$predictionData = [
    'student_name' => $input['student_name'],
    'program' => $input['program_choice'], // Changed from 'program_choice' to 'program'
    'sex' => $input['Sex'],
    'shs_gpa' => floatval($input['SHS_GPA']),
    'shs_strand' => $input['SHS_Strand'],
    'prediction_result' => $class,
    'probability' => $probPercent
];

savePrediction($predictionData);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediction Results - Gradetrack</title>
    <link rel="stylesheet" href="predict.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #ffffff; padding: 0;">
  <!-- Navigation Panel -->
  <nav class="top-nav">
      <div class="nav-container">
          <div class="nav-brand">
              <i class="fas fa-graduation-cap"></i>
              <span>Gradetrack</span>
          </div>
      </div>
  </nav>

  <!-- Result Container -->
  <div class="result-container">
    <div class="result-header">
      <div class="icon-success <?php echo $class == 1 ? 'success' : 'warning'; ?>">
        <i class="fas <?php echo $class == 1 ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
      </div>
      <h1>Prediction Results</h1>
      <?php if (!empty($input['student_name'])): ?>
        <p class="student-name-header"><?php echo htmlspecialchars($input['student_name']); ?></p>
      <?php endif; ?>
      <p class="result-subtitle">Based on AI analysis of your academic profile</p>
    </div>

    <!-- Probability Circle -->
    <div class="probability-circle-container">
      <svg class="probability-circle" viewBox="0 0 200 200">
        <defs>
          <linearGradient id="successGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
          </linearGradient>
          <linearGradient id="warningGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
          </linearGradient>
        </defs>
        <circle class="circle-bg" cx="100" cy="100" r="85"></circle>
        <circle class="circle-progress <?php echo $class == 1 ? 'success' : 'warning'; ?>" 
                cx="100" cy="100" r="85" 
                style="--progress: <?php echo $probPercent; ?>"></circle>
      </svg>
      <div class="probability-text">
        <div class="probability-number"><?php echo $probPercent; ?>%</div>
        <div class="probability-label">Graduation Likelihood</div>
      </div>
    </div>

    <!-- Result Card -->
    <div class="result-card <?php echo $class == 1 ? 'card-success' : 'card-warning'; ?>">
      <div class="result-icon">
        <i class="fas <?php echo $class == 1 ? 'fa-graduation-cap' : 'fa-chart-line'; ?>"></i>
      </div>
      <h2 class="result-title">
        <?php echo $class == 1 ? 'Likely to Graduate' : 'Unlikely to Graduate'; ?>
      </h2>
      <p class="result-description">
        <?php if ($class == 1): ?>
          Based on your academic profile, you have a strong chance of graduating successfully from <?php echo htmlspecialchars($programNames[$input['program_choice']] ?? $input['program_choice']); ?>. 
        <?php else: ?>
          Your current profile suggests challenges ahead for <?php echo htmlspecialchars($programNames[$input['program_choice']] ?? $input['program_choice']); ?>.
        <?php endif; ?>
      </p>
    </div>

    <!-- Input Summary -->
    <div class="input-summary">
      <h3><i class="fas fa-clipboard-list"></i> Your Profile Summary</h3>
      <div class="summary-grid">
        <?php if (!empty($input['student_name'])): ?>
        <div class="summary-item full-width-summary">
          <span class="summary-label"><i class="fas fa-user-circle"></i> Name</span>
          <span class="summary-value"><?php echo htmlspecialchars($input['student_name']); ?></span>
        </div>
        <?php endif; ?>
        <div class="summary-item full-width-summary">
          <span class="summary-label"><i class="fas fa-graduation-cap"></i> Program</span>
          <span class="summary-value"><?php echo htmlspecialchars($programNames[$input['program_choice']] ?? $input['program_choice']); ?></span>
        </div>
        <div class="summary-item">
          <span class="summary-label"><i class="fas fa-chart-line"></i> SHS GPA</span>
          <span class="summary-value"><?php echo htmlspecialchars($input['SHS_GPA']); ?></span>
        </div>
        <div class="summary-item">
          <span class="summary-label"><i class="fas fa-venus-mars"></i> Sex</span>
          <span class="summary-value"><?php echo htmlspecialchars($input['Sex']); ?></span>
        </div>
        <div class="summary-item full-width-summary">
          <span class="summary-label"><i class="fas fa-book"></i> SHS Strand</span>
          <span class="summary-value"><?php echo htmlspecialchars($input['SHS_Strand']); ?></span>
        </div>
      </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
      <a href="index.php" class="btn btn-primary">
        <i class="fas fa-redo"></i>
        <span>Predict Again</span>
      </a>
      <a href="dashboard.php" class="btn btn-secondary">
        <i class="fas fa-chart-line"></i>
        <span>View Dashboard</span>
      </a>
      <a href="records.php" class="btn btn-secondary">
        <i class="fas fa-table"></i>
        <span>View Records</span>
      </a>
    </div>
  </div>

  <script>
    // Animate probability circle on load
    window.addEventListener('load', function() {
      const circle = document.querySelector('.circle-progress');
      if (circle) {
        circle.style.strokeDashoffset = '534';
        setTimeout(() => {
          circle.style.transition = 'stroke-dashoffset 2s ease-out';
          const progress = <?php echo $probPercent; ?>;
          const offset = 534 - (534 * progress / 100);
          circle.style.strokeDashoffset = offset;
        }, 100);
      }
    });

    // Add entrance animations
    document.addEventListener('DOMContentLoaded', function() {
      const elements = document.querySelectorAll('.result-card, .input-summary, .action-buttons');
      elements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        setTimeout(() => {
          el.style.transition = 'all 0.6s ease';
          el.style.opacity = '1';
          el.style.transform = 'translateY(0)';
        }, 500 + (index * 150));
      });
    });
  </script>
</body>
</html>