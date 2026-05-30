<?php
// pms_listing.php - PMS records listing
session_start();

require_once './../mysql/connection.php';
require_once './../mysql/mysql_helper.php';
require_once './../constants/constants.php';

// Ensure user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'User');

// Fetch all users to map IDs to names for assigned_to
$users_map = [];
$res_users = $conn->query("SELECT id, name FROM users");
if ($res_users) {
    while ($row = $res_users->fetch_assoc()) {
        $users_map[$row['id']] = $row['name'];
    }
}

// Fetch PMS records
$pms_records = [];
$query = "
    SELECT p.id, pr.project_name, p.assigned_to, p.from_date, p.to_date, p.created_on, u.name as created_by_name, p.status 
    FROM pms p 
    LEFT JOIN projects pr ON p.project_id = pr.id 
    LEFT JOIN users u ON p.created_by = u.id 
    ORDER BY p.created_on DESC
";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pms_records[] = $row;
    }
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PMS Listing - <?= PROJECT_NAME ?? 'PMS' ?></title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="./../style.css">
        <style>
            .top-avatar { width:36px; height:36px; border-radius:50%; background:#667eea; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:700; }
            .card-spot { border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.08); }
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

        <main class="container my-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">PMS Listing</h2>
                <a href="add_pms.php" class="btn btn-primary shadow-sm">Add PMS</a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                        echo htmlspecialchars($_SESSION['success_message']); 
                        unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-spot p-4 bg-white">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project Name</th>
                                <th>Assigned To</th>
                                <th>From - To Dates</th>
                                <th>Created On</th>
                                <th>Created By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pms_records)): ?>
                                <?php foreach ($pms_records as $record): 
                                    // Parse assigned_to string IDs to user names
                                    $assigned_ids = !empty($record['assigned_to']) ? explode(',', $record['assigned_to']) : [];
                                    $assigned_names = [];
                                    foreach ($assigned_ids as $uid) {
                                        if (isset($users_map[$uid])) {
                                            $assigned_names[] = $users_map[$uid];
                                        }
                                    }
                                    $assigned_display = !empty($assigned_names) ? implode(', ', $assigned_names) : 'Unassigned';
                                ?>
                                <tr>
                                    <td class="fw-semibold text-primary"><?= htmlspecialchars($record['project_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($assigned_display) ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($record['from_date']) ?></span> 
                                        <span class="text-muted mx-1">to</span> 
                                        <span class="badge bg-secondary"><?= htmlspecialchars($record['to_date']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($record['created_on']))) ?></td>
                                    <td><?= htmlspecialchars($record['created_by_name'] ?? 'System') ?></td>
                                    <td>
                                        <?php if ($record['status'] == 0): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No PMS records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    <p style="margin: 8px 0 0 0; font-size: 13px; color: rgba(255,255,255,0.8);">Your session is secure and active</p>
</html>