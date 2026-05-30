<?php
// projects.php - Project listing page
session_start(); // Start session if not already started

// Include DB connection and helpers
require_once './../mysql/connection.php';
require_once './../mysql/mysql_helper.php'; // For user session handling
require_once './project_functions.php'; // New project-specific functions
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

// Handle soft delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $projectIdToDelete = intval($_GET['id']);
    if (soft_delete_project($conn, $projectIdToDelete)) {
        $_SESSION['success_message'] = "Project successfully marked as inactive.";
    } else {
        $_SESSION['error_message'] = "Failed to mark project as inactive.";
    }
    // Redirect to prevent re-submission on refresh and clear GET parameters
    header('Location: projects.php');
    exit;
}

// Safe user values for display
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'User');

// Pagination settings
$pageSize = 5; // Number of records per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $pageSize;

// Filter parameters
$projectNameFilter = htmlspecialchars($_GET['project_name'] ?? '');
$createdByFilter = isset($_GET['created_by']) && is_numeric($_GET['created_by']) ? intval($_GET['created_by']) : null;
$statusFilter = isset($_GET['status']) ? ($_GET['status'] !== '' ? intval($_GET['status']) : null) : 0; // Defaults to 0 (Active)

// Fetch projects and total count with filters
$projects = get_projects_paginated($conn, $pageSize, $offset, $projectNameFilter, $createdByFilter, $statusFilter);
$totalProjects = get_project_count($conn, $projectNameFilter, $createdByFilter, $statusFilter);

// Calculate total pages
$totalPages = ($totalProjects > 0) ? intval(ceil($totalProjects / $pageSize)) : 1;

// Fetch all users for the 'Created By' filter dropdown
function get_all_users(mysqli $conn): array {
    $sql = "SELECT id, name FROM users ORDER BY name ASC";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
$allUsers = get_all_users($conn);

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Projects - <?= PROJECT_NAME?></title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="./../style.css">
        <!-- Bootstrap Icons for action buttons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            /* Small dashboard-specific styles (reused for consistency) */
            .top-avatar { width:36px; height:36px; border-radius:50%; background:#667eea; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:700; }
            .card-spot { border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.08); }
            /* Project-specific styles */
            .action-icon { font-size: 1.2em; margin: 0 5px; }
            .action-icon.view { color: #0d6efd; } /* Bootstrap primary */
            .action-icon.edit { color: #ffc107; } /* Bootstrap warning */
            .action-icon.delete { color: #dc3545; } /* Bootstrap danger */
            .action-icon:hover { transform: scale(1.1); transition: transform 0.2s ease-in-out; }
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
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Project Listing</h2>
                <a href="add_project.php" class="btn btn-primary">Add Project</a>
            </div>

            <!-- Filters -->
            <div class="card card-spot mb-4 p-3">
                <form method="GET" action="projects.php" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="projectNameFilter" class="form-label">Project Name</label>
                        <input type="text" class="form-control" id="projectNameFilter" name="project_name" value="<?= htmlspecialchars($projectNameFilter) ?>" placeholder="Filter by project name">
                    </div>
                    <div class="col-md-3">
                        <label for="createdByFilter" class="form-label">Created By</label>
                        <select class="form-select" id="createdByFilter" name="created_by">
                            <option value="">All Users</option>
                            <?php foreach ($allUsers as $user): ?>
                                <option value="<?= htmlspecialchars($user['id']) ?>" <?= ($createdByFilter == $user['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select class="form-select" id="statusFilter" name="status">
                            <option value="">All Statuses</option>
                            <option value="0" <?= ($statusFilter === 0) ? 'selected' : '' ?>>Active</option>
                            <option value="1" <?= ($statusFilter === 1) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-info me-2">Apply Filters</button>
                        <a href="projects.php" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>

            <div class="card card-spot p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Project Name</th>
                                <th>Project Code</th>
                                <th>Created By</th>
                                <th>Created On</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($projects)) : ?>
                                <tr><td colspan="7" class="text-muted text-center">No projects found.</td></tr>
                            <?php else: foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['id']); ?></td>
                                    <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                    <td><?php echo htmlspecialchars($project['project_code']); ?></td>
                                    <td><?php echo htmlspecialchars($project['created_by_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($project['created_on']))); ?></td>
                                    <td>
                                        <?php if ($project['status'] == 0): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- View (placeholder - link to a future view_project.php) -->
                                        <a href="view_project.php?id=<?php echo htmlspecialchars($project['id']); ?>" class="action-icon view" title="View Project"><i class="bi bi-eye-fill"></i></a>
                                        <!-- Edit -->
                                        <a href="edit_project.php?id=<?php echo htmlspecialchars($project['id']); ?>" class="action-icon edit" title="Edit Project"><i class="bi bi-pencil-square"></i></a>
                                        <!-- Delete (Soft Delete) -->
                                        <a href="projects.php?action=delete&id=<?php echo htmlspecialchars($project['id']); ?>"
                                           class="action-icon delete" title="Delete Project"
                                           onclick="return confirm('Are you sure you want to mark this project as inactive?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination controls -->
                <nav aria-label="Project list pagination" class="mt-3">
                    <ul class="pagination justify-content-center mb-0">
                        <?php
                            // Build base URL for pagination, preserving filters
                            $queryParams = $_GET;
                            unset($queryParams['page']); // Remove existing page parameter
                            $queryString = http_build_query($queryParams);
                            $baseUrl = 'projects.php?' . $queryString;

                            $prevPage = max(1, $page - 1);
                            $nextPage = min($totalPages, $page + 1);
                        ?>
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . '&page=' . $prevPage; ?>" aria-label="Previous">&laquo;</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo $baseUrl . '&page=' . $i; ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . '&page=' . $nextPage; ?>" aria-label="Next">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </main>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    <p style="margin: 8px 0 0 0; font-size: 13px; color: rgba(255,255,255,0.8);">Your session is secure and active</p>
</html>
