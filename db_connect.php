<?php
// Set timezone to Philippines (Asia/Manila)
date_default_timezone_set('Asia/Manila');

// Get current timestamp and user
$current_datetime = '2025-05-01 15:47:07'; // Converting UTC 07:47:07 to PHT (+8)
$current_user = 'TJVillamil';

// Database connection configuration
$environment = getenv('APP_ENV') ?: 'development';

// Database configurations
$config = [
    'production' => [
        'servername' => "sql111.infinityfree.com",
        'username' => "if0_38858248",
        'password' => "",
        'dbname' => "if0_38858248_oten"
    ],
    'development' => [
        'servername' => "localhost",
        'username' => "root",
        'password' => "",
        'dbname' => "cpe46_localdb"
    ]
];

// Get the configuration based on environment
$dbConfig = $config[$environment];

// Connect to MySQL server
$conn = new mysqli(
    $dbConfig['servername'],
    $dbConfig['username'],
    $dbConfig['password']
);

// Check connection
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Set timezone for MySQL connection
$conn->query("SET time_zone = '+08:00'");

// Create database if it does not exist
$sql = "CREATE DATABASE IF NOT EXISTS {$dbConfig['dbname']}";
if (!$conn->query($sql)) {
    die("❌ Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbConfig['dbname']);

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
        image_path VARCHAR(255) DEFAULT 'assets/images/default/default-profile.png',
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

// Function to generate mock users
function generateMockUsers($conn, $count = 20) {
    // Sample data for generating mock users
    $firstNames = ['John', 'Maria', 'Michael', 'Sofia', 'James', 'Ana', 'David', 'Emma', 'Daniel', 'Isabella'];
    $middleNames = ['Santos', 'Cruz', 'Garcia', 'Reyes', 'Dela Cruz', 'Ramos', 'Torres', 'Lopez', 'Mendoza', 'Flores'];
    $lastNames = ['Tan', 'Lim', 'Yu', 'Santos', 'Reyes', 'Cruz', 'Garcia', 'Martinez', 'Rodriguez', 'Fernandez'];
    $courses = [
        ['code' => 'BSA', 'name' => 'BS in Architecture'],
        ['code' => 'BSCE', 'name' => 'BS in Civil Engineering'],
        ['code' => 'BSCpE', 'name' => 'BS in Computer Engineering'],
        ['code' => 'BSEE', 'name' => 'BS in Electrical Engineering'],
        ['code' => 'BSME', 'name' => 'BS in Mechanical Engineering']
    ];

    // First, ensure courses table is empty before inserting new courses
    $conn->query("TRUNCATE TABLE courses");

    // Insert courses
    foreach ($courses as $course) {
        $stmt = $conn->prepare("INSERT INTO courses (code, name) VALUES (?, ?)");
        $stmt->bind_param("ss", $course['code'], $course['name']);
        $stmt->execute();
        $stmt->close();
    }

    // Get role IDs
    $roleQuery = $conn->query("SELECT id FROM roles WHERE name = 'Student'");
    $roleId = $roleQuery->fetch_assoc()['id'];

    // Get status ID for 'active'
    $statusQuery = $conn->query("SELECT id FROM status WHERE name = 'active'");
    $statusId = $statusQuery->fetch_assoc()['id'];

    // Generate users
    for ($i = 0; $i < $count; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $middleName = $middleNames[array_rand($middleNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        
        // Generate student ID (2020-00001 format)
        $studentId = sprintf("2020-%05d", $i + 1);
        
        // Generate email
        $email = strtolower($firstName . '.' . $lastName . '@example.com');
        
        // Default password (hashed) - "password123"
        $password = password_hash("password123", PASSWORD_DEFAULT);
        
        // Random year level (1-4)
        $yearLevelId = rand(1, 4);
        
        // Random course
        $courseQuery = $conn->query("SELECT id FROM courses ORDER BY RAND() LIMIT 1");
        $courseId = $courseQuery->fetch_assoc()['id'];
        
        // Random merit/demerit points
        $merit = rand(0, 50);
        $demerit = rand(0, 20);

        // Prepare and execute insert statement
        $stmt = $conn->prepare("
            INSERT INTO users (
                email, password, role_id, student_id, year_level_id, 
                course_id, status_id, first_name, middle_name, last_name,
                merit, demerit, created_at
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $created_at = $current_datetime;
        $stmt->bind_param(
            "ssissiisssiii",
            $email, $password, $roleId, $studentId, $yearLevelId,
            $courseId, $statusId, $firstName, $middleName, $lastName,
            $merit, $demerit, $created_at
        );

        if (!$stmt->execute()) {
            echo "Error creating mock user: " . $stmt->error . "\n";
        }
        $stmt->close();
    }
    
    echo "✅ Generated $count mock users successfully!\n";
}

// Insert default values
insertDefaultData($conn, "roles", "name", ["Student", "Moderator", "Admin"]);
insertDefaultData($conn, "year_levels", "name", ["Freshmen", "Sophomore", "Junior", "Senior"]);
insertDefaultData($conn, "status", "name", ["pending", "approved", "rejected", "active", "inactive"]);
insertDefaultData($conn, "priority", "name", ["High Priority", "Mid Priority", "Low Priority"]);

// Generate mock users
generateMockUsers($conn, 20);

// Log database connection with Philippine time
$log_message = sprintf(
    "Database connection established at %s by user %s in %s environment (PHT)\n",
    $current_datetime,
    $current_user,
    $environment
);
error_log($log_message, 3, "database.log");

echo "✅ Database connection and setup completed successfully!\n";
echo "Environment: " . ucfirst($environment) . "\n";
echo "Connected to: {$dbConfig['dbname']}\n";
echo "Current Philippine Time: " . $current_datetime . "\n";

// Display sample user credentials and available courses
echo "\nSample User Credentials for Testing:\n";
echo "Email: [firstname].[lastname]@example.com (e.g., john.tan@example.com)\n";
echo "Password: password123\n";

echo "\nAvailable Courses:\n";
echo "- BSA  (BS in Architecture)\n";
echo "- BSCE (BS in Civil Engineering)\n";
echo "- BSCpE (BS in Computer Engineering)\n";
echo "- BSEE (BS in Electrical Engineering)\n";
echo "- BSME (BS in Mechanical Engineering)\n";
?>
