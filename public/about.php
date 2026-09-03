<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Gradetrack Tool</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================
           ABOUT PAGE STYLES
           Consistent with Gradetrack design system
        ============================================ */

        :root {
            --primary: #0e2412;
            --primary-mid: #2d5a3a;
            --success: #10b981;
            --success-dark: #059669;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --info: #3b82f6;
            --text: #1a2e1d;
            --text-light: #6b7a6e;
            --bg-light: #f3f7f4;
            --border: #d1e0d6;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(180deg, #f8faf9 0%, #ffffff 100%);
            color: var(--text);
            padding-top: 90px;
            min-height: 100vh;
        }

        /* ---- Page Container ---- */
        .about-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 48px 32px 80px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ---- Hero Banner ---- */
        .about-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 60%, #3d7a4e 100%);
            border-radius: 24px;
            padding: 56px 48px;
            margin-bottom: 48px;
            display: flex;
            align-items: center;
            gap: 40px;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .about-hero::after {
            content: '';
            position: absolute;
            bottom: -80px; left: 30%;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(16,185,129,0.08);
        }

        .hero-icon {
            width: 96px;
            height: 96px;
            min-width: 96px;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--success);
            position: relative;
            z-index: 1;
        }

        .hero-text {
            position: relative;
            z-index: 1;
        }

        .hero-text h1 {
            font-size: 2.4rem;
            font-weight: 900;
            color: white;
            letter-spacing: -0.03em;
            line-height: 1.1;
            margin-bottom: 12px;
        }

        .hero-text h1 span { color: var(--success); }

        .hero-text p {
            font-size: 1.05rem;
            color: rgba(255,255,255,0.78);
            line-height: 1.65;
            max-width: 580px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16,185,129,0.2);
            border: 1px solid rgba(16,185,129,0.4);
            color: #6ee7b7;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 14px;
            text-transform: uppercase;
        }

        /* ---- Section Title ---- */
        .section-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.02em;
        }

        .section-title i {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--primary-mid));
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* ---- Card Base ---- */
        .card {
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(209, 224, 214, 0.5);
            box-shadow: 0 2px 8px rgba(14, 36, 18, 0.06);
            padding: 32px;
            margin-bottom: 28px;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 24px rgba(14, 36, 18, 0.10);
            border-color: var(--border);
        }

        /* ---- Overview Grid ---- */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .overview-item {
            background: var(--bg-light);
            border-radius: 14px;
            padding: 24px 20px;
            text-align: center;
            transition: transform 0.25s ease, background 0.25s ease;
            border: 1px solid transparent;
        }

        .overview-item:hover {
            transform: translateY(-4px);
            background: white;
            border-color: var(--border);
            box-shadow: 0 6px 18px rgba(14, 36, 18, 0.08);
        }

        .overview-item .ov-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: white;
        }

        .ov-green  { background: linear-gradient(135deg, #10b981, #059669); }
        .ov-blue   { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .ov-amber  { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .ov-purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
        .ov-dark   { background: linear-gradient(135deg, #0e2412, #2d5a3a); }
        .ov-rose   { background: linear-gradient(135deg, #f43f5e, #be123c); }

        .overview-item h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
        }

        .overview-item p {
            font-size: 0.83rem;
            color: var(--text-light);
            line-height: 1.55;
        }

        /* ---- How It Works Steps ---- */
        .steps-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .step-item {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            padding: 20px 0;
            position: relative;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 20px;
            top: 64px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--border) 60%, transparent);
        }

        .step-num {
            width: 42px;
            height: 42px;
            min-width: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-mid));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 800;
            position: relative;
            z-index: 1;
        }

        .step-body h4 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 5px;
        }

        .step-body p {
            font-size: 0.88rem;
            color: var(--text-light);
            line-height: 1.6;
        }

        /* ---- Features Grid ---- */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            border-radius: 14px;
            background: var(--bg-light);
            border: 1px solid transparent;
            transition: all 0.25s ease;
        }

        .feature-item:hover {
            background: white;
            border-color: var(--border);
            box-shadow: 0 4px 14px rgba(14, 36, 18, 0.07);
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-mid));
        }

        .feature-icon.alt { background: linear-gradient(135deg, var(--success), var(--success-dark)); }
        .feature-icon.inf { background: linear-gradient(135deg, var(--info), #1d4ed8); }
        .feature-icon.wrn { background: linear-gradient(135deg, var(--warning), var(--warning-dark)); }

        .feature-item h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .feature-item p {
            font-size: 0.83rem;
            color: var(--text-light);
            line-height: 1.55;
        }

        /* ---- Input Variables Table ---- */
        .input-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .input-table thead th {
            background: var(--bg-light);
            padding: 12px 16px;
            text-align: left;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-table thead th:first-child { border-radius: 10px 0 0 10px; }
        .input-table thead th:last-child  { border-radius: 0 10px 10px 0; }

        .input-table tbody tr {
            border-bottom: 1px solid rgba(209, 224, 214, 0.4);
            transition: background 0.2s ease;
        }

        .input-table tbody tr:last-child { border-bottom: none; }
        .input-table tbody tr:hover { background: var(--bg-light); }

        .input-table tbody td {
            padding: 13px 16px;
            color: var(--text);
            vertical-align: middle;
        }

        .input-table tbody td:first-child { font-weight: 700; color: var(--primary-mid); }

        .tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .tag-numeric { background: rgba(59,130,246,0.1); color: #1d4ed8; }
        .tag-categorical { background: rgba(139,92,246,0.1); color: #6d28d9; }
        .tag-binary { background: rgba(16,185,129,0.1); color: #059669; }

        /* ---- Accuracy / Model Info ---- */
        .model-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .model-stat {
            text-align: center;
            padding: 20px 16px;
            background: var(--bg-light);
            border-radius: 14px;
            border: 1px solid transparent;
            transition: all 0.25s ease;
        }

        .model-stat:hover {
            background: white;
            border-color: var(--border);
            box-shadow: 0 4px 14px rgba(14, 36, 18, 0.07);
        }

        .model-stat-value {
            font-size: 1.9rem;
            font-weight: 900;
            color: var(--primary);
            letter-spacing: -0.03em;
            line-height: 1;
            margin-bottom: 6px;
        }

        .model-stat-value.green { color: var(--success-dark); }
        .model-stat-value.blue  { color: #1d4ed8; }

        .model-stat-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .model-description {
            font-size: 0.92rem;
            color: var(--text-light);
            line-height: 1.7;
        }

        .model-description strong { color: var(--text); font-weight: 700; }

        /* ---- Disclaimer ---- */
        .disclaimer-card {
            background: rgba(245, 158, 11, 0.06);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-left: 4px solid var(--warning);
            border-radius: 14px;
            padding: 20px 24px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .disclaimer-card i {
            color: var(--warning);
            font-size: 1.3rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .disclaimer-card p {
            font-size: 0.88rem;
            color: #78580a;
            line-height: 1.65;
        }

        .disclaimer-card p strong { font-weight: 700; color: #5a4000; }

        /* ---- Version Footer ---- */
        .about-footer {
            text-align: center;
            padding: 32px 0 0;
            border-top: 2px solid var(--bg-light);
            margin-top: 40px;
        }

        .about-footer p {
            font-size: 0.85rem;
            color: var(--text-light);
            line-height: 1.7;
        }

        .about-footer strong { color: var(--text); }

        /* ---- Responsive ---- */
        @media (max-width: 900px) {
            .overview-grid { grid-template-columns: repeat(2, 1fr); }
            .model-stats    { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .about-hero { flex-direction: column; gap: 24px; padding: 36px 28px; }
            .hero-text h1 { font-size: 1.75rem; }
            .overview-grid { grid-template-columns: 1fr; }
            .features-grid { grid-template-columns: 1fr; }
            .model-stats   { grid-template-columns: repeat(2, 1fr); }
            .about-container { padding: 32px 16px 60px; }
            .card { padding: 24px; }
        }

        @media (max-width: 400px) {
            .model-stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

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
                <a href="index.php" class="nav-link">
                    <i class="fas fa-brain"></i>
                    <span>Single</span>
                </a>
                <a href="bulk_predict.php" class="nav-link">
                    <i class="fas fa-file-upload"></i>
                    <span>Bulk</span>
                </a>
                <a href="about.php" class="nav-link active">
                    <i class="fas fa-info-circle"></i>
                    <span>About</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="about-container">

        <!-- Hero Banner -->
        <div class="about-hero">
            <div class="hero-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="hero-text">
                <div class="hero-badge">
                    <i class="fas fa-circle" style="font-size:0.5rem;"></i>
                    Predictive Analytics Tool
                </div>
                <h1>About <span>Gradetrack</span> Tool</h1>
                <p>A machine-learning–powered system designed to forecast student graduation likelihood based on academic records, demographic factors, and program enrollment—helping educators act early and with confidence.</p>
            </div>
        </div>

        <!-- At a Glance -->
        <div class="">
            <div class="section-title">
                <i class="fas fa-eye"></i>
                At a Glance
            </div>
            <div class="overview-grid">
                <div class="overview-item">
                    <div class="ov-icon ov-green"><i class="fas fa-brain"></i></div>
                    <h4>ML-Powered Predictions</h4>
                    <p>Uses a trained classification model to assess each student's graduation probability.</p>
                </div>
                <div class="overview-item">
                    <div class="ov-icon ov-blue"><i class="fas fa-file-upload"></i></div>
                    <h4>Bulk &amp; Single Mode</h4>
                    <p>Predict for one student at a time or upload a CSV to process an entire cohort.</p>
                </div>
                <div class="overview-item">
                    <div class="ov-icon ov-amber"><i class="fas fa-chart-pie"></i></div>
                    <h4>Interactive Dashboard</h4>
                    <p>Visualize trends across programs, GPA ranges, strands, and student demographics.</p>
                </div>
                <div class="overview-item">
                    <div class="ov-icon ov-purple"><i class="fas fa-history"></i></div>
                    <h4>Persistent Records</h4>
                    <p>Every prediction is stored and browsable in the Records view for future reference.</p>
                </div>
                <div class="overview-item">
                    <div class="ov-icon ov-dark"><i class="fas fa-shield-alt"></i></div>
                    <h4>Data-Driven Insights</h4>
                    <p>Analytics are updated in real time so advisors always work with the latest picture.</p>
                </div>
                <div class="overview-item">
                    <div class="ov-icon ov-rose"><i class="fas fa-user-graduate"></i></div>
                    <h4>Student-Centric Design</h4>
                    <p>Outputs are framed for actionable guidance, not just raw numbers.</p>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="card">
            <div class="section-title">
                <i class="fas fa-cogs"></i>
                How It Works
            </div>
            <div class="steps-list">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <h4>Data Entry</h4>
                        <p>Academic counselors or administrators enter student information—either individually through the Single Prediction form, or in batch via a structured CSV file upload in Bulk mode.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <h4>Feature Encoding &amp; Preprocessing</h4>
                        <p>Raw inputs (GPA, strand, program, sex, etc.) are encoded and normalized to match the format expected by the trained model, ensuring consistent and reliable evaluation.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <h4>Machine Learning Inference</h4>
                        <p>The preprocessed features are passed to the classification model hosted via a Python API endpoint. The model outputs a probability score (0–100%) and a binary prediction label.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">4</div>
                    <div class="step-body">
                        <h4>Result Storage &amp; Display</h4>
                        <p>Results are persisted in the database alongside the original inputs. The outcome is displayed immediately and becomes part of the aggregated analytics on the Dashboard.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">5</div>
                    <div class="step-body">
                        <h4>Advisor Action</h4>
                        <p>Students flagged as "Unlikely to Graduate" can be identified through the Records table or Dashboard charts, enabling targeted academic intervention and support planning.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Variables -->
        <div class="card">
            <div class="section-title">
                <i class="fas fa-list-alt"></i>
                Input Variables
            </div>
            <p style="font-size:0.9rem;color:var(--text-light);margin-bottom:20px;line-height:1.6;">
                The following student attributes are collected and used as features for prediction. Each variable contributes to the model's assessment of graduation likelihood.
            </p>
            <table class="input-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Example Values</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>SHS GPA</td>
                        <td>Senior High School Grade Point Average</td>
                        <td><span class="tag tag-numeric">Numeric</span></td>
                        <td>75.0 – 100.0</td>
                    </tr>
                    <tr>
                        <td>SHS Strand</td>
                        <td>Academic track taken during Senior High School</td>
                        <td><span class="tag tag-categorical">Categorical</span></td>
                        <td>STEM, ABM, HUMSS, TVL, GAS</td>
                    </tr>
                    <tr>
                        <td>Program</td>
                        <td>College program / degree the student is enrolled in</td>
                        <td><span class="tag tag-categorical">Categorical</span></td>
                        <td>BSCS, BSIT, BSEd, BSN, BSBA…</td>
                    </tr>
                    <tr>
                        <td>Sex</td>
                        <td>Student's biological sex</td>
                        <td><span class="tag tag-binary">Binary</span></td>
                        <td>Male / Female</td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td>Student's full name</td>
                        <td><span class="tag tag-numeric">None</span></td>
                        <td>Rabara Charles, J. </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Model Information -->
        <div class="card">
            <div class="section-title">
                <i class="fas fa-microchip"></i>
                Model Information
            </div>
            <div class="model-stats">
                <div class="model-stat">
                    <div class="model-stat-value green">Binary</div>
                    <div class="model-stat-label">Output Type</div>
                </div>
                <div class="model-stat">
                    <div class="model-stat-value blue">0 – 100%</div>
                    <div class="model-stat-label">Probability Range</div>
                </div>
                <div class="model-stat">
                    <div class="model-stat-value">50%</div>
                    <div class="model-stat-label">Classification Threshold</div>
                </div>
                <div class="model-stat">
                    <div class="model-stat-value green">Supervised</div>
                    <div class="model-stat-label">Learning Type</div>
                </div>
            </div>
            <p class="model-description">
                Gradetrack uses a <strong>supervised machine learning classifier</strong> trained on historical student graduation records. The model accepts the six input features described above and returns a <strong>probability score</strong> between 0% and 100%, where values above 50% are classified as <strong>"Likely to Graduate"</strong> and values at or below 50% are classified as <strong>"Unlikely to Graduate."</strong>
            </p>
        </div>

        <!-- Key Features -->
        <div class="card">
            <div class="section-title">
                <i class="fas fa-star"></i>
                Key Features
            </div>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-brain"></i></div>
                    <div>
                        <h4>Single Prediction</h4>
                        <p>Enter one student's data through a guided form and receive an instant graduation likelihood score with a clear outcome label.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon alt"><i class="fas fa-file-csv"></i></div>
                    <div>
                        <h4>Bulk CSV Upload</h4>
                        <p>Upload a properly formatted CSV file to predict outcomes for an entire class or batch, saving significant time for administrators.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon inf"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h4>Analytics Dashboard</h4>
                        <p>Interactive charts break down predictions by program, GPA range, SHS strand, and sex—with Likely vs. Unlikely breakdowns for each dimension.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon wrn"><i class="fas fa-table"></i></div>
                    <div>
                        <h4>Records Management</h4>
                        <p>A searchable, sortable table of all past predictions allows advisors to track students over time and review historical decisions.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-percentage"></i></div>
                    <div>
                        <h4>Probability Scoring</h4>
                        <p>Every prediction includes a continuous probability score, giving advisors nuance beyond a simple yes/no classification.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon alt"><i class="fas fa-database"></i></div>
                    <div>
                        <h4>Persistent Storage</h4>
                        <p>All predictions are stored in a relational database, ensuring data is retained across sessions and available for long-term trend analysis.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disclaimer -->
        <div class="disclaimer-card">
            <i class="fas fa-exclamation-triangle"></i>
            <p>
                <strong>Important Notice:</strong> Gradetrack Tool is a <strong>decision-support tool</strong>, not a definitive judgment system. Predictions are probabilistic estimates based on historical patterns and should be used alongside professional academic advising, counselor observations, and student circumstances. No automated system can fully account for individual motivations, personal challenges, or life events. Always involve qualified educators when making academic interventions.
            </p>
        </div>

        <!-- Footer -->
        <div class="about-footer">
            <p>
                <strong>Gradetrack Tool</strong> &nbsp;·&nbsp; Version 1.0 &nbsp;·&nbsp; Built with PHP, MySQL, Chart.js &amp; Python ML Backend<br>
                Designed to support academic advisors in making timely, data-informed interventions.
            </p>
        </div>

    </div>
</body>
</html>