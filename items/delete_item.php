<?php
session_start();
require_once "../config/database.php";

// Verify admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Validate item ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid item ID";
    header('Location: ../dashboard/admin_requests.php');
    exit();
}

$item_id = (int)$_GET['id'];

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Check if item exists
    $check_stmt = $conn->prepare("SELECT id FROM items WHERE id = ?");
    $check_stmt->execute([$item_id]);
    
    if ($check_stmt->rowCount() === 0) {
        throw new Exception("Item not found");
    }
    
    // Delete the item (will cascade to transactions)
    $delete_stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $delete_stmt->execute([$item_id]);
    
    if ($delete_stmt->rowCount() > 0) {
        $_SESSION['success'] = "Item and related transactions deleted successfully";
    } else {
        throw new Exception("No rows affected - deletion failed");
    }
    
    $conn->commit();
    
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log("Delete Error: " . $e->getMessage());
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../dashboard/admin_requests.php");
exit();
?>