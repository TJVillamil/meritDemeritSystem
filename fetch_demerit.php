<?php
session_start();
include 'db_connect.php';
require_once 'session_checker.php';
check_session(false);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Validate pagination parameters
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 20;
if ($limit > 100) $limit = 100;

header('Content-Type: application/json'); // Set content type to JSON

try {
    // Get total count of records
    $count_query = "SELECT COUNT(*) as total FROM demerits WHERE user_id = ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_result = $stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_records = $total_row['total'];
    
    // If there are no records, return an empty response
    if ($total_records == 0) {
        echo json_encode([
            'records' => [],
            'total_records' => 0,
            'total_pages' => 1,
            'current_page' => 1,
            'limit' => $limit
        ]);
        exit;
    }

    $total_pages = ceil($total_records / $limit);
    $offset = ($page - 1) * $limit;
    
    // Get records for current page
    $query = "SELECT id, amount, reason, date_given, status 
              FROM demerits 
              WHERE user_id = ? 
              ORDER BY date_given DESC 
              LIMIT ? OFFSET ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $row['date_given'] = date('Y-m-d H:i:s', strtotime($row['date_given']));
        $records[] = $row;
    }
    
    echo json_encode([
        'records' => $records,
        'total_records' => $total_records,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'limit' => $limit
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => $e->getMessage(),
        'records' => [],
        'total_records' => 0,
        'total_pages' => 1,
        'current_page' => 1,
        'limit' => $limit
    ]);
}