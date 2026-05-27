<?php
// edit_project.php - Form to edit an existing project
session_start(); // Start session if not already started

// Include DB connection and helpers
require_once './../mysql/connection.php';
require_once './../mysql/mysql_helper.php'; // For user session handling
require_once './../app/project_functions.php'; // Project-specific functions
require_once './../constants/constants.php';

// Ensure user is logged in, attempt cookie-based restore if available
if (empty($_SESSION['user_id'])) {
    if (!empty($_COOKIE['remember_user'])) {
        $userId = intval($_COOKIE['remember_user']);
        $row = mysql_help($conn, $userId); // Assuming mysql_help fetches user by ID
        if ($row) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
        }
    }
}

// If still not logged in, redirect to login
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

$errors = [];
$project = null;
$projectId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch project data if ID is provided
if ($projectId > 0) {
    $project = get_project_by_id($conn, $projectId);
    if (!$project) {
        $_SESSION['error_message'] = "Project not found.";
        header('Location: projects.php');
        exit;
    }
} else {
    $_SESSION['error_message'] = "Invalid project ID.";
    header('Location: projects.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = trim($_POST['project_name'] ?? '');
    $projectCode = trim($_POST['project_code'] ?? '');
    $status = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Basic validation
    if (empty($projectName)) {
        $errors[] = "Project Name is required.";
    }
    if (empty($projectCode)) {
        $errors[] = "Project Code is required.";
    }

    // Check for duplicate project code, excluding the current project
    if (!empty($projectCode) && is_project_code_duplicate($conn, $projectCode, $projectId)) {
        $errors[] = "Project Code '$projectCode' already exists. Please choose a different one.";
    }

    if (empty($errors)) {
        if (update_project($conn, $projectId, $projectName, $projectCode, $status)) {
            $_SESSION['success_message'] = "Project '$projectName' updated successfully!";
            header('Location: projects.php');
            exit;
        } else {
            $errors[] = "Failed to update project. Please try again.";
        }
    }
    // If there are errors or update failed, re-populate $project with POST data to retain user input
    $project['project_name'] = $projectName;
    $project['project_code'] = $projectCode;
    $project['status'] = $status;
}

// Safe user values for display
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'User');
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edit Project - <?= PROJECT_NAME?></title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="./../style.css">
        <style>
            /* Small dashboard-specific styles (reused for consistency) */
            .top-avatar { width:36px; height:36px; border-radius:50%; background:#667eea; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:700; }
            .card-spot { border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.08); }
        </style>
    </head>
    <body class="dashboard-page">
        <!-- Top navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="./../app/index.php">
                    <span class="me-2" style="font-size:20px;">🚀</span>
                    <span class="fw-bold"><?= PROJECT_NAME?></span>
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
                            <a class="nav-link active" aria-current="page" href="projects.php">Projects</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-danger" href="./../app/logout.php">🚪 Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="container my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Edit Project: <?php echo htmlspecialchars($project['project_name'] ?? ''); ?></h2>
                <a href="projects.php" class="btn btn-secondary">Back to Projects</a>
            </div>

            <div class="card card-spot p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit_project.php?id=<?php echo htmlspecialchars($projectId); ?>">
                    <div class="mb-3">
                        <label for="projectName" class="form-label">Project Name</label>
                        <input type="text" class="form-control" id="projectName" name="project_name" value="<?= htmlspecialchars($project['project_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="projectCode" class="form-label">Project Code</label>
                        <input type="text" class="form-control" id="projectCode" name="project_code" value="<?= htmlspecialchars($project['project_code'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="0" <?= (isset($project['status']) && $project['status'] == 0) ? 'selected' : '' ?>>Active</option>
                            <option value="1" <?= (isset($project['status']) && $project['status'] == 1) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Project</button>
                </form>
            </div>
        </main>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>