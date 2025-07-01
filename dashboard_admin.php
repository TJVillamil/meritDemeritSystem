<?php
session_start();
include 'db_connect.php';
require_once 'session_checker.php';
check_session(true); // Admin/Moderator dashboard
// Add this at the top of both login.php and register.php after session_start():
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add this right before any header() redirect:
if (headers_sent($file, $line)) {
    die("Headers already sent in $file on line $line");
}

// Fetch admin details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$is_admin = ($_SESSION['role_id'] == 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #1a1a1a;
            font-family: 'Poppins', sans-serif;
            color: #fff;
        }
        .card { 
            margin-bottom: 20px; 
            transition: 0.3s; 
            position: relative;
            border: none;
            border-radius: 15px;
            background: #2d2d2d;
            color: white;
        }
        .card:hover { 
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .dashboard-container { 
            margin-top: 80px;
            padding-bottom: 40px;
        }
        .navbar {
            background-color: #2d2d2d !important;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        .quick-action {
            border: 2px solid rgba(255,255,255,0.1);
            transition: all 0.3s;
        }
        .quick-action:hover {
            border-color: #007bff;
            background: rgba(0,123,255,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="#">
                <i class="fas fa-shield-alt me-2"></i>
                <?= $is_admin ? 'Admin' : 'Moderator' ?> Dashboard
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user me-2"></i><?= htmlspecialchars($user['first_name']) ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Users</h6>
                                <div class="stat-number text-info">150</div>
                            </div>
                            <i class="fas fa-users fa-2x text-info opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Pending Requests</h6>
                                <div class="stat-number text-warning">4</div>
                            </div>
                            <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Merits</h6>
                                <div class="stat-number text-success">523</div>
                            </div>
                            <i class="fas fa-award fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Demerits</h6>
                                <div class="stat-number text-danger">89</div>
                            </div>
                            <i class="fas fa-exclamation-circle fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <h5 class="mb-4">Quick Actions</h5>
            </div>
            <div class="col-md-3">
                <a href="manage_users.php" class="card quick-action text-decoration-none">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2 text-info"></i>
                        <h5>Manage Users</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="pending_requests.php" class="card quick-action text-decoration-none">
                    <div class="card-body text-center">
                        <i class="fas fa-tasks fa-2x mb-2 text-warning"></i>
                        <h5>Review Requests</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="reports.php" class="card quick-action text-decoration-none">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar fa-2x mb-2 text-success"></i>
                        <h5>View Reports</h5>
                    </div>
                </a>
            </div>
            <?php if ($is_admin): ?>
            <div class="col-md-3">
                <a href="settings.php" class="card quick-action text-decoration-none">
                    <div class="card-body text-center">
                        <i class="fas fa-cog fa-2x mb-2 text-primary"></i>
                        <h5>Settings</h5>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity Log -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Recent Activity Log</h5>
                <div class="table-responsive">
                    <table class="table table-dark">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="text-center">No recent activities</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>