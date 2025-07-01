<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (headers_sent($file, $line)) {
    die("Headers already sent in $file on line $line");
}

function check_session($is_admin_page = false) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
        $_SESSION['login_error'] = "❌ Please login to continue.";
        header("Location: logreg_site.php?show=login");
        exit();
    }

    if ($is_admin_page) {
        // Check if user is admin or moderator (role_id 2 or 3)
        if ($_SESSION['role_id'] == 1) {
            $_SESSION['login_error'] = "❌ You don't have permission to access this page.";
            header("Location: dashboard_site.php");
            exit();
        }
    } else {
        // Check if user is student (role_id 1)
        if ($_SESSION['role_id'] != 1) {
            header("Location: dashboard_admin.php");
            exit();
        }
    }
}
?>