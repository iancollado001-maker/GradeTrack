<?php
// dashboard.php
require_once 'config.php';

$analytics = getAnalytics();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gradetrack Predictor</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
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
                <a href="dashboard.php" class="nav-link active">
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
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($analytics['total'] ?? 0); ?></div>
                    <div class="stat-label">Total Predictions</div>
                </div>
            </div>

            <div class="stat-card stat-success">
                <div class="stat-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($analytics['likely_graduate'] ?? 0); ?></div>
                    <div class="stat-label">Likely to Graduate</div>
                    <div class="stat-percentage">
                        <?php 
                        $percentage = $analytics['total'] > 0 ? round(($analytics['likely_graduate'] / $analytics['total']) * 100, 1) : 0;
                        echo $percentage . '%';
                        ?>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-warning">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($analytics['needs_improvement'] ?? 0); ?></div>
                    <div class="stat-label">Unlikely to Graduate</div>
                    <div class="stat-percentage">
                        <?php 
                        $percentage = $analytics['total'] > 0 ? round(($analytics['needs_improvement'] / $analytics['total']) * 100, 1) : 0;
                        echo $percentage . '%';
                        ?>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($analytics['avg_probability'] ?? 0, 1); ?>%</div>
                    <div class="stat-label">Average Probability</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-grid">
            <!-- Prediction Distribution Pie Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-pie"></i> Prediction Distribution</h3>
                </div>
                <div class="chart-container chart-pie">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>

            <!-- Predictions by Program -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-bar"></i> Predictions by Program</h3>
                </div>

                <div class="chart-container">
                    <canvas id="programChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Additional Charts Row - 3 Columns -->
        <div class="charts-grid charts-grid-three">
            <!-- GPA Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-bar"></i> GPA Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="gpaChart"></canvas>
                </div>
            </div>

            <!-- Predictions by Strand -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-line"></i> Predictions by Strand</h3>
                </div>
                <div class="chart-container">
                    <canvas id="strandChart"></canvas>
                </div>
            </div>

            <!-- Predictions by Sex -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-venus-mars"></i> Predictions by Sex</h3>
                </div>
                <div class="chart-container">
                    <canvas id="sexChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart.js configurations
        const chartColors = {
            primary: '#0e2412',
            secondary: '#2d5a3a',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6'
        };

        let programChartInstance = null;

        // Distribution Pie Chart
        const distributionCtx = document.getElementById('distributionChart');
        if (distributionCtx) {
            new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Likely to Graduate', 'Unlikely to Graduate'],
                    datasets: [{
                        data: [
                            <?php echo $analytics['likely_graduate'] ?? 0; ?>,
                            <?php echo $analytics['needs_improvement'] ?? 0; ?>
                        ],
                        backgroundColor: [chartColors.success, chartColors.warning],
                        borderWidth: 3,
                        borderColor: '#ffffff',
                        hoverBorderWidth: 4,
                        hoverBorderColor: '#ffffff',
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 1.4,
                    layout: { padding: 10 },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 16,
                                font: { size: 13, weight: '600', family: 'Inter, sans-serif' },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 12,
                                boxHeight: 12
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(14, 36, 18, 0.95)',
                            padding: 12,
                            titleFont: { size: 14, weight: '600' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '35%',
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 800,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // Initialize Program Chart with Likely/Unlikely breakdown
        function initProgramChart(data) {
            const programCtx = document.getElementById('programChart');
            if (!programCtx) return;

            if (programChartInstance) {
                programChartInstance.destroy();
            }

            programChartInstance = new Chart(programCtx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Likely to Graduate',
                        data: data.likely,
                        backgroundColor: chartColors.success,
                        borderRadius: 8
                    }, {
                        label: 'Unlikely to Graduate',
                        data: data.unlikely,
                        backgroundColor: chartColors.warning,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: {
                                padding: 12,
                                font: { size: 12, weight: '600' },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(14, 36, 18, 0.95)',
                            padding: 12,
                            titleFont: { size: 14, weight: '600' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                            callbacks: {
                                afterBody: function(context) {
                                    const dataIndex = context[0].dataIndex;
                                    const likely = data.likely[dataIndex];
                                    const unlikely = data.unlikely[dataIndex];
                                    const total = likely + unlikely;
                                    return `\nTotal: ${total}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // Load initial chart data
        const programData = <?php 
            $programData = ['labels' => [], 'likely' => [], 'unlikely' => []];
            if (!empty($analytics['by_program'])) {
                foreach ($analytics['by_program'] as $prog) {
                    $programData['labels'][] = $prog['program'];
                    
                    // Get likely/unlikely counts for this program
                    $conn = getDBConnection();
                    $stmt = $conn->prepare("SELECT 
                        SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as likely,
                        SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as unlikely
                        FROM predictions WHERE program = ?");
                    $stmt->bind_param("s", $prog['program']);
                    $stmt->execute();
                    $result = $stmt->get_result()->fetch_assoc();
                    $programData['likely'][] = $result['likely'] ?? 0;
                    $programData['unlikely'][] = $result['unlikely'] ?? 0;
                    $stmt->close();
                    $conn->close();
                }
            }
            echo json_encode($programData);
        ?>;
        initProgramChart(programData);

        // GPA Distribution Chart with Likely/Unlikely breakdown
        const gpaCtx = document.getElementById('gpaChart');
        if (gpaCtx) {
            const gpaData = <?php
                $gpaData = ['labels' => [], 'likely' => [], 'unlikely' => []];
                if (!empty($analytics['by_gpa'])) {
                    foreach ($analytics['by_gpa'] as $gpa) {
                        $gpaData['labels'][] = $gpa['gpa_range'];
                        
                        // Get likely/unlikely counts for this GPA range
                        $conn = getDBConnection();
                        $rangeCondition = "";
                        switch($gpa['gpa_range']) {
                            case 'Below 75':
                                $rangeCondition = "shs_gpa < 75";
                                break;
                            case '75-85':
                                $rangeCondition = "shs_gpa >= 75 AND shs_gpa < 85";
                                break;
                            case '85-90':
                                $rangeCondition = "shs_gpa >= 85 AND shs_gpa < 90";
                                break;
                            case '90+':
                                $rangeCondition = "shs_gpa >= 90";
                                break;
                        }
                        
                        $query = "SELECT 
                            SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as likely,
                            SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as unlikely
                            FROM predictions WHERE $rangeCondition";
                        $result = $conn->query($query)->fetch_assoc();
                        $gpaData['likely'][] = $result['likely'] ?? 0;
                        $gpaData['unlikely'][] = $result['unlikely'] ?? 0;
                        $conn->close();
                    }
                }
                echo json_encode($gpaData);
            ?>;

            new Chart(gpaCtx, {
                type: 'bar',
                data: {
                    labels: gpaData.labels,
                    datasets: [{
                        label: 'Likely to Graduate',
                        data: gpaData.likely,
                        backgroundColor: chartColors.success,
                        borderRadius: 8
                    }, {
                        label: 'Unlikely to Graduate',
                        data: gpaData.unlikely,
                        backgroundColor: chartColors.warning,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: {
                                padding: 12,
                                font: { size: 12, weight: '600' },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(14, 36, 18, 0.95)',
                            padding: 12,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // Strand Chart with Likely/Unlikely breakdown
        const strandCtx = document.getElementById('strandChart');
        if (strandCtx) {
            const strandData = <?php
                $strandData = ['labels' => [], 'likely' => [], 'unlikely' => []];
                if (!empty($analytics['by_strand'])) {
                    foreach ($analytics['by_strand'] as $strand) {
                        $strandData['labels'][] = $strand['shs_strand'];
                        
                        // Get likely/unlikely counts for this strand
                        $conn = getDBConnection();
                        $stmt = $conn->prepare("SELECT 
                            SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as likely,
                            SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as unlikely
                            FROM predictions WHERE shs_strand = ?");
                        $stmt->bind_param("s", $strand['shs_strand']);
                        $stmt->execute();
                        $result = $stmt->get_result()->fetch_assoc();
                        $strandData['likely'][] = $result['likely'] ?? 0;
                        $strandData['unlikely'][] = $result['unlikely'] ?? 0;
                        $stmt->close();
                        $conn->close();
                    }
                }
                echo json_encode($strandData);
            ?>;

            new Chart(strandCtx, {
                type: 'bar',
                data: {
                    labels: strandData.labels,
                    datasets: [{
                        label: 'Likely to Graduate',
                        data: strandData.likely,
                        backgroundColor: chartColors.success,
                        borderRadius: 8
                    }, {
                        label: 'Unlikely to Graduate',
                        data: strandData.unlikely,
                        backgroundColor: chartColors.warning,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: {
                                padding: 12,
                                font: { size: 12, weight: '600' },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(14, 36, 18, 0.95)',
                            padding: 12,
                            titleFont: { size: 14, weight: '600' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // Sex Chart with Likely/Unlikely breakdown
        const sexCtx = document.getElementById('sexChart');
        if (sexCtx) {
            const sexData = <?php
                $sexData = ['labels' => [], 'likely' => [], 'unlikely' => []];
                if (!empty($analytics['by_sex'])) {
                    foreach ($analytics['by_sex'] as $sex) {
                        $sexData['labels'][] = $sex['sex'];
                        
                        // Get likely/unlikely counts for this sex
                        $conn = getDBConnection();
                        $stmt = $conn->prepare("SELECT 
                            SUM(CASE WHEN prediction_result = 1 THEN 1 ELSE 0 END) as likely,
                            SUM(CASE WHEN prediction_result = 0 THEN 1 ELSE 0 END) as unlikely
                            FROM predictions WHERE sex = ?");
                        $stmt->bind_param("s", $sex['sex']);
                        $stmt->execute();
                        $result = $stmt->get_result()->fetch_assoc();
                        $sexData['likely'][] = $result['likely'] ?? 0;
                        $sexData['unlikely'][] = $result['unlikely'] ?? 0;
                        $stmt->close();
                        $conn->close();
                    }
                }
                echo json_encode($sexData);
            ?>;

            new Chart(sexCtx, {
                type: 'bar',
                data: {
                    labels: sexData.labels,
                    datasets: [{
                        label: 'Likely to Graduate',
                        data: sexData.likely,
                        backgroundColor: chartColors.success,
                        borderRadius: 8
                    }, {
                        label: 'Unlikely to Graduate',
                        data: sexData.unlikely,
                        backgroundColor: chartColors.warning,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: {
                                padding: 12,
                                font: { size: 12, weight: '600' },
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(14, 36, 18, 0.95)',
                            padding: 12,
                            titleFont: { size: 14, weight: '600' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>