<?php
// functions.php

function load_model($path) {
    if (!file_exists($path)) {
        throw new Exception("Model JSON not found: $path");
    }
    $json = file_get_contents($path);
    return json_decode($json, true);
}

function preprocess_inputs($input) {
    // Extract raw values from input
    $shs_gpa = isset($input['SHS_GPA']) ? floatval($input['SHS_GPA']) : 0.0;
    
    // Sex: Male=1, Female=0
    $sex = 0;
    if (isset($input['Sex'])) {
        $sex = ($input['Sex'] === 'Male' || $input['Sex'] === 1) ? 1 : 0;
    }
    
    // Strand_Favorable: STEM or TVL-ICT = 1, others = 0
    $strand_favorable = 0;
    if (isset($input['SHS_Strand'])) {
        $strand_favorable = in_array($input['SHS_Strand'], ['STEM', 'TVL-ICT']) ? 1 : 0;
    }
    
    return [
        'SHS_GPA' => $shs_gpa,
        'Sex' => $sex,
        'Strand_Favorable' => $strand_favorable
    ];
}

function predict_prob($model, $input) {
    // Preprocess inputs
    $features = preprocess_inputs($input);
    
    // Get coefficients
    $coef = $model['coefficients'];
    $intercept = floatval($model['intercept']);
    
    // Calculate linear combination (z)
    $z = $intercept;
    $z += $coef['SHS_GPA'] * $features['SHS_GPA'];
    $z += $coef['Sex'] * $features['Sex'];
    $z += $coef['Strand_Favorable'] * $features['Strand_Favorable'];
    
    // Apply sigmoid function
    $prob = 1.0 / (1.0 + exp(-$z));
    
    return $prob;
}

function classify($model, $input, $threshold = 0.5) {
    $prob = predict_prob($model, $input);
    return ($prob >= $threshold) ? 1 : 0;
}