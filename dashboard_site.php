<?php
session_start();
require_once 'session_checker.php';
check_session(false); // Regular user dashboard

// Check if user ID is set in the session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #800000;
            --primary-light: #a63f3f;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f1c40f;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 15px rgba(0,0,0,0.08);
            --hover-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        body { 
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
            color: var(--secondary-color);
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
        }

        .card { 
            background: white;
            margin-bottom: 20px;
            position: relative;
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover { 
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover::before {
            opacity: 1;
        }

        .dashboard-container { 
            margin-top: 90px;
            padding-bottom: 40px;
        }

        .user-info {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 30px;
            border-radius: 25px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: 0 10px 30px rgba(128, 0, 0, 0.2);
        }

        .user-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 4px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .user-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .user-image:hover img {
            transform: scale(1.1);
        }

        .total-points {
            text-align: center;
            min-width: 150px;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .stat-icon {
            font-size: 3.5rem;
            opacity: 0.9;
            margin-right: 15px;
            transition: transform 0.3s ease;
        }

        .card:hover .stat-icon {
            transform: scale(1.1);
        }

        .card-body {
            display: flex;
            align-items: center;
            justify-content: start;
            gap: 20px;
            padding: 25px;
        }

        .stat-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .stat-text h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .stat-text p {
            margin: 5px 0 0;
            font-size: 1.25rem;
            color: #6c757d;
        }

        #announcement-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #announcement-list li {
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: background-color 0.3s ease;
        }

        #announcement-list li:last-child {
            border-bottom: none;
        }

        #announcement-list li:hover {
            background-color: rgba(0,0,0,0.02);
            padding-left: 10px;
        }

        .btn-outline-light {
            border-width: 2px;
            font-weight: 500;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .btn-outline-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeIn 0.5s ease-out forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .user-info {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }

            .user-details {
                text-align: center;
            }

            .total-points {
                width: 100%;
                margin-top: 15px;
            }

            .card-body {
                flex-direction: column;
                text-align: center;
            }

            .stat-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .stat-text {
                align-items: center;
            }
        }

        /* Loading Skeleton Animation */
        @keyframes skeleton-loading {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .loading {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
            height: 1em;
            display: inline-block;
            min-width: 100px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="dashboard_site.php">
                <i class="fas fa-award me-2"></i>Student Dashboard
            </a>
            <div class="d-flex align-items-center">
                <span id="user-name" class="text-white me-3">
                    <i class="fas fa-user me-2"></i><span class="loading">Loading...</span>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill ms-2">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        <!-- User Info Card -->
        <div id="user-info" class="user-info">
            <div class="user-image">
                <img id="user-image" src="default_user_image.jpg" alt="User Image">
            </div>
            <div class="user-details flex-grow-1">
                <h4 id="user-full-name" class="mb-2"><span class="loading">Loading...</span></h4>
                <p class="mb-0">
                    <span id="user-student-id" class="me-3"><i class="fas fa-id-card me-2"></i><span class="loading">Loading...</span></span>
                    <span id="user-course" class="me-3"><i class="fas fa-graduation-cap me-2"></i><span class="loading">Loading...</span></span>
                    <span id="user-year"><i class="fas fa-layer-group me-2"></i><span class="loading">Loading...</span></span>
                </p>
            </div>
            <div class="total-points">
                <h4 class="text-warning mb-2">Total Points</h4>
                <p id="user-points" class="fs-3 fw-bold text-warning mb-0"><span class="loading">Loading...</span></p>
            </div>
            <a href="view_profile.php" class="btn btn-outline-light btn-sm rounded-pill">
                <i class="fas fa-user-circle me-2"></i>View Profile
            </a>    
        </div>

        <!-- Announcements -->
        <div class="card">
            <div class="card-body">
                <div class="w-100">
                    <h4 class="fw-bold mb-4">
                        <i class="fas fa-bullhorn me-2 text-primary"></i>
                        Announcements
                    </h4>
                    <ul id="announcement-list">
                        <li><span class="loading">Loading...</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Merit and Demerit Statistics -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card" onclick="window.location.href='merit_site.php'">
                    <div class="card-body">
                        <i class="fas fa-plus-circle stat-icon text-success"></i>
                        <div class="stat-text">
                            <h4 class="text-success">Merits</h4>
                            <p id="user-merit" class="fs-5"><span class="loading">Loading...</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" onclick="window.location.href='demerit_site.php'">
                    <div class="card-body">
                        <i class="fas fa-minus-circle stat-icon text-danger"></i>
                        <div class="stat-text">
                            <h4 class="text-danger">Demerits</h4>
                            <p id="user-demerit" class="fs-5"><span class="loading">Loading...</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card" onclick="window.location.href='leaderstats_site.php'">
                    <div class="card-body">
                        <i class="fas fa-chart-line stat-icon text-primary"></i>
                        <div class="stat-text">
                            <h4 class="text-primary fw-bold">Leaderstats</h4>
                            <p class="fs-5 text-primary">View Rankings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fetchDashboardStats() {
            fetch('fetch_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    // Remove loading classes
                    document.querySelectorAll('.loading').forEach(el => {
                        el.classList.remove('loading');
                    });

                    // Populate user details with fade animation
                    const user = data.user;
                    const updateWithFade = (elementId, content) => {
                        const element = document.getElementById(elementId);
                        element.style.opacity = '0';
                        setTimeout(() => {
                            element.innerHTML = content;
                            element.style.opacity = '1';
                        }, 200);
                    };

                    updateWithFade('user-name', `${user.first_name}`);
                    updateWithFade('user-full-name', `${user.first_name} ${user.last_name}`);
                    updateWithFade('user-student-id', `<i class="fas fa-id-card me-2"></i>${user.student_id}`);
                    updateWithFade('user-course', `<i class="fas fa-graduation-cap me-2"></i>${user.course_name}`);
                    updateWithFade('user-year', `<i class="fas fa-layer-group me-2"></i>${user.year_name}`);
                    document.getElementById('user-image').src = user.image_path ? user.image_path : 'default_user_image.jpg';
                    updateWithFade('user-points', user.points);

                    // Populate announcements with staggered animation
                    const announcements = data.announcements;
                    const announcementList = document.getElementById('announcement-list');
                    announcementList.innerHTML = '';
                    if (announcements.length > 0) {
                        announcements.forEach((announcement, index) => {
                            const li = document.createElement('li');
                            li.style.opacity = '0';
                            li.style.transform = 'translateY(20px)';
                            li.textContent = announcement.message;
                            announcementList.appendChild(li);
                            
                            setTimeout(() => {
                                li.style.transition = 'all 0.3s ease';
                                li.style.opacity = '1';
                                li.style.transform = 'translateY(0)';
                            }, index * 100);
                        });
                    } else {
                        announcementList.innerHTML = '<p class="text-muted">No announcements at the moment.</p>';
                    }

                    // Populate stats with animation
                    const stats = data.stats;
                    updateWithFade('user-merit', `+${stats.merit}`);
                    updateWithFade('user-demerit', `-${stats.demerit}`);
                })
                .catch(error => console.error('Error fetching dashboard stats:', error));
        }

        // Fetch stats immediately and then every 5 seconds
        document.addEventListener('DOMContentLoaded', function () {
            fetchDashboardStats();
            setInterval(fetchDashboardStats, 5000); // 5 seconds interval
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>