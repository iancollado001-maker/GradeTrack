<?php
// records.php - Enhanced with clickable rows, prediction details modal, print report, and EDIT functionality
require_once 'config.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                $result = deletePrediction($id);
                echo json_encode(['success' => $result]);
                exit;
                
            case 'delete_multiple':
                $ids = isset($_POST['ids']) ? json_decode($_POST['ids'], true) : [];
                $success = true;
                foreach ($ids as $id) {
                    $result = deletePrediction(intval($id));
                    if (!$result) {
                        $success = false;
                    }
                }
                echo json_encode(['success' => $success]);
                exit;
        }
    }
}

// Get filters from URL
$filters = [
    'strand' => isset($_GET['strand']) ? $_GET['strand'] : 'all',
    'program' => isset($_GET['program']) ? $_GET['program'] : 'all',
    'result' => isset($_GET['result']) ? $_GET['result'] : 'all',
    'sex' => isset($_GET['sex']) ? $_GET['sex'] : 'all',
    'student_name' => isset($_GET['student_name']) ? $_GET['student_name'] : '',
    'min_gpa' => isset($_GET['min_gpa']) ? $_GET['min_gpa'] : '',
    'max_gpa' => isset($_GET['max_gpa']) ? $_GET['max_gpa'] : '',
    'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
    'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : '',
    'name_sort' => isset($_GET['name_sort']) && in_array($_GET['name_sort'], ['asc', 'desc']) ? $_GET['name_sort'] : ''
];

// Check for update/error messages
$showUpdateSuccess = isset($_GET['updated']) && $_GET['updated'] == '1';
$showUpdateError = isset($_GET['error']) && $_GET['error'] == '1';

$recentPredictions = getAllPredictions(10000, $filters);

