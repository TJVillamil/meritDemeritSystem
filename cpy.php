for dashboard_admin.php it will contain 2 viewport admin and moderator, they will botth have the same status total number of users, total active users, total inactive users, total demerits of all users existing in the system and history of announcement. For admin only cards it will have amange moderators, manage users, review request of pending merits and merits, leaderstats. For moderator cards only, we will have manage users(limited only to users who is the same year_level with the mdoerator), create announcements, history of assigned merits and demerits altogether and lastly leader stats. 

here is the db_connection for reference
<?php
// Database connection configuration

// Production (Online)
// $servername = "sql111.infinityfree.com";
// $username = "if0_38858248"; // Replace with your production DB username
// $password = "otensquared"; // Replace with your production DB password
// $dbname = "if0_38858248_oten";

// // Local Development
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cpe46_localdb";

// Connect to MySQL server
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Create database if it does not exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (!$conn->query($sql)) {
    die("❌ Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Array of SQL queries for creating tables
$tables = [
    "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name ENUM('Student', 'Moderator', 'Admin') NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS status (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name ENUM('pending', 'approved', 'rejected', 'active', 'inactive') NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS year_levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(10) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) NOT NULL,
        name VARCHAR(100) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS priority (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name ENUM('High Priority', 'Mid Priority', 'Low Priority') NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        role_id INT NOT NULL,
        student_id VARCHAR(10),
        year_level_id INT NOT NULL,
        course_id INT NOT NULL,
        status_id INT NOT NULL,
        first_name VARCHAR(20),
        middle_name VARCHAR(20),
        last_name VARCHAR(20),
        merit INT DEFAULT 0,
        demerit INT DEFAULT 0,
        points INT GENERATED ALWAYS AS (merit - demerit) STORED,
        password_changed_at TIMESTAMP NULL,
        last_login TIMESTAMP NULL,
        FOREIGN KEY (role_id) REFERENCES roles(id),
        FOREIGN KEY (year_level_id) REFERENCES year_levels(id),
        FOREIGN KEY (course_id) REFERENCES courses(id),
        FOREIGN KEY (status_id) REFERENCES status(id)
    )",
    "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message VARCHAR(255) NOT NULL,
        priority_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (priority_id) REFERENCES priority(id)
    )",
    "CREATE TABLE IF NOT EXISTS merits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        moderator_id INT NOT NULL,
        amount INT NOT NULL,
        reason TEXT NOT NULL,
        date_given TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_approved TIMESTAMP NULL,
        status_id INT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (moderator_id) REFERENCES users(id),
        FOREIGN KEY (status_id) REFERENCES status(id)
    )",
    "CREATE TABLE IF NOT EXISTS demerits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        moderator_id INT NOT NULL,
        amount INT NOT NULL,
        reason TEXT NOT NULL,
        date_given TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_approved TIMESTAMP NULL,
        status_id INT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (moderator_id) REFERENCES users(id),
        FOREIGN KEY (status_id) REFERENCES status(id)
    )"
];

// Execute table creation
foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        die("❌ Error creating table: " . $conn->error);
    }
}

// Ensure the `merit`, `demerit`, and `points` columns exist in the `users` table
$columns_to_check = [
    'merit' => "ALTER TABLE users ADD COLUMN merit INT DEFAULT 0 AFTER last_name",
    'demerit' => "ALTER TABLE users ADD COLUMN demerit INT DEFAULT 0 AFTER merit",
    'points' => "ALTER TABLE users ADD COLUMN points INT GENERATED ALWAYS AS (merit - demerit) STORED AFTER demerit"
];

foreach ($columns_to_check as $column => $alter_query) {
    $column_check_query = "
        SELECT COUNT(*) AS column_exists 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = '$dbname' 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME = '$column'";
    $column_check_result = $conn->query($column_check_query);
    if ($column_check_result) {
        $row = $column_check_result->fetch_assoc();
        if ($row['column_exists'] == 0) {
            if (!$conn->query($alter_query)) {
                die("❌ Error adding $column column: " . $conn->error);
            }
        }
    }
}

// Insert default Roles, Year Levels, Statuses, and Priorities
function insertDefaultData($conn, $table, $column, $values) {
    foreach ($values as $value) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        if (!$stmt) {
            die("❌ Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            $stmt = $conn->prepare("INSERT INTO $table ($column) VALUES (?)");
            if (!$stmt) {
                die("❌ Error preparing statement: " . $conn->error);
            }
            $stmt->bind_param("s", $value);
            $stmt->execute();
            $stmt->close();
        }
    }
}

insertDefaultData($conn, "roles", "name", ["Student", "Moderator", "Admin"]);
insertDefaultData($conn, "year_levels", "name", ["Freshmen", "Sophomore", "Junior", "Senior"]);
insertDefaultData($conn, "status", "name", ["Pending", "Approved", "Rejected", "Active", "Inactive"]);
insertDefaultData($conn, "priority", "name", ["High Priority", "Mid Priority", "Low Priority"]);
?>

here is the current dashboard_admin.php
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

remove those not in my description