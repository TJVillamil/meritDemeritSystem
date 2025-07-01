<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Debug logging function
function debug_log($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $user = $_SESSION['user_id'] ?? 'unknown';
    $log_message = "[{$timestamp}] [User: {$user}] {$message}";
    
    if ($data !== null) {
        $log_message .= "\nData: " . print_r($data, true);
    }
    
    $log_message .= "\n" . str_repeat('-', 80) . "\n";
    
    // Append to production.log in the same directory as the script
    error_log($log_message, 3, dirname(__FILE__) . '/production.log');
}

// Set timezone to UTC for consistent timestamps
date_default_timezone_set('UTC');

try {
    debug_log("Starting dashboard stats fetch");
    
    include 'db_connect.php';
    require_once 'session_checker.php';

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    $user_id = $_SESSION['user_id'];
    debug_log("Processing request for user_id: {$user_id}");

    // Test database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    debug_log("Database connection successful");

    // Function to test if a table exists
    function tableExists($conn, $tableName) {
        $result = $conn->query("SHOW TABLES LIKE '{$tableName}'");
        $exists = $result && $result->num_rows > 0;
        debug_log("Checking table {$tableName}", ["exists" => $exists]);
        return $exists;
    }

    // Verify required tables exist
    $requiredTables = ['users', 'courses', 'year_levels', 'status', 'announcements', 'priority'];
    foreach ($requiredTables as $table) {
        if (!tableExists($conn, $table)) {
            throw new Exception("Required table {$table} does not exist");
        }
    }

    function getUserDetails($conn, $user_id) {
        try {
            debug_log("Getting user details", ["user_id" => $user_id]);

            // First verify user exists
            $check_query = "SELECT COUNT(*) as count FROM users WHERE id = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count == 0) {
                throw new Exception("User ID {$user_id} not found in database");
            }
            
            // Get basic user data
            $query = "SELECT 
                        u.id,
                        u.email,
                        u.student_id,
                        u.first_name,
                        u.middle_name,
                        u.last_name,
                        u.merit,
                        u.demerit,
                        u.points,
                        u.image_path,
                        u.year_level_id,
                        u.course_id,
                        u.status_id,
                        c.name as course_name,
                        y.name as year_name,
                        s.name as status_name
                    FROM users u
                    LEFT JOIN courses c ON u.course_id = c.id
                    LEFT JOIN year_levels y ON u.year_level_id = y.id
                    LEFT JOIN status s ON u.status_id = s.id
                    WHERE u.id = ?";

            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare user query: " . $conn->error);
            }

            $stmt->bind_param("i", $user_id);
            
            debug_log("Executing user query for ID: {$user_id}");
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute user query: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                throw new Exception("No user data returned for ID: {$user_id}");
            }

            debug_log("Raw user data retrieved", $user);

            // Ensure all fields have default values
            $defaults = [
                'email' => '',
                'student_id' => 'N/A',
                'first_name' => '',
                'middle_name' => '',
                'last_name' => '',
                'merit' => 0,
                'demerit' => 0,
                'points' => 0,
                'image_path' => 'assets/images/default_user_image.jpg',
                'year_level_id' => 1,
                'course_id' => 1,
                'course_name' => 'Unassigned',
                'year_name' => 'Unassigned',
                'status_id' => 1,
                'status_name' => 'pending'
            ];

            foreach ($defaults as $key => $default) {
                if (!isset($user[$key]) || is_null($user[$key])) {
                    $user[$key] = $default;
                    debug_log("Setting default value for {$key}", ["value" => $default]);
                }
            }

            // Convert numeric values to integers
            $numericFields = ['id', 'merit', 'demerit', 'points', 'year_level_id', 'course_id', 'status_id'];
            foreach ($numericFields as $field) {
                if (isset($user[$field])) {
                    $user[$field] = (int)$user[$field];
                }
            }

            debug_log("Processed user data", $user);
            return $user;

        } catch (Exception $e) {
            debug_log("Error in getUserDetails", [
                "error" => $e->getMessage(),
                "user_id" => $user_id,
                "trace" => $e->getTraceAsString()
            ]);
            
            return [
                'error' => 'Failed to fetch user details',
                'id' => $user_id,
                'email' => '',
                'student_id' => 'N/A',
                'first_name' => '',
                'middle_name' => '',
                'last_name' => '',
                'merit' => 0,
                'demerit' => 0,
                'points' => 0,
                'image_path' => 'assets/images/default_user_image.jpg',
                'year_level_id' => 1,
                'course_id' => 1,
                'course_name' => 'Unassigned',
                'year_name' => 'Unassigned',
                'status_id' => 1,
                'status_name' => 'pending'
            ];
        }
    }

    function getAnnouncements($conn) {
        try {
            debug_log("Fetching announcements");
            
            $query = "SELECT 
                        a.message,
                        DATE_FORMAT(a.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
                        COALESCE(p.name, 'Low Priority') as priority_name
                    FROM announcements a
                    LEFT JOIN priority p ON a.priority_id = p.id
                    WHERE a.message IS NOT NULL AND a.message != ''
                    ORDER BY 
                        CASE p.name 
                            WHEN 'High Priority' THEN 1
                            WHEN 'Mid Priority' THEN 2
                            ELSE 3
                        END,
                        a.created_at DESC
                    LIMIT 5";

            $result = $conn->query($query);
            if (!$result) {
                throw new Exception("Failed to fetch announcements: " . $conn->error);
            }

            $announcements = [];
            while ($row = $result->fetch_assoc()) {
                $announcements[] = [
                    'message' => $row['message'] ?? '',
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'priority_name' => $row['priority_name'] ?? 'Low Priority'
                ];
            }

            debug_log("Announcements fetched", ["count" => count($announcements)]);
            return $announcements;

        } catch (Exception $e) {
            debug_log("Error in getAnnouncements", ["error" => $e->getMessage()]);
            return [];
        }
    }

    function getUserStats($conn, $user_id) {
        try {
            debug_log("Fetching user stats", ["user_id" => $user_id]);
            
            $query = "SELECT 
                        COALESCE(merit, 0) as merit,
                        COALESCE(demerit, 0) as demerit,
                        COALESCE(points, 0) as points
                    FROM users 
                    WHERE id = ?";

            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare stats query: " . $conn->error);
            }

            $stmt->bind_param("i", $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute stats query: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();

            if (!$stats) {
                throw new Exception("No stats found for user: {$user_id}");
            }

            // Ensure all values are integers
            $stats = array_map('intval', $stats);
            
            debug_log("User stats retrieved", $stats);
            return $stats;

        } catch (Exception $e) {
            debug_log("Error in getUserStats", [
                "error" => $e->getMessage(),
                "user_id" => $user_id
            ]);
            return ['merit' => 0, 'demerit' => 0, 'points' => 0];
        }
    }

    // Fetch all data
    $currentTime = date('Y-m-d H:i:s');
    debug_log("Starting data collection at {$currentTime}");

    $user_details = getUserDetails($conn, $user_id);
    $announcements = getAnnouncements($conn);
    $stats = getUserStats($conn, $user_id);

    $response = [
        'timestamp' => $currentTime,
        'user' => $user_details,
        'announcements' => $announcements,
        'stats' => $stats,
        'server_info' => [
            'timezone' => 'UTC',
            'request_time' => $currentTime
        ]
    ];

    debug_log("Preparing final response", [
        "has_user_data" => !empty($user_details),
        "announcement_count" => count($announcements),
        "has_stats" => !empty($stats)
    ]);

    // Set headers and return response
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    echo json_encode($response);

} catch (Exception $e) {
    debug_log("Critical error in dashboard", [
        "error" => $e->getMessage(),
        "trace" => $e->getTraceAsString(),
        "user_id" => $user_id ?? 'not set'
    ]);
    
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while fetching dashboard data',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'fallback_data' => [
            'user' => [
                'id' => $user_id ?? 0,
                'email' => '',
                'student_id' => 'N/A',
                'first_name' => '',
                'middle_name' => '',
                'last_name' => '',
                'merit' => 0,
                'demerit' => 0,
                'points' => 0,
                'image_path' => 'assets/images/default_user_image.jpg',
                'year_level_id' => 1,
                'course_id' => 1,
                'course_name' => 'Unassigned',
                'year_name' => 'Unassigned',
                'status_id' => 1,
                'status_name' => 'pending'
            ],
            'announcements' => [],
            'stats' => ['merit' => 0, 'demerit' => 0, 'points' => 0]
        ]
    ]);
}
?>