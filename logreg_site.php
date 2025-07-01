<?php
include 'db_connect.php';
session_start();

// Fetch year levels from the database
$yearLevels = [];
$yearQuery = "SELECT id, name FROM year_levels";
$yearResult = $conn->query($yearQuery);
while ($row = $yearResult->fetch_assoc()) {
    $yearLevels[] = $row;
}

// Fetch courses from the database
$courses = [];
$courseQuery = "SELECT id, name FROM courses";
$courseResult = $conn->query($courseQuery);
while ($row = $courseResult->fetch_assoc()) {
    $courses[] = $row;
}

// Determine which form to show (handles direct refresh)
$showForm = isset($_GET['show']) ? $_GET['show'] : 'login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register - Merit System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .nav-btn {
            background-color: white !important;
            color: #800000 !important;
            border: 2px solid white !important;
            border-radius: 25px;
            padding: 8px 25px;
            transition: all 0.3s ease;
        }

        .nav-btn-outline {
            color: white !important;
            border: 2px solid white !important;
            background: transparent !important;
            border-radius: 25px;
            padding: 8px 25px;
            transition: all 0.3s ease;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #800000 !important;
            border-color: #800000 !important;
            border-radius: 25px;
            padding: 12px 25px;
            transition: all 0.3s ease;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .input-group-text {
            background: white;
            color: #800000;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .image-section {
            flex: 1;
            background: #f8f8f8;
            display: flex;
            flex-direction: column;
            padding: 1rem;
            gap: 1rem;
            height: calc(100vh - 76px);
            overflow-y: auto;
        }

        .image-container {
            position: relative;
            height: 200px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .image-container:hover img {
            transform: scale(1.1);
            filter: blur(0) !important;
        }
    </style>
</head>
<body style="font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #ffffff 0%, #f8f8f8 100%); min-height: 100vh; margin: 0; padding: 0;">
    
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg" style="background-color: #800000 !important; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: fixed; width: 100%; top: 0; z-index: 1000;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php" style="color: white !important; font-size: 1.5rem;">
                <i class="fas fa-award me-2"></i>Merit System
            </a>
            <div>
                <a href="?show=login" class="btn nav-btn me-2">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
                <a href="?show=register" class="btn nav-btn-outline">
                    <i class="fas fa-user-plus me-2"></i>Register
                </a>
            </div>
        </div>
    </nav>

    <!-- Split Container -->
    <div class="split-container" style="display: flex; width: 100%; height: calc(100vh - 76px); padding-top: 76px;">
        <!-- Image Section -->
        <div class="image-section">
            <div class="image-container">
                <img src="assets/image1.jpeg" alt="Campus Life" style="filter: blur(1px);">
            </div>
            <div class="image-container">
                <img src="assets/image2.jpeg" alt="Students" style="filter: blur(1px);">
            </div>
            <div class="image-container">
                <img src="assets/image3.jpeg" alt="Achievement" style="filter: blur(1px);">
            </div>
            <div class="image-container">
                <img src="assets/image4.jpeg" alt="Education" style="filter: blur(1px);">
            </div>
            <div class="image-container">
                <img src="assets/image5.jpeg" alt="Success" style="filter: blur(1px);">
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section" style="flex: 1; display: flex; justify-content: center; align-items: center; padding: 1rem; height: calc(100vh - 76px); overflow-y: auto;">
            <div class="card p-4 shadow-lg" style="border: none; border-radius: 15px; background: rgba(255, 255, 255, 0.95); box-shadow: 0 10px 30px rgba(128,0,0,0.08); width: 100%; max-width: 400px;">
                
                <!-- Login Form -->
                <div id="loginForm" <?= ($showForm === "login") ? '' : 'style="display: none;"' ?>>
                    <h2 class="text-center mb-4">Welcome Back</h2>

                    <!-- Display Login Messages -->
                    <?php if (isset($_SESSION['login_error'])) : ?>
                        <div class="alert alert-danger alert-dismissible fade show text-center auto-dismiss">
                            <i class="fas fa-exclamation-circle me-1"></i> <?= $_SESSION['login_error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['login_error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['login_success'])) : ?>
                        <div class="alert alert-success alert-dismissible fade show text-center auto-dismiss">
                            <i class="fas fa-check-circle me-1"></i> <?= $_SESSION['login_success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['login_success']); ?>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" class="form-control" placeholder="Email (@su.edu.ph)" required>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle me-1"></i>Default password: {Lastname}1234
                        </small>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                </div>

                <!-- Registration Form -->
                <div id="registerForm" <?= ($showForm === "register") ? '' : 'style="display: none;"' ?>>
                    <h2 class="text-center mb-4">Create Account</h2>

                    <!-- Display Registration Messages -->
                    <?php if (isset($_SESSION['error_message'])) : ?>
                        <div class="alert alert-danger alert-dismissible fade show text-center auto-dismiss">
                            <i class="fas fa-exclamation-circle me-1"></i> <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_message'])) : ?>
                        <div class="alert alert-success alert-dismissible fade show text-center auto-dismiss">
                            <i class="fas fa-check-circle me-1"></i> <?= $_SESSION['success_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                        </div>
                        
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="middle_name" class="form-control" placeholder="Middle Name" required>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text" name="student_id" class="form-control" placeholder="Student ID (00-0-00000)" required>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" class="form-control" placeholder="Email (@su.edu.ph)" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-graduation-cap me-2"></i>Year Level
                            </label>
                            <select name="year_level_id" class="form-select" required>
                                <option value="">Select Year Level</option>
                                <?php foreach ($yearLevels as $year) : ?>
                                    <option value="<?= $year['id'] ?>"><?= $year['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-book me-2"></i>Course
                            </label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course) : ?>
                                    <option value="<?= $course['id'] ?>"><?= $course['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation functions
        function validateName(input) {
            const pattern = /^[A-Za-zñÑ\s]+$/;
            const isValid = pattern.test(input.value);
            input.classList.toggle("is-invalid", !isValid);
            return isValid;
        }

        function validateEmail(input) {
            const pattern = /^[a-zA-Z0-9._%+-]+@su\.edu\.ph$/;
            const isValid = pattern.test(input.value);
            input.classList.toggle("is-invalid", !isValid);
            return isValid;
        }

        function validateStudentId(input) {
            const pattern = /^\d{2}-\d{1}-\d{5}$/;
            const isValid = pattern.test(input.value);
            input.classList.toggle("is-invalid", !isValid);
            return isValid;
        }

        // Add form validation listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Form switching logic
            const params = new URLSearchParams(window.location.search);
            const showForm = params.get("show") || "login";

            document.getElementById("loginForm").style.display = showForm === "login" ? "block" : "none";
            document.getElementById("registerForm").style.display = showForm === "register" ? "block" : "none";

            // Login form validation
            const loginForm = document.querySelector('#loginForm form');
            const loginEmail = loginForm.querySelector('input[name="email"]');

            loginForm.addEventListener('submit', function(e) {
                if (!validateEmail(loginEmail)) {
                    e.preventDefault();
                    alert('Please enter a valid @su.edu.ph email address');
                }
            });

            // Registration form validation
            const registerForm = document.querySelector('#registerForm form');
            const firstName = registerForm.querySelector('input[name="first_name"]');
            const middleName = registerForm.querySelector('input[name="middle_name"]');
            const lastName = registerForm.querySelector('input[name="last_name"]');
            const studentId = registerForm.querySelector('input[name="student_id"]');
            const registerEmail = registerForm.querySelector('input[name="email"]');

            registerForm.addEventListener('submit', function(e) {
                let isValid = true;

                if (!validateName(firstName)) {
                    isValid = false;
                    alert('First name should only contain letters');
                }

                if (!validateName(middleName)) {
                    isValid = false;
                    alert('Middle name should only contain letters');
                }

                if (!validateName(lastName)) {
                    isValid = false;
                    alert('Last name should only contain letters');
                }

                if (!validateStudentId(studentId)) {
                    isValid = false;
                    alert('Invalid Student ID format. Use: XX-X-XXXXX');
                }

                if (!validateEmail(registerEmail)) {
                    isValid = false;
                    alert('Please enter a valid @su.edu.ph email address');
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Real-time validation
            firstName?.addEventListener('input', () => validateName(firstName));
            middleName?.addEventListener('input', () => validateName(middleName));
            lastName?.addEventListener('input', () => validateName(lastName));
            studentId?.addEventListener('input', () => validateStudentId(studentId));
            loginEmail?.addEventListener('input', () => validateEmail(loginEmail));
            registerEmail?.addEventListener('input', () => validateEmail(registerEmail));
        });

        // Form switching with buttons
        document.querySelectorAll('.navbar .btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const isLogin = this.href.includes('show=login');
                document.getElementById('loginForm').style.display = isLogin ? 'block' : 'none';
                document.getElementById('registerForm').style.display = isLogin ? 'none' : 'block';
                window.history.pushState({}, '', `?show=${isLogin ? 'login' : 'register'}`);
            });
        });

        // Add auto-dismiss functionality for alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.auto-dismiss');
            alerts.forEach(alert => {
                // Set timeout to dismiss after 10 seconds
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>