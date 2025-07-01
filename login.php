<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input fields
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "❌ Please fill in all fields!";
        header("Location: logreg_site.php?show=login");
        exit();
    }

    // Query to check user credentials and role
    $sql = "SELECT id, email, password, role_id, status_id FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Check if account is active
        if ($row['status_id'] != 4) {
            $_SESSION['login_error'] = "❌ Your account is not active. Please contact the administrator.";
            header("Location: logreg_site.php?show=login");
            exit();
        }

        if (password_verify($password, $row['password'])) {
            // Store session data
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role_id'] = $row['role_id'];

            // Redirect based on role
            if ($row['role_id'] == 1) {
                // Student
                $_SESSION['login_success'] = "✅ Welcome, Student!";
                header("Location: dashboard_site.php");
            } else {
                // Moderator (role_id = 2) or Admin (role_id = 3)
                $_SESSION['login_success'] = "✅ Welcome, Administrator!";
                header("Location: dashboard_admin.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = "❌ Incorrect password!";
            header("Location: logreg_site.php?show=login");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "❌ Email not found!";
        header("Location: logreg_site.php?show=login");
        exit();
    }
}
?>