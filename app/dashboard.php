    <?php
// dashboard.php - protected page visible only to logged-in users
// Include DB connection and helpers from the mysql folder
require_once './../mysql/connection.php'; // central DB connection and helpers
require_once './../mysql/mysql_helper.php'; // helper to load a user by id
require_once './../constants/constants.php'; // Include project constants

        // Ensure user is logged in, attempt cookie-based restore if available
        if (empty($_SESSION['user_id'])) {
                if (!empty($_COOKIE['remember_user'])) {
                    // Use helper to fetch user by id (keeps SQL out of view files)
                    $userId = intval($_COOKIE['remember_user']);
                    $row = mysql_help($conn, $userId);
                    if ($row) {
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['user_name'] = $row['name'];
                        $_SESSION['user_email'] = $row['email'];
                    }
                }
        }

        // If still not logged in, redirect to login
        if (empty($_SESSION['user_id'])) {
                header('Location:login.php?error=not_logged_in');
                exit;
        }

        // Safe user values for display
        $userName = htmlspecialchars($_SESSION['user_name'] ?? 'User'); 
        $userEmail = htmlspecialchars($_SESSION['user_email'] ?? ''); 

        // Fetch total users (simple, efficient query)
        // Get total users via helper
        $totalUsers = get_user_count($conn);

        // Pagination: list users with server-side pagination (default 5 per page)
        $pageSize = 5; // number of records per page
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $pageSize;

        // Fetch current page users
        // Load paginated users via helper
        $users = get_users_page($conn, $pageSize, $offset);

        // Calculate total pages
        $totalPages = ($totalUsers > 0) ? intval(ceil($totalUsers / $pageSize)) : 1;

        
        ?>
        <!doctype html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Dashboard - <?= PROJECT_NAME?></title>
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="./../style.css">
                <style>
                    /* Small dashboard-specific styles */
                    .top-avatar { width:36px; height:36px; border-radius:50%; background:#667eea; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:700; }
                    .card-spot { border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.08); }
                    .metric-value { font-size:28px; font-weight:700; color:#333; }
                    .welcome-emoji { font-size:34px; }
                    @media (max-width:576px){ .metric-value { font-size:22px; } }
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
                                    <a class="btn btn-outline-danger" href="./../app/logout.php">🚪 Logout</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <main class="container my-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="p-4 bg-white card-spot">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                    <div>
                                        <h2 class="mb-1">Welcome back, <span class="text-primary"><?php echo $userName; ?></span> <span class="welcome-emoji">✨</span></h2>
                                        <p class="text-muted mb-0">Here's a quick overview of your <?= PROJECT_NAME?> account and the user base.</p>
                                    </div>
                                    <div class="mt-3 mt-md-0">
                                        <a class="btn btn-primary btn-lg" href="dashboard.php" role="button">View Dashboard</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Center metrics -->
                        <div class="col-12 col-md-6">
                            <div class="p-4 bg-white card-spot h-100">
                                <h5 class="mb-3">Total Registered Users</h5>
                                <!-- Well-designed HTML table showing the metric -->
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Metric</th>
                                                <th scope="col" class="text-end">Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Registered Users</td>
                                                <td class="text-end metric-value"><?php echo number_format($totalUsers); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Users table with pagination -->
                        <div class="col-12 col-md-6">
                            <div class="p-4 bg-white card-spot h-100">
                                <h5 class="mb-3">Users</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($users)) : ?>
                                                <tr><td colspan="3" class="text-muted">No users yet</td></tr>
                                            <?php else: foreach ($users as $u): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($u['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination controls -->
                                <nav aria-label="User list pagination" class="mt-3">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php
                                            $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
                                            // previous
                                            $prev = max(1, $page - 1);
                                        ?>
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $baseUrl . '?page=' . $prev; ?>" aria-label="Previous">&laquo;</a>
                                        </li>
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo $baseUrl . '?page=' . $i; ?>"><?php echo $i; ?></a></li>
                                        <?php endfor; ?>
                                        <?php
                                            $next = min($totalPages, $page + 1);
                                        ?>
                                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo $baseUrl . '?page=' . $next; ?>" aria-label="Next">&raquo;</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>

                    </div>
                </main>

                <!-- Bootstrap JS -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
            </body>
        </html>
            <p style="margin: 8px 0 0 0; font-size: 13px; color: rgba(255,255,255,0.8);">Your session is secure and active</p>
        </div>
    </div>
</body>
</html>
                                                                                                                                                                        