<?php
/**
 * Database Setup Script - SIMPLIFIED VERSION WITH PROGRAM
 * Run this file ONCE to create/update the database and table
 * Access via: http://yoursite.com/setup.php
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Change if needed
define('DB_PASS', '');              // Change if needed
define('DB_NAME', 'gradetrack_db');

// HTML Header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gradetrack - Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0e2412 0%, #1a3d21 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 700px;
            width: 100%;
        }
        h1 {
            color: #0e2412;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .step {
            background: #f8faf9;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 4px solid #0e2412;
        }
        .step-title {
            font-weight: 600;
            color: #0e2412;
            margin-bottom: 8px;
        }
        .success {
            background: #d1fae5;
            border-left-color: #10b981;
        }
        .success .step-title {
            color: #059669;
        }
        .error {
            background: #fee2e2;
            border-left-color: #ef4444;
        }
        .error .step-title {
            color: #dc2626;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #0e2412, #2d5a3a);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #dc2626;
        }
        .warning {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .warning strong {
            color: #d97706;
        }
        .info-box {
            background: #dbeafe;
            border: 2px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .info-box strong {
            color: #1e40af;
        }
        ul {
            margin-top: 10px;
            margin-left: 20px;
        }
        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Gradetrack Setup</h1>
        <p class="subtitle">Database Installation & Update Wizard</p>

        <?php
        $errors = [];
        $success = [];
        $warnings = [];

        // Step 1: Connect to MySQL (without database)
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $success[] = "✓ Connected to MySQL server successfully";
            
            // Step 2: Create Database
            $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
            if ($conn->query($sql) === TRUE) {
                $success[] = "✓ Database '" . DB_NAME . "' created/verified successfully";
            } else {
                $errors[] = "Error creating database: " . $conn->error;
            }
            
            // Step 3: Select Database
            $conn->select_db(DB_NAME);
            $success[] = "✓ Selected database '" . DB_NAME . "'";
            
            // Step 4: Check if table exists
            $tableExists = false;
            
            $result = $conn->query("SHOW TABLES LIKE 'predictions'");
            if ($result && $result->num_rows > 0) {
                $tableExists = true;
                $success[] = "✓ Table 'predictions' exists";
                
                // Check for old columns that need to be removed
                $columnsToRemove = ['parent_income', 'age', 'academic_awards', 'highschool_type', 'program_choice'];
                foreach ($columnsToRemove as $col) {
                    $result = $conn->query("SHOW COLUMNS FROM predictions LIKE '$col'");
                    if ($result->num_rows > 0) {
                        $warnings[] = "⚠️ Found '$col' column - will be removed in update";
                    }
                }
                
                // Check if program column exists
                $result = $conn->query("SHOW COLUMNS FROM predictions LIKE 'program'");
                if ($result->num_rows == 0) {
                    $warnings[] = "⚠️ 'program' column missing - will be added";
                }
            }
            
            // Step 5: Create or Update Table
            if (!$tableExists) {
                // Create new simplified table with program column
                $sql = "CREATE TABLE predictions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_name VARCHAR(100) DEFAULT NULL,
                    shs_gpa DECIMAL(5,2) NOT NULL,
                    sex VARCHAR(10) NOT NULL,
                    shs_strand VARCHAR(50) NOT NULL,
                    program VARCHAR(50) DEFAULT NULL COMMENT 'BSIT, BSCS, BSECE, BSCpE, BSIS',
                    prediction_result TINYINT NOT NULL COMMENT '1 = Likely to Graduate, 0 = Unlikely to Graduate',
                    probability DECIMAL(5,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_created_at (created_at),
                    INDEX idx_prediction_result (prediction_result),
                    INDEX idx_program (program)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                if ($conn->query($sql) === TRUE) {
                    $success[] = "✓ Table 'predictions' created successfully with program column";
                } else {
                    $errors[] = "Error creating table: " . $conn->error;
                }
            } else {
                // Update existing table - remove unnecessary columns
                $columnsToRemove = ['parent_income', 'age', 'academic_awards', 'highschool_type', 'program_choice'];
                foreach ($columnsToRemove as $col) {
                    $result = $conn->query("SHOW COLUMNS FROM predictions LIKE '$col'");
                    if ($result->num_rows > 0) {
                        $sql = "ALTER TABLE predictions DROP COLUMN $col";
                        if ($conn->query($sql) === TRUE) {
                            $success[] = "✓ Removed '$col' column from table";
                        } else {
                            $errors[] = "Error removing $col: " . $conn->error;
                        }
                    }
                }
                
                // Add program column if it doesn't exist
                $result = $conn->query("SHOW COLUMNS FROM predictions LIKE 'program'");
                if ($result->num_rows == 0) {
                    $sql = "ALTER TABLE predictions ADD COLUMN program VARCHAR(50) DEFAULT NULL COMMENT 'BSIT, BSCS, BSECE, BSCpE, BSIS' AFTER shs_strand";
                    if ($conn->query($sql) === TRUE) {
                        $success[] = "✓ Added 'program' column to table";
                        
                        // Add index for program column
                        $sql = "ALTER TABLE predictions ADD INDEX idx_program (program)";
                        if ($conn->query($sql) === TRUE) {
                            $success[] = "✓ Added index for 'program' column";
                        }
                    } else {
                        $errors[] = "Error adding program column: " . $conn->error;
                    }
                }
                
                $success[] = "✓ Table structure is up to date";
            }
            
            // Step 6: Verify Table Structure
            $result = $conn->query("DESCRIBE predictions");
            if ($result && $result->num_rows > 0) {
                $success[] = "✓ Table structure verified (" . $result->num_rows . " columns)";
                
                // Display column info
                echo '<div class="info-box">';
                echo '<strong>📋 Current Table Structure:</strong><br>';
                echo '<ul style="margin-top: 10px;">';
                while ($row = $result->fetch_assoc()) {
                    echo '<li><code>' . $row['Field'] . '</code> - ' . $row['Type'];
                    if (!empty($row['Comment'])) {
                        echo ' <span style="color: #666;">(' . $row['Comment'] . ')</span>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            $conn->close();
            
        } catch (Exception $e) {
            $errors[] = "Connection Error: " . $e->getMessage();
        }
        ?>

        <?php if (!empty($errors)): ?>
            <div class="warning">
                <strong>⚠️ Important:</strong> Please update the database credentials at the top of this file:
                <br><code>DB_USER</code> and <code>DB_PASS</code>
            </div>
        <?php endif; ?>

        <?php foreach ($warnings as $msg): ?>
            <div class="step" style="background: #fef3c7; border-left-color: #f59e0b;">
                <div class="step-title" style="color: #d97706;"><?php echo $msg; ?></div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($success as $msg): ?>
            <div class="step success">
                <div class="step-title"><?php echo $msg; ?></div>
            </div>
        <?php endforeach; ?>

        <?php foreach ($errors as $error): ?>
            <div class="step error">
                <div class="step-title">✗ <?php echo $error; ?></div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($errors) && !empty($success)): ?>
            <div class="step success">
                <div class="step-title">🎉 Setup Complete!</div>
                <p style="margin-top: 10px; color: #059669;">
                    Your database is ready with the simplified model including program tracking:
                </p>
                <ul style="margin-top: 10px; margin-left: 20px; color: #059669;">
                    <li><strong>Student Name</strong> - Optional identifier</li>
                    <li><strong>SHS GPA</strong> - Senior High School Grade Point Average</li>
                    <li><strong>Sex</strong> - Male or Female</li>
                    <li><strong>SHS Strand</strong> - Senior High School strand (STEM, TVL-ICT, etc.)</li>
                    <li><strong>Program</strong> - BSIT, BSCS, BSECE, BSCpE, BSIS</li>
                    <li><strong>Prediction Result</strong> - Graduate likelihood</li>
                    <li><strong>Probability</strong> - Confidence score</li>
                </ul>
            </div>

            <div class="info-box" style="margin-top: 20px;">
                <strong>📚 Supported Programs:</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li><strong>BSIT</strong> - Bachelor of Science in Information Technology</li>
                    <li><strong>BSCS</strong> - Bachelor of Science in Computer Science</li>
                    <li><strong>BSECE</strong> - Bachelor of Science in Electronics and Communications Engineering</li>
                    <li><strong>BSCpE</strong> - Bachelor of Science in Computer Engineering</li>
                    <li><strong>BSIS</strong> - Bachelor of Science in Information Systems</li>
                </ul>
            </div>

            <div class="warning" style="margin-top: 20px;">
                <strong>🔒 Security:</strong> For security reasons, please delete this <code>setup.php</code> file after setup is complete!
            </div>

            <a href="index.php" class="btn">Go to Prediction Form →</a>
            <a href="dashboard.php" class="btn" style="margin-left: 10px;">View Dashboard →</a>
        <?php else: ?>
            <div class="step">
                <div class="step-title">Next Steps:</div>
                <ol style="margin-top: 10px; margin-left: 20px; color: #666;">
                    <li>Check your MySQL credentials in this file</li>
                    <li>Make sure MySQL server is running</li>
                    <li>Refresh this page to try again</li>
                </ol>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; color: #999; font-size: 0.9rem;">
            <strong>Database Info:</strong><br>
            Host: <?php echo DB_HOST; ?><br>
            Database: <?php echo DB_NAME; ?><br>
            User: <?php echo DB_USER; ?>
        </div>
    </div>
</body>
</html>