<?php
// config.php - Database Configuration (Simplified for 3-feature model)

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change to your database username
define('DB_PASS', ''); // Change to your database password
define('DB_NAME', 'gradetrack_db');

// Create database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Get single prediction by ID
function getPredictionById($id) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $stmt = $conn->prepare("SELECT * FROM predictions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $prediction = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $prediction;
}

// Update prediction
function updatePrediction($id, $data) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $stmt = $conn->prepare("UPDATE predictions SET student_name = ?, shs_gpa = ?, sex = ?, shs_strand = ?, program = ?, prediction_result = ?, probability = ? WHERE id = ?");
    
    $stmt->bind_param("sdsssidi", 
        $data['student_name'],
        $data['shs_gpa'],
        $data['sex'],
        $data['shs_strand'],
        $data['program'],
        $data['prediction_result'],
        $data['probability'],
        $id
    );
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Save prediction to database (simplified - only 3 features)
function savePrediction($data) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $stmt = $conn->prepare("INSERT INTO predictions (student_name, shs_gpa, sex, shs_strand, program, prediction_result, probability) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sdsssid", 
        $data['student_name'],
        $data['shs_gpa'],
        $data['sex'],
        $data['shs_strand'],
        $data['program'],
        $data['prediction_result'],
        $data['probability']
    );
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Get all predictions with filters
function getAllPredictions($limit = 100, $filters = []) {
    $conn = getDBConnection();
    if (!$conn) return [];
    
    $query = "SELECT * FROM predictions WHERE 1=1";
    $params = [];
    $types = "";
    
    // Search by student name
    if (!empty($filters['student_name'])) {
        $query .= " AND student_name LIKE ?";
        $params[] = '%' . $filters['student_name'] . '%';
        $types .= "s";
    }
    
    // Filter by program
    if (!empty($filters['program']) && $filters['program'] !== 'all') {
        $query .= " AND program = ?";
        $params[] = $filters['program'];
        $types .= "s";
    }
    
    // Filter by strand
    if (!empty($filters['strand']) && $filters['strand'] !== 'all') {
        $query .= " AND shs_strand = ?";
        $params[] = $filters['strand'];
        $types .= "s";
    }
    
    // Filter by result
    if (!empty($filters['result']) && $filters['result'] !== 'all') {
        $query .= " AND prediction_result = ?";
        $params[] = ($filters['result'] === 'likely') ? 1 : 0;
        $types .= "i";
    }
    
    // Filter by sex
    if (!empty($filters['sex']) && $filters['sex'] !== 'all') {
        $query .= " AND sex = ?";
        $params[] = $filters['sex'];
        $types .= "s";
    }
    
    // Filter by GPA range
    if (!empty($filters['min_gpa'])) {
        $query .= " AND shs_gpa >= ?";
        $params[] = $filters['min_gpa'];
        $types .= "d";
    }
    
    if (!empty($filters['max_gpa'])) {
        $query .= " AND shs_gpa <= ?";
        $params[] = $filters['max_gpa'];
        $types .= "d";
    }
    
    // Filter by date range
    if (!empty($filters['date_from'])) {
        $query .= " AND DATE(created_at) >= ?";
        $params[] = $filters['date_from'];
        $types .= "s";
    }
    
    if (!empty($filters['date_to'])) {
        $query .= " AND DATE(created_at) <= ?";
        $params[] = $filters['date_to'];
        $types .= "s";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $predictions = [];
    while ($row = $result->fetch_assoc()) {
        $predictions[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $predictions;
}

// Delete single prediction
function deletePrediction($id) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $stmt = $conn->prepare("DELETE FROM predictions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Delete all predictions
function deleteAllPredictions() {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $result = $conn->query("DELETE FROM predictions");
    $conn->close();
    
    return $result;
}

// Delete predictions by filter
function deleteFilteredPredictions($filters = []) {
    $conn = getDBConnection();
    if (!$conn) return false;
    
    $query = "DELETE FROM predictions WHERE 1=1";
    $params = [];
    $types = "";
    
    // Apply same filters as getAllPredictions
    if (!empty($filters['program']) && $filters['program'] !== 'all') {
        $query .= " AND program = ?";
        $params[] = $filters['program'];
        $types .= "s";
    }
    
    if (!empty($filters['strand']) && $filters['strand'] !== 'all') {
        $query .= " AND shs_strand = ?";
        $params[] = $filters['strand'];
        $types .= "s";
    }
    
    if (!empty($filters['result']) && $filters['result'] !== 'all') {
        $query .= " AND prediction_result = ?";
        $params[] = ($filters['result'] === 'likely') ? 1 : 0;
        $types .= "i";
    }
    
    if (!empty($filters['sex']) && $filters['sex'] !== 'all') {
        $query .= " AND sex = ?";
        $params[] = $filters['sex'];
        $types .= "s";
    }
    
    if (empty($params)) {
        // If no filters, don't allow deletion (safety measure)
        return false;
    }
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}

// Get analytics data
function getAnalytics() {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $analytics = [];
    
    // Total predictions
    $result = $conn->query("SELECT COUNT(*) as total FROM predictions");
    $analytics['total'] = $result->fetch_assoc()['total'];
    
    // Likely to graduate count
    $result = $conn->query("SELECT COUNT(*) as count FROM predictions WHERE prediction_result = 1");
    $analytics['likely_graduate'] = $result->fetch_assoc()['count'];
    
    // Needs improvement count
    $result = $conn->query("SELECT COUNT(*) as count FROM predictions WHERE prediction_result = 0");
    $analytics['needs_improvement'] = $result->fetch_assoc()['count'];
    
    // Average probability
    $result = $conn->query("SELECT AVG(probability) as avg_prob FROM predictions");
    $analytics['avg_probability'] = round($result->fetch_assoc()['avg_prob'], 2);
    
    // Predictions by program
    $result = $conn->query("SELECT program, COUNT(*) as count, AVG(probability) as avg_prob FROM predictions GROUP BY program ORDER BY count DESC");
    $analytics['by_program'] = [];
    while ($row = $result->fetch_assoc()) {
        $analytics['by_program'][] = $row;
    }
    
    // Predictions by strand
    $result = $conn->query("SELECT shs_strand, COUNT(*) as count, AVG(probability) as avg_prob FROM predictions GROUP BY shs_strand ORDER BY count DESC");
    $analytics['by_strand'] = [];
    while ($row = $result->fetch_assoc()) {
        $analytics['by_strand'][] = $row;
    }
    
    // Predictions by sex
    $result = $conn->query("SELECT sex, COUNT(*) as count, AVG(probability) as avg_prob FROM predictions GROUP BY sex");
    $analytics['by_sex'] = [];
    while ($row = $result->fetch_assoc()) {
        $analytics['by_sex'][] = $row;
    }
    
    // Predictions by GPA range
    $result = $conn->query("
        SELECT 
            CASE 
                WHEN shs_gpa < 75 THEN 'Below 75'
                WHEN shs_gpa >= 75 AND shs_gpa < 85 THEN '75-85'
                WHEN shs_gpa >= 85 AND shs_gpa < 90 THEN '85-90'
                ELSE '90+'
            END as gpa_range,
            COUNT(*) as count,
            AVG(probability) as avg_prob
        FROM predictions
        GROUP BY gpa_range
        ORDER BY 
            CASE gpa_range
                WHEN 'Below 75' THEN 1
                WHEN '75-85' THEN 2
                WHEN '85-90' THEN 3
                WHEN '90+' THEN 4
            END
    ");
    $analytics['by_gpa'] = [];
    while ($row = $result->fetch_assoc()) {
        $analytics['by_gpa'][] = $row;
    }
    
    // Recent predictions timeline (last 30 days)
    $result = $conn->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count,
            SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as likely,
            SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as needs_improvement
        FROM predictions
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $analytics['timeline'] = [];
    while ($row = $result->fetch_assoc()) {
        $analytics['timeline'][] = $row;
    }
    
    // Strand favorability analysis
    $result = $conn->query("
        SELECT 
            shs_strand,
            COUNT(*) as total,
            SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as graduated,
            AVG(probability) as avg_prob,
            CASE WHEN shs_strand IN ('STEM', 'TVL-ICT') THEN 'Favorable' ELSE 'Other' END as favorability
        FROM predictions
        GROUP BY shs_strand
        ORDER BY avg_prob DESC
    ");
    $analytics['strand_performance'] = [];
    while ($row = $result->fetch_assoc()) {
        $analytics['strand_performance'][] = $row;
    }
    
    $conn->close();
    return $analytics;
}
?>