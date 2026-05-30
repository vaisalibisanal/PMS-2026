<?php
// add_pms.php - Add a new PMS record
session_start();

require_once './../mysql/connection.php';
require_once './../mysql/mysql_helper.php';
require_once './../constants/constants.php';

// Ensure user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = intval($_POST['project_id'] ?? 0);
    $assigned_to = $_POST['assigned_to'] ?? [];
    $from_date = trim($_POST['from_date'] ?? '');
    $to_date = trim($_POST['to_date'] ?? '');
    $progress = intval($_POST['progress'] ?? 0);
    $created_by = $_SESSION['user_id'];
    $status = 0; // Default to active (0 represents active based on existing project schemas)

    // Form validation
    if (empty($project_id)) {
        $errors[] = "Please select a Project.";
    }
    if (empty($assigned_to) || !is_array($assigned_to)) {
        $errors[] = "Please assign at least one user.";
    }
    if (empty($from_date)) {
        $errors[] = "From Date is required.";
    }
    if (empty($to_date)) {
        $errors[] = "To Date is required.";
    }
    if (!empty($from_date) && !empty($to_date) && strtotime($from_date) > strtotime($to_date)) {
        $errors[] = "From Date cannot be later than To Date.";
    }
    
    // Insert into DB if no errors are found
    if (empty($errors)) {
        // Convert assigned users array into a string
        $assigned_to_str = implode(',', array_map('intval', $assigned_to));
        
        // Using prepared statements for optimized and safe execution
        $stmt = $conn->prepare("INSERT INTO pms (project_id, assigned_to, from_date, to_date, progress_percentage, created_by, created_on, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
        
        if ($stmt) {
            $stmt->bind_param("isssiii", $project_id, $assigned_to_str, $from_date, $to_date, $progress, $created_by, $status);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "PMS record added successfully!";
                header('Location: pms_listing.php');
                exit;
            } else {
                $errors[] = "Failed to add PMS record. Please try again. Error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            $errors[] = "Database preparation error. Please try again later.";
        }
    }
}

// Fetch active projects for dropdown
/*$projects = [];
$res = $conn->query("SELECT id, name FROM projects WHERE status = 0 ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Fetch users for multi-select dropdown
$users = [];
$res = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
}*/
// Fetch projects for dropdown
$projects = [];

$res = $conn->query(" SELECT id, project_name FROM projects WHERE status = 0 ORDER BY project_name ASC
");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $projects[] = $row;
    }
}

// Fetch users for multi-select dropdown
$users = [];

$res = $conn->query("SELECT id, name FROM users ORDER BY name ASC
");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
}

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'User');
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Add PMS - <?= PROJECT_NAME ?? 'PMS' ?></title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Select2 for better multi-select UX design -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="./../style.css">
        <style>
            .top-avatar { width:36px; height:36px; border-radius:50%; background:#667eea; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:700; }
            .card-spot { border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.08); }
            .select2-container .select2-selection--multiple {
                min-height: 38px;
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
            }
        </style>
    </head>
    <body class="dashboard-page bg-light">
        <!-- Top navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="./../app/index.php">
                    <span class="me-2" style="font-size:20px;">🚀</span>
                    <span class="fw-bold"><?= defined('PROJECT_NAME') ? PROJECT_NAME : 'PMS' ?></span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navMenu">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                        <li class="nav-item me-3 d-none d-lg-block">
                            <span class="text-muted small">Signed in as</span>
                            <div class="d-inline-block ms-2"> <span class="top-avatar"><?php echo strtoupper(substr($userName,0,1)); ?></span> <span class="ms-2 fw-semibold"><?php echo $userName; ?></span></div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="projects.php">Projects</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="pms_listing.php">PMS</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-danger" href="./../app/logout.php">🚪 Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="container my-4" style="max-width: 800px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Add New PMS</h2>
                <a href="pms_listing.php" class="btn btn-secondary">Back to Listing</a>
            </div>

            <div class="card card-spot p-4 bg-white">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add_pms.php">
                    <div class="mb-3">
                        <label for="project_id" class="form-label fw-semibold">Project</label>
                        <select class="form-select" id="project_id" name="project_id" required>
                            <option value="">-- Select Project --</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id'] ?>" <?= (isset($_POST['project_id']) && $_POST['project_id'] == $project['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['project_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label fw-semibold">Assigned To</label>
                        <select class="form-select select2-multi" id="assigned_to" name="assigned_to[]" multiple="multiple" required>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= (isset($_POST['assigned_to']) && in_array($user['id'], $_POST['assigned_to'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="from_date" class="form-label fw-semibold">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="<?= htmlspecialchars($_POST['from_date'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="to_date" class="form-label fw-semibold">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="<?= htmlspecialchars($_POST['to_date'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="progress" class="form-label fw-semibold">Progress (%)</label>
                        <select class="form-select" id="progress" name="progress">
                            <?php for ($i = 0; $i <= 100; $i += 5): ?>
                                <option value="<?= $i ?>" <?= (isset($_POST['progress']) && $_POST['progress'] == $i) ? 'selected' : '' ?>><?= $i ?>%</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">Save PMS Record</button>
                </form>
            </div>
        </main>

        <!-- Bootstrap JS & jQuery (required for Select2 plugin) -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.select2-multi').select2({
                    placeholder: "-- Select Users --",
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>
    </body>
    <p style="margin: 8px 0 0 0; font-size: 13px; color: rgba(255,255,255,0.8);">Your session is secure and active</p>
</html>