function hasActiveFilters($filters) {
    return ($filters['strand'] !== 'all' ||
            $filters['program'] !== 'all' ||
            $filters['result'] !== 'all' ||
            $filters['sex'] !== 'all' ||
            !empty($filters['student_name']) ||
            !empty($filters['min_gpa']) ||
            !empty($filters['max_gpa']) ||
            !empty($filters['date_from']) ||
            !empty($filters['date_to']));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediction Records - Gradetrack Predictor</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      :root {
        --primary: #0e2412;
        --secondary: #2d5a3a;
        --accent: #1cc88a;
        --bg-light: #f8faf9;
        --bg-white: #ffffff;
        --text: #1f2937;
        --text-light: #6b7280;
        --border: #e5e7eb;
        --success: #10b981;
        --success-dark: #059669;
        --danger: #ef4444;
        --danger-dark: #dc2626;
        --warning: #f59e0b;
        --warning-dark: #d97706;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: var(--bg-light);
        color: var(--text);
        min-height: 100vh;
        overflow-x: hidden;
      }

      /* Success/Error Toast Messages */
      .toast-message {
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: var(--shadow-xl);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10001;
        animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s;
        max-width: 400px;
      }

      @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
      }

      @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
      }

      .toast-success {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
      }

      .toast-error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
      }

      .toast-message i {
        font-size: 20px;
      }

      .toast-message-content {
        flex: 1;
      }

      .toast-message-content strong {
        display: block;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 2px;
      }

      .toast-message-content p {
        font-size: 13px;
        margin: 0;
        opacity: 0.95;
      }

      /* Two Column Layout */
      .two-column-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        min-height: calc(100vh - 60px);
        margin-top: 60px;
        gap: 0;
      }

      /* Left Sidebar - Filters */
      .left-sidebar {
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
        border-right: 1px solid var(--border);
        padding: 30px 25px;
        overflow-y: auto;
        position: sticky;
        top: 60px;
        height: calc(100vh - 60px);
      }

      .sidebar-header {
        margin-bottom: 25px;
      }

      .sidebar-header h2 {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
      }

      .sidebar-header h2 i {
        color: var(--accent);
      }

      .sidebar-header p {
        font-size: 0.8rem;
        color: var(--text-light);
        line-height: 1.5;
      }

      /* Filter Form */
      .filter-form .form-group {
        margin-bottom: 18px;
      }

      .filter-form label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text);
        font-size: 0.8rem;
      }

      .filter-form label i {
        color: var(--primary);
        font-size: 0.9rem;
      }

      .filter-form input,
      .filter-form select {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        background: var(--bg-white);
        font-family: inherit;
      }

      .filter-form input:focus,
      .filter-form select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(14, 36, 18, 0.1);
      }

      .select-wrapper {
        position: relative;
      }

      .select-wrapper select {
        appearance: none;
        padding-right: 35px;
        cursor: pointer;
      }

      .select-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
        pointer-events: none;
        font-size: 0.8rem;
      }

      .filter-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid var(--border);
      }

      .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-family: inherit;
        flex: 1;
        text-decoration: none;
      }

      .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        box-shadow: var(--shadow-lg);
      }

      .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-xl);
      }

      .btn-secondary {
        background: white;
        color: var(--text);
        border: 2px solid var(--border);
      }

      .btn-secondary:hover {
        border-color: var(--primary);
        color: var(--primary);
      }

      .active-filters-badge {
        background: rgba(28, 200, 138, 0.1);
        color: var(--accent);
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      /* Right Panel - Records Table */
      .right-panel {
        padding: 30px;
        overflow-y: auto;
        background: var(--bg-light);
      }

      .panel-header {
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: var(--shadow);
      }

      .panel-header h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
      }

      .panel-header h1 i {
        color: var(--accent);
      }

      .record-count {
        font-size: 0.9rem;
        color: var(--text-light);
        font-weight: 500;
        margin-left: 10px;
      }

      .header-actions {
        display: flex;
        gap: 10px;
      }

      .btn-sm {
        padding: 8px 16px;
        font-size: 0.85rem;
      }

      .btn-danger {
        background: var(--danger);
        color: white;
      }

      .btn-danger:hover {
        background: var(--danger-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-xl);
      }

      /* Filter Info Banner */
      .filter-info {
        background: rgba(59, 130, 246, 0.1);
        border-left: 4px solid #3b82f6;
        padding: 12px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #2563eb;
        font-size: 0.9rem;
        font-weight: 600;
      }

      .filter-info i {
        font-size: 1.1rem;
      }

      .clear-filter-link {
        color: #2563eb;
        text-decoration: underline;
        font-weight: 700;
        margin-left: 5px;
        cursor: pointer;
      }

      /* Table Container */
      .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        overflow: hidden;
      }

      .table-responsive {
        overflow-x: hidden;
        max-height: 600px;
        overflow-y: auto;
      }

      .table-responsive::-webkit-scrollbar {
        width: 10px;
        height: 10px;
      }

      .table-responsive::-webkit-scrollbar-track {
        background: var(--bg-light);
        border-radius: 10px;
      }

      .table-responsive::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
      }

      .table-responsive::-webkit-scrollbar-thumb:hover {
        background: var(--secondary);
      }

      .data-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
      }

      /* Fixed column widths */
      .data-table th:nth-child(1),
      .data-table td:nth-child(1) { width: 3%; text-align: center; }
      .data-table th:nth-child(2),
      .data-table td:nth-child(2) { width: 12%; }
      .data-table th:nth-child(3),
      .data-table td:nth-child(3) { width: 18%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
      .data-table th:nth-child(4),
      .data-table td:nth-child(4) { width: 10%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
      .data-table th:nth-child(5),
      .data-table td:nth-child(5) { width: 8%; }
      .data-table th:nth-child(6),
      .data-table td:nth-child(6) { width: 8%; }
      .data-table th:nth-child(7),
      .data-table td:nth-child(7) { width: 12%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
      .data-table th:nth-child(8),
      .data-table td:nth-child(8) { width: 12%; }
      .data-table th:nth-child(9),
      .data-table td:nth-child(9) { width: 15%; }

      .checkbox-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
      }

      .record-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
        margin: 0;
      }

      .quick-filter-divider {
        width: 1px;
        background: var(--border);
        margin: 4px 0;
        flex-shrink: 0;
      }

      .quick-filter-btn.sort-asc,
      .quick-filter-btn.sort-desc {
        border-color: var(--primary);
        background: rgba(14, 36, 18, 0.06);
        color: var(--primary);
      }

      /* Sortable column header */
      .sortable-header {
        cursor: default;
      }

      .sort-icon { display: none; }

      /* Edit Mode Styles */
      .edit-mode-active tbody tr {
        cursor: default;
      }

      .edit-mode-active tbody tr:hover {
        background: var(--bg-light);
        transform: none;
      }

      .edit-mode-active tbody tr.selected {
        background: rgba(14, 36, 18, 0.05);
      }

      /* Edit Mode Banner */
      .edit-mode-banner {
        background: linear-gradient(135deg, rgba(14, 36, 18, 0.1), rgba(45, 90, 58, 0.1));
        border: 2px solid var(--primary);
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 12px;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        animation: slideDown 0.3s ease;
      }

      .edit-mode-banner.show {
        display: flex;
      }

      @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .edit-mode-info {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--primary);
        font-weight: 700;
        font-size: 0.9rem;
      }

      .edit-mode-info i {
        font-size: 1.2rem;
      }

      .selection-count {
        background: var(--primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
      }

      .edit-mode-actions {
        display: flex;
        gap: 10px;
      }

      .btn-delete-selected {
        background: var(--danger);
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .btn-delete-selected:hover:not(:disabled) {
        background: var(--danger-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
      }

      .btn-delete-selected:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }

      .btn-cancel-edit {
        background: white;
        color: var(--text);
        border: 2px solid var(--border);
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
      }

      .btn-cancel-edit:hover {
        border-color: var(--primary);
        color: var(--primary);
      }

      .data-table thead {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        position: sticky;
        top: 0;
        z-index: 10;
      }

      .data-table th {
        padding: 16px;
        text-align: left;
        font-weight: 700;
        color: white;
        font-size: 0.80rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .data-table td {
        padding: 16px;
        border-bottom: 1px solid var(--border);
        color: var(--text-light);
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .data-table tbody tr {
        transition: all 0.2s ease;
        cursor: pointer;
      }

      .data-table tbody tr:hover {
        background: var(--bg-light);
        transform: scale(1.002);
      }

      .data-table tbody tr.clickable-row:hover td:not(:first-child) {
        color: var(--primary);
      }

      /* Badges */
      .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .badge-success { background: rgba(16, 185, 129, 0.1); color: var(--success-dark); }
      .badge-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning-dark); }
      .badge-info { background: rgba(59, 130, 246, 0.1); color: #2563eb; }
      .badge-primary { background: rgba(14, 36, 18, 0.1); color: var(--primary); }

      /* Empty State */
      .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-light);
      }

      .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.3;
        color: var(--text-light);
      }

      .empty-state h3 {
        font-size: 1.2rem;
        margin-bottom: 10px;
        color: var(--text);
      }

      .empty-state a {
        color: var(--primary);
        font-weight: 700;
        text-decoration: underline;
      }

      /* Modal */
      .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
      }

      .modal.show { display: flex; }

      .modal-content {
        background: white;
        border-radius: 16px;
        max-width: 700px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-xl);
        animation: modalSlideIn 0.3s ease;
      }

      @keyframes modalSlideIn {
        from { opacity: 0; transform: translateY(-50px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .modal-header {
        padding: 25px;
        border-bottom: 2px solid var(--border);
      }

      .modal-header h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--danger);
        font-size: 1.2rem;
        margin: 0;
      }

      .modal-body {
        padding: 25px;
        font-size: 0.9rem;
        line-height: 1.6;
      }

      .modal-footer {
        padding: 20px 25px;
        border-top: 2px solid var(--border);
        display: flex;
        gap: 10px;
        justify-content: flex-end;
      }

      /* Prediction Details Modal */
      .details-modal .modal-content { max-width: 800px; }

      .details-modal .modal-header {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border-bottom: none;
      }

      .details-modal .modal-header h3 { color: white; }

      .prediction-result-banner {
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
      }

      .prediction-result-banner.success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
        border: 2px solid var(--success);
      }

      .prediction-result-banner.warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.1));
        border: 2px solid var(--warning);
      }

      .prediction-result-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
      }

      .prediction-result-banner.success .prediction-result-icon { background: var(--success); color: white; }
      .prediction-result-banner.warning .prediction-result-icon { background: var(--warning); color: white; }

      .prediction-result-content h4 { margin: 0 0 5px 0; font-size: 1.2rem; }
      .prediction-result-banner.success .prediction-result-content h4 { color: var(--success-dark); }
      .prediction-result-banner.warning .prediction-result-content h4 { color: var(--warning-dark); }

      .prediction-probability { font-size: 2rem; font-weight: 800; margin: 0; }

      .details-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 25px;
      }

      .detail-item {
        background: var(--bg-light);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid var(--primary);
      }

      .detail-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
      }

      .detail-value { font-size: 1.1rem; font-weight: 700; color: var(--text); }
      .full-width { grid-column: 1 / -1; }

      .timestamp-info {
        background: rgba(59, 130, 246, 0.1);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #3b82f6;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        color: #2563eb;
      }

      .click-hint {
        text-align: center;
        padding: 12px;
        background: rgba(28, 200, 138, 0.1);
        border-radius: 8px;
        margin-bottom: 15px;
        color: var(--primary);
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
      }

      .click-hint i { animation: pulse 2s infinite; }

      @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
      }

      /* Quick Filter Buttons */
      .quick-filters {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
      }

      .quick-filter-btn {
        flex: 1;
        padding: 12px 20px;
        border: 2px solid var(--border);
        background: white;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: var(--text);
        font-family: inherit;
      }

      .quick-filter-btn:hover {
        border-color: var(--primary);
        background: var(--bg-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
      }

      .quick-filter-btn.active {
        border-color: var(--primary);
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        box-shadow: var(--shadow-lg);
      }

      .quick-filter-btn i { font-size: 1rem; }

      /* Responsive */
      @media (max-width: 1200px) {
        .two-column-container { grid-template-columns: 320px 1fr; }
        .details-grid { grid-template-columns: 1fr; }
        .data-table { min-width: 1000px; }
      }

      @media (max-width: 768px) {
        .two-column-container { grid-template-columns: 1fr; margin-top: 60px; }
        .left-sidebar { position: static; height: auto; border-right: none; border-bottom: 1px solid var(--border); padding: 20px; }
        .panel-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .header-actions { width: 100%; flex-direction: column; }
        .header-actions .btn { width: 100%; }
        .data-table { font-size: 0.8rem; min-width: 900px; }
        .data-table th, .data-table td { padding: 12px 8px; }
        .table-responsive { max-height: 500px; }
        .modal-content { width: 95%; }
        .modal-footer { flex-direction: column; }
        .modal-footer .btn { width: 100%; }
        .quick-filters { flex-direction: column; gap: 10px; }
        .quick-filter-btn { width: 100%; }
        .details-grid { grid-template-columns: 1fr; }
        .edit-mode-banner { flex-direction: column; align-items: stretch; }
        .edit-mode-actions { width: 100%; flex-direction: column; }
        .btn-delete-selected, .btn-cancel-edit { width: 100%; }
        .toast-message { right: 10px; left: 10px; max-width: none; }
      }

      /* Scrollbar Styling */
      .left-sidebar::-webkit-scrollbar,
      .right-panel::-webkit-scrollbar,
      .modal-content::-webkit-scrollbar { width: 6px; }

      .left-sidebar::-webkit-scrollbar-track,
      .right-panel::-webkit-scrollbar-track,
      .modal-content::-webkit-scrollbar-track { background: transparent; }

      .left-sidebar::-webkit-scrollbar-thumb,
      .right-panel::-webkit-scrollbar-thumb,
      .modal-content::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }

      .left-sidebar::-webkit-scrollbar-thumb:hover,
      .right-panel::-webkit-scrollbar-thumb:hover,
      .modal-content::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</head>
