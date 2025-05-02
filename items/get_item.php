<?php
session_start();
require_once "../../config/database.php";

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

try {
    $query = "SELECT id, name, category, department, available_quantity FROM items WHERE available_quantity > 0";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR department LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($category)) {
        $query .= " AND category = ?";
        $params[] = $category;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also get categories for filter dropdown
    $categories = $conn->query("SELECT DISTINCT category FROM items")->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'items' => $items,
        'categories' => $categories
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>