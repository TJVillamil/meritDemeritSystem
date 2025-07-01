<?php
include 'db_connect.php';
session_start();

// Add this at the top of both login.php and register.php after session_start():
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add this right before any header() redirect:
if (headers_sent($file, $line)) {
    die("Headers already sent in $file on line $line");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $student_id = $_POST['student_id'];
    $email = $_POST['email'];
    $year_level_id = $_POST['year_level_id'];
    $course_id = $_POST['course_id'];

    // Validate names (No numbers, only letters, spaces, and ñ)
    $namePattern = "/^[A-Za-zñÑ\s]+$/";
    if (!preg_match($namePattern, $first_name)) {
        $_SESSION['error_message'] = "❌ First Name cannot contain numbers!";
        header("Location: logreg_site.php?show=register");
        exit();
    }
    if (!preg_match($namePattern, $middle_name)) {
        $_SESSION['error_message'] = "❌ Middle Name cannot contain numbers!";
        header("Location: logreg_site.php?show=register");
        exit();
    }
    if (!preg_match($namePattern, $last_name)) {
        $_SESSION['error_message'] = "❌ Last Name cannot contain numbers!";
        header("Location: logreg_site.php?show=register");
        exit();
    }

    // Validate Student ID Format (00-0-00000)
    if (!preg_match("/^\d{2}-\d{1}-\d{5}$/", $student_id)) {
        $_SESSION['error_message'] = "❌ Invalid Student ID format! Example: 23-1-12345";
        header("Location: logreg_site.php?show=register");
        exit();
    }

    // Validate Email Format (Must end in @su.edu.ph)
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@su\.edu\.ph$/", $email)) {
        $_SESSION['error_message'] = "❌ Email must be in @su.edu.ph format!";
        header("Location: logreg_site.php?show=register");
        exit();
    }

    // Ensure Year Level and Course are selected
    if (empty($year_level_id) || empty($course_id)) {
        $_SESSION['error_message'] = "❌ Please select a valid Year Level and Course!";
        header("Location: logreg_site.php?show=register");
        exit();
    }

    // Check for Duplicate Entry (Prevent duplicate emails and student IDs)
    $checkDuplicate = $conn->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
    $checkDuplicate->bind_param("ss", $email, $student_id);
    $checkDuplicate->execute();
    $checkDuplicate->store_result();

    if ($checkDuplicate->num_rows > 0) {
        $_SESSION['error_message'] = "❌ You are already registered!";
        header("Location: logreg_site.php?show=register");
        exit();
    }
    $checkDuplicate->close();

    // Insert Data into Users Table
    $role_id = 1; // Default role: Student
    $status_id = 4; // Default status: Active
    $default_password = password_hash($last_name . "1234", PASSWORD_DEFAULT); // Secure password hashing

    $stmt = $conn->prepare("INSERT INTO users (email, password, role_id, student_id, year_level_id, course_id, first_name, middle_name, last_name, status_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssississsi", $email, $default_password, $role_id, $student_id, $year_level_id, $course_id, $first_name, $middle_name, $last_name, $status_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ Registration successful! Default password: {$last_name}1234";
        header("Location: logreg_site.php?show=register");
        exit();
    } else {
        $_SESSION['error_message'] = "❌ Registration failed!";
        header("Location: logreg_site.php?show=register");
        exit();
    }
}
?>
