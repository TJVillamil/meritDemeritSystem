<?php
session_start();
include 'db_connect.php';
require_once 'session_checker.php';
check_session(false); // Regular user dashboard

// Validate if user_id exists in the session
if (!isset($_SESSION['user_id'])) {
    die("Error: User is not logged in. Please log in to access the leaderboard.");
}

$user_id = $_SESSION['user_id'];

// Fetch user details for navbar
$user_query = "SELECT first_name FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

// Check if user data was retrieved
if (!$user_data) {
    die("Error: User not found. Please contact support.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderstats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            color: #2c3e50;
        }

        .navbar {
            background: linear-gradient(135deg, #800000 0%, #a83232 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            color: white !important;
            font-size: 1.4rem;
            transition: transform 0.2s;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .page-title {
            color: #2c3e50;
            font-weight: 600;
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .page-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #800000 0%, #a83232 100%);
            border-radius: 2px;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: linear-gradient(135deg, #800000 0%, #a83232 100%);
            color: white;
            font-weight: 500;
            border: none;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f1f1;
        }

        .table tbody tr {
            transition: background-color 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .pagination-controls {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 1.5rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #eaeaea;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #800000;
            box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
        }

        .rank-badge {
            background: #800000;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .points-badge {
            background: #28a745;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .course-badge {
            background: #17a2b8;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* Animation for table rows */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table tbody tr {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .pagination-controls {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .table-responsive {
                border-radius: 16px;
                overflow: hidden;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard_site.php">
                <i class="fas fa-trophy me-2"></i>Student Dashboard
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="fas fa-user-circle me-2"></i>
                    <?= htmlspecialchars($user_data['first_name']) ?>
                </span>
                <a href="logout.php" class="btn btn-light btn-sm rounded-pill ms-2">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <h1 class="page-title text-center">Leaderboard Rankings</h1>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover" id="leaderboard-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-medal me-2"></i>Rank</th>
                            <th><i class="fas fa-user me-2"></i>Name</th>
                            <th><i class="fas fa-graduation-cap me-2"></i>Course</th>
                            <th><i class="fas fa-star me-2"></i>Total Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic Content Goes Here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination and Display Options -->
        <div class="pagination-controls">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="info" id="user-info"></div>
                </div>
                <div class="col-md-4 text-center">
                    <form class="d-flex align-items-center justify-content-center gap-2" onsubmit="goToPage(event)">
                        <span>Page</span>
                        <input type="number" id="page-input" class="form-control form-control-sm" value="1" min="1" style="width: 80px"/>
                        <span id="total-pages"></span>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <label for="items-per-page" class="me-2">Show entries:</label>
                    <select id="items-per-page" class="form-select form-select-sm d-inline-block" style="width: auto" onchange="updateLimit(this.value)">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let itemsPerPage = 20;

        function fetchLeaderboard() {
            fetch(`fetch_leaderboard.php?limit=${itemsPerPage}&page=${currentPage}`)
                .then(response => response.json())
                .then(data => {
                    updateLeaderboard(data);
                })
                .catch(error => {
                    console.error("Error fetching leaderboard:", error);
                });
        }

        function updateLeaderboard(data) {
            const tbody = document.querySelector('#leaderboard-table tbody');
            tbody.innerHTML = '';

            if (data.leaderboard.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <i class="fas fa-info-circle me-2"></i>No data found
                        </td>
                    </tr>`;
                document.getElementById('user-info').textContent = '';
                document.getElementById('total-pages').textContent = '';
                document.getElementById('page-input').max = 1;
                return;
            }

            data.leaderboard.forEach((user, index) => {
                const rank = (currentPage - 1) * itemsPerPage + index + 1;
                const row = `
                    <tr>
                        <td><span class="rank-badge">#${rank}</span></td>
                        <td>
                            <i class="fas fa-user-circle me-2"></i>
                            ${user.name}
                        </td>
                        <td><span class="course-badge">${user.course || 'N/A'}</span></td>
                        <td><span class="points-badge">${user.points} pts</span></td>
                    </tr>`;
                tbody.innerHTML += row;
            });

            const info = document.getElementById('user-info');
            info.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                Showing ${(currentPage - 1) * itemsPerPage + 1}-${Math.min(currentPage * itemsPerPage, data.total_users)} 
                of ${data.total_users} users
            `;

            const totalPages = document.getElementById('total-pages');
            totalPages.textContent = `of ${data.total_pages}`;
            document.getElementById('page-input').max = data.total_pages;
        }

        function goToPage(event) {
            event.preventDefault();
            const pageInput = document.getElementById('page-input');
            const page = parseInt(pageInput.value);
            if (page >= 1) {
                currentPage = page;
                fetchLeaderboard();
            }
        }

        function updateLimit(limit) {
            itemsPerPage = limit;
            currentPage = 1;
            fetchLeaderboard();
        }

        // Auto-reload leaderboard every 5 seconds
        setInterval(fetchLeaderboard, 5000);

        // Initial fetch
        fetchLeaderboard();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>