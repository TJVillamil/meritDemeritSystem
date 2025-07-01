<?php
include 'db_connect.php';

// Get pagination parameters
$items_per_page = isset($_GET['limit']) && in_array($_GET['limit'], [20, 50, 100]) ? (int)$_GET['limit'] : 20;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Fetch total users count
$total_users_query = "SELECT COUNT(*) AS total_users FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total_users'] ?? 0;

// Fetch leaderboard data
$leaderboard_query = "
    SELECT users.id, CONCAT(users.first_name, ' ', users.last_name) AS name, users.points, courses.name AS course
    FROM users
    LEFT JOIN courses ON users.course_id = courses.id
    ORDER BY users.points DESC
    LIMIT ? OFFSET ?";
$stmt = $conn->prepare($leaderboard_query);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$leaderboard = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'total_users' => $total_users,
    'current_page' => $current_page,
    'items_per_page' => $items_per_page,
    'total_pages' => ceil($total_users / $items_per_page),
    'leaderboard' => $leaderboard
]);
?>