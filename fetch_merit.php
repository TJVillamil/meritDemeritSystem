<?php
include 'db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Validate and sanitize input
$items_per_page = isset($_GET['limit']) && in_array($_GET['limit'], [20, 50, 100]) ? (int)$_GET['limit'] : 20;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Fetch total records count with status "approved"
$total_query = "SELECT COUNT(*) AS total_records FROM merit WHERE status_id = (SELECT id FROM statuses WHERE name = 'approved')";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['total_records'] ?? 0;

// Fetch paginated merit records with status "approved"
$merit_query = "
    SELECT id, user_id, moderator_id, amount, reason, date_given, date_approved, status_id
    FROM merit
    WHERE status_id = (SELECT id FROM statuses WHERE name = 'approved')
    ORDER BY date_given DESC
    LIMIT ? OFFSET ?";
$stmt = $conn->prepare($merit_query);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$merit_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'total_records' => $total_records,
    'current_page' => $current_page,
    'items_per_page' => $items_per_page,
    'total_pages' => ceil($total_records / $items_per_page),
    'records' => $merit_records
]);
?>