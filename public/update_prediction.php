<?php
// update_prediction.php - Handle prediction updates
require_once 'functions.php';
require_once 'config.php';

// Check if this is an update request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['edit_id'])) {
    header('Location: records.php');
    exit;
}

$editId = intval($_POST['edit_id']);

// Load model for re-prediction
try {
    $model = json_decode(file_get_contents(__DIR__ . '/../model/model.json'), true);
} catch (Exception $e) {
    die("Model load error: " . $e->getMessage());
}

// Collect POST inputs
$input = [
    'student_name' => $_POST['student_name'] ?? '',
    'program_choice' => $_POST['program_choice'] ?? '',
    'Sex' => $_POST['Sex'] ?? '',
    'SHS_GPA' => $_POST['SHS_GPA'] ?? '',
    'SHS_Strand' => $_POST['SHS_Strand'] ?? ''
];

// Re-calculate prediction with updated data
$prob = predict_prob($model, $input);
$class = classify($model, $input);
$probPercent = round($prob * 100, 2);

// Prepare update data
$updateData = [
    'student_name' => $input['student_name'],
    'program' => $input['program_choice'],
    'sex' => $input['Sex'],
    'shs_gpa' => floatval($input['SHS_GPA']),
    'shs_strand' => $input['SHS_Strand'],
    'prediction_result' => $class,
    'probability' => $probPercent
];

// Update the prediction in database
$success = updatePrediction($editId, $updateData);

if ($success) {
    // Redirect back to records with success message
    header('Location: records.php?updated=1');
} else {
    // Redirect back to records with error message
    header('Location: records.php?error=1');
}
exit;
?>