<body>
    <?php if ($showUpdateSuccess): ?>
    <div class="toast-message toast-success">
      <i class="fas fa-check-circle"></i>
      <div class="toast-message-content">
        <strong>Success!</strong>
        <p>Record updated successfully</p>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($showUpdateError): ?>
    <div class="toast-message toast-error">
      <i class="fas fa-exclamation-circle"></i>
      <div class="toast-message-content">
        <strong>Error!</strong>
        <p>Failed to update record. Please try again.</p>
      </div>
    </div>
    <?php endif; ?>

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
                <a href="records.php" class="nav-link active">
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

    <div class="two-column-container">
      <!-- Left Sidebar - Filters -->
      <aside class="left-sidebar">
        <div class="sidebar-header">
          <h2>
            <i class="fas fa-filter"></i>
            Filter Records
          </h2>
          <p>Refine your search using the filters below</p>
        </div>

        <?php if (hasActiveFilters($filters)): ?>
        <div class="active-filters-badge">
          <i class="fas fa-check-circle"></i>
          <span>Filters Active</span>
        </div>
        <?php endif; ?>

        <form method="GET" action="records.php" class="filter-form" id="filterForm">
          <div class="form-group">
            <label>
              <i class="fas fa-user"></i>
              Student Name
            </label>
            <input type="text" name="student_name" placeholder="Search by name" 
                   value="<?php echo htmlspecialchars($filters['student_name']); ?>">
          </div>

          <div class="form-group">
            <label>
              <i class="fas fa-university"></i>
              Program
            </label>
            <div class="select-wrapper">
              <select name="program">
                <option value="all" <?php echo $filters['program'] === 'all' ? 'selected' : ''; ?>>All Programs</option>
                <option value="BSIT" <?php echo $filters['program'] === 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                <option value="BSCS" <?php echo $filters['program'] === 'BSCS' ? 'selected' : ''; ?>>BSCS</option>
                <option value="BSECE" <?php echo $filters['program'] === 'BSECE' ? 'selected' : ''; ?>>BSECE</option>
                <option value="BSCpE" <?php echo $filters['program'] === 'BSCpE' ? 'selected' : ''; ?>>BSCpE</option>
                <option value="BSIS" <?php echo $filters['program'] === 'BSIS' ? 'selected' : ''; ?>>BSIS</option>
              </select>
              <i class="fas fa-chevron-down select-arrow"></i>
            </div>
          </div>

          <div class="form-group">
            <label>
              <i class="fas fa-stream"></i>
              Strand
            </label>
            <div class="select-wrapper">
              <select name="strand">
                <option value="all" <?php echo $filters['strand'] === 'all' ? 'selected' : ''; ?>>All Strands</option>
                <option value="STEM" <?php echo $filters['strand'] === 'STEM' ? 'selected' : ''; ?>>STEM</option>
                <option value="TVL-ICT" <?php echo $filters['strand'] === 'TVL-ICT' ? 'selected' : ''; ?>>TVL-ICT</option>
                <option value="ABM" <?php echo $filters['strand'] === 'ABM' ? 'selected' : ''; ?>>ABM</option>
                <option value="HUMSS" <?php echo $filters['strand'] === 'HUMSS' ? 'selected' : ''; ?>>HUMSS</option>
                <option value="GAS" <?php echo $filters['strand'] === 'GAS' ? 'selected' : ''; ?>>GAS</option>
              </select>
              <i class="fas fa-chevron-down select-arrow"></i>
            </div>
          </div>

          <div class="form-group">
            <label>
              <i class="fas fa-venus-mars"></i>
              Sex
            </label>
            <div class="select-wrapper">
              <select name="sex">
                <option value="all" <?php echo $filters['sex'] === 'all' ? 'selected' : ''; ?>>All</option>
                <option value="Male" <?php echo $filters['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $filters['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
              </select>
              <i class="fas fa-chevron-down select-arrow"></i>
            </div>
          </div>

          <div class="form-group">
            <label>
              <i class="fas fa-percentage"></i>
              Min GPA
            </label>
            <input type="number" name="min_gpa" step="0.01" min="0" max="100" 
                   placeholder="e.g., 75" value="<?php echo htmlspecialchars($filters['min_gpa']); ?>">
          </div>

          <div class="form-group">
            <label>
              <i class="fas fa-percentage"></i>
              Max GPA
            </label>
            <input type="number" name="max_gpa" step="0.01" min="0" max="100" 
                   placeholder="e.g., 95" value="<?php echo htmlspecialchars($filters['max_gpa']); ?>">
          </div>

          <div class="form-group">
            <label>
              <i class="fas fa-calendar-alt"></i>
              From Date
            </label>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
          </div>

          <div class="form-group">
            <label>
              <i class="fas fa-calendar-alt"></i>
              To Date
            </label>
            <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
          </div>

          <div class="filter-buttons">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-search"></i>
              Apply
            </button>
            <button type="button" class="btn btn-secondary" onclick="resetFilters()">
              <i class="fas fa-undo"></i>
              Reset
            </button>
          </div>
        </form>
      </aside>

      <!-- Right Panel - Records Table -->
      <main class="right-panel">
        <div class="panel-header">
          <h1>
            <i class="fas fa-database"></i>
            All Records
            <?php if (count($recentPredictions) > 0): ?>
              <span class="record-count">(<?php echo count($recentPredictions); ?>)</span>
            <?php endif; ?>
          </h1>
          <div class="header-actions">
            <a href="index.php" class="btn btn-sm btn-primary">
              <i class="fas fa-plus"></i> New Prediction
            </a>
            <?php if (count($recentPredictions) > 0): ?>
              <a href="#" id="viewReportBtn" class="btn btn-sm btn-secondary"
                 onclick="openReport(event)">
                <i class="fas fa-print"></i> View Report
              </a>
              <button type="button" class="btn btn-sm btn-secondary" id="toggleEditMode">
                <i class="fas fa-edit"></i> Edit Mode
              </button>
            <?php endif; ?>
          </div>
        </div>

        <?php if (count($recentPredictions) > 0): ?>
        <!-- Edit Mode Banner -->
        <div class="edit-mode-banner" id="editModeBanner">
          <div class="edit-mode-info">
            <i class="fas fa-check-square"></i>
            <span>Select records to delete</span>
            <span class="selection-count" id="selectionCount">0 selected</span>
          </div>
          <div class="edit-mode-actions">
            <button class="btn-delete-selected" id="deleteSelectedBtn" disabled onclick="confirmDeleteSelected()">
              <i class="fas fa-trash-alt"></i>
              Delete Selected
            </button>
            <button class="btn-cancel-edit" onclick="exitEditMode()">
              <i class="fas fa-times"></i>
              Cancel
            </button>
          </div>
        </div>

        <div class="click-hint" id="clickHint">
          <i class="fas fa-hand-pointer"></i>
          Click on any record to view detailed prediction information
        </div>

        <div class="quick-filters">
          <button class="quick-filter-btn <?php echo $filters['result'] === 'all' ? 'active' : ''; ?>" 
                  onclick="applyQuickFilter('all')">
            <i class="fas fa-list"></i>
            All Records
          </button>
          <button class="quick-filter-btn <?php echo $filters['result'] === 'likely' ? 'active' : ''; ?>" 
                  onclick="applyQuickFilter('likely')">
            <i class="fas fa-check-circle"></i>
            Likely to Graduate
          </button>
          <button class="quick-filter-btn <?php echo $filters['result'] === 'unlikely' ? 'active' : ''; ?>" 
                  onclick="applyQuickFilter('unlikely')">
            <i class="fas fa-exclamation-circle"></i>
            Unlikely to Graduate
          </button>
          <div class="quick-filter-divider"></div>
          <button class="quick-filter-btn<?php echo $filters['name_sort'] === 'asc' ? ' sort-asc' : ($filters['name_sort'] === 'desc' ? ' sort-desc' : ''); ?>" 
                  id="sortNameBtn" onclick="sortByName()">
            <i class="fas <?php echo $filters['name_sort'] === 'desc' ? 'fa-sort-alpha-up' : ($filters['name_sort'] === 'asc' ? 'fa-sort-alpha-down' : 'fa-sort'); ?>" id="sortNameIcon"></i>
            Name
          </button>
        </div>
        <?php endif; ?>

        <?php if (count($recentPredictions) > 0 && hasActiveFilters($filters)): ?>
        <div class="filter-info">
          <i class="fas fa-info-circle"></i>
          Showing filtered results.
          <a href="records.php" class="clear-filter-link">Clear filters</a>
        </div>
        <?php endif; ?>

        <div class="table-container">
          <div class="table-responsive">
            <table class="data-table" id="recordsTable">
              <thead>
                <tr>
                  <th>
                    <div class="checkbox-cell">
                      <input type="checkbox" id="selectAll" class="record-checkbox" style="display: none;" onchange="toggleSelectAll(this)">
                    </div>
                  </th>
                  <th>Date</th>
                  <th>Student Name</th>
                  <th>Program</th>
                  <th>Sex</th>
                  <th>GPA</th>
                  <th>Strand</th>
                  <th>Probability</th>
                  <th>Result</th>
                </tr>
              </thead>
              <tbody id="recordsTbody">
                <?php if (empty($recentPredictions)): ?>
                <tr>
                  <td colspan="9">
                    <div class="empty-state">
                      <i class="fas fa-inbox"></i>
                      <h3>No Records Found</h3>
                      <p>
                        <?php if (hasActiveFilters($filters)): ?>
                          No predictions match your filters. <a href="records.php">Clear filters</a>
                        <?php else: ?>
                          No predictions yet. <a href="index.php">Create your first prediction</a>
                        <?php endif; ?>
                      </p>
                    </div>
                  </td>
                </tr>
                <?php else: ?>
                  <?php foreach($recentPredictions as $pred): ?>
                  <tr id="row-<?php echo $pred['id']; ?>" class="clickable-row" 
                      data-prediction='<?php echo json_encode($pred); ?>'
                      data-id="<?php echo $pred['id']; ?>"
                      data-name="<?php echo htmlspecialchars(strtolower($pred['student_name'] ?? '')); ?>"
                      onclick="handleRowClick(event, this)">
                    <td onclick="event.stopPropagation();">
                      <div class="checkbox-cell">
                        <input type="checkbox" class="record-checkbox row-checkbox" 
                               value="<?php echo $pred['id']; ?>" 
                               style="display: none;"
                               onchange="updateSelectionCount()">
                      </div>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($pred['created_at'])); ?></td>
                    <td title="<?php echo htmlspecialchars($pred['student_name'] ?? 'Anonymous'); ?>">
                      <strong>
                        <?php 
                        $name = htmlspecialchars($pred['student_name'] ?? 'Anonymous');
                        echo $name ?: 'Anonymous';
                        ?>
                      </strong>
                    </td>
                    <td>
                      <span class="badge badge-primary">
                        <?php echo htmlspecialchars($pred['program'] ?? 'N/A'); ?>
                      </span>
                    </td>
                    <td><?php echo $pred['sex']; ?></td>
                    <td><strong><?php echo $pred['shs_gpa']; ?></strong></td>
                    <td>
                      <span class="badge <?php echo in_array($pred['shs_strand'], ['STEM', 'TVL-ICT']) ? 'badge-success' : 'badge-info'; ?>">
                        <?php echo $pred['shs_strand']; ?>
                      </span>
                    </td>
                    <td><strong><?php echo number_format($pred['probability'], 1); ?>%</strong></td>
                    <td>
                      <?php if ($pred['prediction_result'] == 1): ?>
                        <span class="badge badge-success">
                          <i class="fas fa-check"></i> Likely
                        </span>
                      <?php else: ?>
                        <span class="badge badge-warning">
                          <i class="fas fa-exclamation"></i> Unlikely
                        </span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
        </div>
        <div class="modal-body">
          <p id="modalMessage">Are you sure you want to delete this prediction? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" onclick="closeModal('deleteModal')">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button class="btn btn-danger" onclick="executeDelete()">
            <i class="fas fa-trash-alt"></i> Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Prediction Details Modal -->
    <div id="detailsModal" class="modal details-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3><i class="fas fa-file-alt"></i> Prediction Details</h3>
        </div>
        <div class="modal-body">
          <div id="resultBanner" class="prediction-result-banner">
            <div class="prediction-result-icon">
              <i id="resultIcon" class="fas"></i>
            </div>
            <div class="prediction-result-content">
              <h4 id="resultTitle"></h4>
              <p class="prediction-probability" id="resultProbability"></p>
            </div>
          </div>

          <div class="details-grid">
            <div class="detail-item full-width">
              <div class="detail-label">
                <i class="fas fa-user-circle"></i>
                Student Name
              </div>
              <div class="detail-value" id="detailNameValue"></div>
            </div>

            <div class="detail-item full-width">
              <div class="detail-label">
                <i class="fas fa-graduation-cap"></i>
                Program
              </div>
              <div class="detail-value" id="detailProgramValue"></div>
            </div>

            <div class="detail-item">
              <div class="detail-label">
                <i class="fas fa-venus-mars"></i>
                Sex
              </div>
              <div class="detail-value" id="detailSex"></div>
            </div>

            <div class="detail-item">
              <div class="detail-label">
                <i class="fas fa-chart-line"></i>
                SHS GPA
              </div>
              <div class="detail-value" id="detailGPA"></div>
            </div>

            <div class="detail-item full-width">
              <div class="detail-label">
                <i class="fas fa-book"></i>
                SHS Strand
              </div>
              <div class="detail-value" id="detailStrand"></div>
            </div>
          </div>

          <div class="timestamp-info">
            <i class="fas fa-clock"></i>
            <div>
              <strong>Prediction Date:</strong> <span id="detailDate"></span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" onclick="closeModal('detailsModal')">
            <i class="fas fa-times"></i> Close
          </button>
          <a href="#" id="editRecordBtn" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Record
          </a>
        </div>
      </div>
    </div>

    <script>
      const programNames = {
        'BSCS': 'Bachelor of Science in Computer Science',
        'BSIT': 'Bachelor of Science in Information Technology',
        'BSIS': 'Bachelor of Science in Information Systems',
        'BSCpE': 'Bachelor of Science in Computer Engineering',
        'BSECE': 'Bachelor of Science in Electronics Engineering'
      };

      let isEditMode = false;
      let deleteId = null;

      // ── Name Sorting ──────────────────────────────────────────────
      // Initialize sort state from URL param
      let nameSortDirection = <?php echo $filters['name_sort'] ? "'" . $filters['name_sort'] . "'" : 'null'; ?>;

      // Capture original row order on page load for reset
      const originalRowOrder = Array.from(
        document.getElementById('recordsTbody').querySelectorAll('tr.clickable-row')
      );

      // Apply initial sort to DOM on page load if a sort param exists
      (function() {
        if (!nameSortDirection) return;
        const tbody = document.getElementById('recordsTbody');
        const rows = Array.from(tbody.querySelectorAll('tr.clickable-row'));
        rows.sort((a, b) => {
          const nameA = a.getAttribute('data-name') || '';
          const nameB = b.getAttribute('data-name') || '';
          return nameSortDirection === 'asc' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
        });
        rows.forEach(row => tbody.appendChild(row));
      })();

      function sortByName() {
        const tbody = document.getElementById('recordsTbody');
        const rows = Array.from(tbody.querySelectorAll('tr.clickable-row'));
        if (rows.length === 0) return;

        // Cycle: null → asc → desc → null (reset)
        if (nameSortDirection === null) {
          nameSortDirection = 'asc';
        } else if (nameSortDirection === 'asc') {
          nameSortDirection = 'desc';
        } else {
          nameSortDirection = null;
        }

        if (nameSortDirection === null) {
          // Restore original insertion order
          originalRowOrder.forEach(row => tbody.appendChild(row));
        } else {
          rows.sort((a, b) => {
            const nameA = a.getAttribute('data-name') || '';
            const nameB = b.getAttribute('data-name') || '';
            return nameSortDirection === 'asc'
              ? nameA.localeCompare(nameB)
              : nameB.localeCompare(nameA);
          });
          rows.forEach(row => tbody.appendChild(row));
        }

        // Update URL param — remove it entirely on reset
        const urlParams = new URLSearchParams(window.location.search);
        if (nameSortDirection) {
          urlParams.set('name_sort', nameSortDirection);
        } else {
          urlParams.delete('name_sort');
        }
        window.history.replaceState(null, '', 'records.php?' + urlParams.toString());

        updateSortButton();
      }

      function updateSortButton() {
        const btn = document.getElementById('sortNameBtn');
        const icon = document.getElementById('sortNameIcon');
        if (!btn || !icon) return;

        btn.classList.remove('sort-asc', 'sort-desc');
        if (nameSortDirection === 'asc') {
          btn.classList.add('sort-asc');
          icon.className = 'fas fa-sort-alpha-down';
        } else if (nameSortDirection === 'desc') {
          btn.classList.add('sort-desc');
          icon.className = 'fas fa-sort-alpha-up';
        } else {
          icon.className = 'fas fa-sort';
        }
      }

      // ── Auto-hide toasts ──────────────────────────────────────────
      setTimeout(function() {
        document.querySelectorAll('.toast-message').forEach(t => t.style.display = 'none');
      }, 3000);

      // ── Edit Mode ─────────────────────────────────────────────────
      document.getElementById('toggleEditMode')?.addEventListener('click', function() {
        isEditMode = !isEditMode;
        if (isEditMode) {
          enterEditMode(this);
        } else {
          exitEditMode();
        }
      });

      function enterEditMode(btn) {
        document.getElementById('editModeBanner').classList.add('show');
        document.getElementById('clickHint').style.display = 'none';
        document.getElementById('selectAll').style.display = 'block';
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.style.display = 'block');
        document.getElementById('recordsTable').classList.add('edit-mode-active');
        btn.innerHTML = '<i class="fas fa-eye"></i> View Mode';
        btn.classList.replace('btn-secondary', 'btn-primary');
      }

      function exitEditMode() {
        isEditMode = false;
        document.getElementById('editModeBanner').classList.remove('show');
        document.getElementById('clickHint').style.display = 'flex';
        const selectAll = document.getElementById('selectAll');
        selectAll.style.display = 'none';
        selectAll.checked = false;
        document.querySelectorAll('.row-checkbox').forEach(cb => { cb.style.display = 'none'; cb.checked = false; });
        document.getElementById('recordsTable').classList.remove('edit-mode-active');
        document.querySelectorAll('tr.selected').forEach(row => row.classList.remove('selected'));
        const toggleBtn = document.getElementById('toggleEditMode');
        if (toggleBtn) {
          toggleBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Mode';
          toggleBtn.classList.replace('btn-primary', 'btn-secondary');
        }
        updateSelectionCount();
      }

      // ── Row Interaction ───────────────────────────────────────────
      function handleRowClick(event, row) {
        if (isEditMode) {
          const checkbox = row.querySelector('.row-checkbox');
          checkbox.checked = !checkbox.checked;
          row.classList.toggle('selected', checkbox.checked);
          updateSelectionCount();
        } else {
          showPredictionDetails(row);
        }
      }

      function toggleSelectAll(selectAllCheckbox) {
        const rows = document.querySelectorAll('.clickable-row');
        document.querySelectorAll('.row-checkbox').forEach((cb, i) => {
          cb.checked = selectAllCheckbox.checked;
          rows[i].classList.toggle('selected', selectAllCheckbox.checked);
        });
        updateSelectionCount();
      }

      function updateSelectionCount() {
        const count = document.querySelectorAll('.row-checkbox:checked').length;
        const countEl = document.getElementById('selectionCount');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        if (countEl) countEl.textContent = `${count} selected`;
        if (deleteBtn) deleteBtn.disabled = count === 0;
        const selectAll = document.getElementById('selectAll');
        const all = document.querySelectorAll('.row-checkbox');
        if (selectAll && all.length > 0) selectAll.checked = count === all.length;
      }

      // ── Details Modal ─────────────────────────────────────────────
      function showPredictionDetails(row) {
        const data = JSON.parse(row.getAttribute('data-prediction'));
        const isLikely = data.prediction_result == 1;

        const banner = document.getElementById('resultBanner');
        banner.className = 'prediction-result-banner ' + (isLikely ? 'success' : 'warning');
        document.getElementById('resultIcon').className = 'fas ' + (isLikely ? 'fa-graduation-cap' : 'fa-exclamation-circle');
        document.getElementById('resultTitle').textContent = isLikely ? 'Likely to Graduate' : 'Unlikely to Graduate';
        document.getElementById('resultProbability').textContent = parseFloat(data.probability).toFixed(1) + '%';

        document.getElementById('detailNameValue').textContent = data.student_name || 'Anonymous';
        document.getElementById('detailProgramValue').textContent = programNames[data.program] || data.program;
        document.getElementById('detailSex').textContent = data.sex;
        document.getElementById('detailGPA').textContent = data.shs_gpa;
        document.getElementById('detailStrand').textContent = data.shs_strand;

        const date = new Date(data.created_at);
        document.getElementById('detailDate').textContent = date.toLocaleDateString('en-US', {
          year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });

        document.getElementById('editRecordBtn').href = 'index.php?edit=' + data.id;
        document.getElementById('detailsModal').classList.add('show');
      }

      // ── Delete ────────────────────────────────────────────────────
      function confirmDeleteSelected() {
        const count = document.querySelectorAll('.row-checkbox:checked').length;
        if (count === 0) return;
        document.getElementById('modalMessage').textContent =
          `Are you sure you want to delete ${count} selected record(s)? This action cannot be undone.`;
        document.getElementById('deleteModal').classList.add('show');
      }

      function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
        if (modalId === 'deleteModal') deleteId = null;
      }

      function executeDelete() {
        if (isEditMode) {
          executeDeleteSelected();
        } else if (deleteId) {
          const formData = new FormData();
          formData.append('action', 'delete');
          formData.append('id', deleteId);
          fetch('records.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
              if (data.success) {
                removeRowAnimated(deleteId);
                closeModal('deleteModal');
              } else {
                alert('Failed to delete prediction. Please try again.');
              }
            })
            .catch(() => alert('An error occurred. Please try again.'));
        }
      }

      function executeDeleteSelected() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        if (ids.length === 0) return;

        const formData = new FormData();
        formData.append('action', 'delete_multiple');
        formData.append('ids', JSON.stringify(ids));

        fetch('records.php', { method: 'POST', body: formData })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              ids.forEach(id => removeRowAnimated(id));
              closeModal('deleteModal');
              exitEditMode();
            } else {
              alert('Failed to delete records. Please try again.');
            }
          })
          .catch(() => alert('An error occurred. Please try again.'));
      }

      function removeRowAnimated(id) {
        const row = document.getElementById('row-' + id);
        if (!row) return;
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        setTimeout(() => { row.remove(); checkEmptyTable(); }, 300);
      }

      function checkEmptyTable() {
        if (document.querySelector('.data-table tbody').children.length === 0) {
          location.reload();
        }
      }

      // ── Utilities ─────────────────────────────────────────────────
      function openReport(e) {
        e.preventDefault();
        const params = new URLSearchParams(window.location.search);
        window.location.href = 'generate_records_report.php?' + params.toString();
      }

      function resetFilters() {
        window.location.href = 'records.php';
      }

      function applyQuickFilter(result) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('result', result);
        window.location.href = 'records.php?' + urlParams.toString();
      }

      window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
          event.target.classList.remove('show');
        }
      };

      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          document.querySelectorAll('.modal.show').forEach(m => m.classList.remove('show'));
          if (isEditMode) exitEditMode();
        }
      });
    </script>
</body>
</html>