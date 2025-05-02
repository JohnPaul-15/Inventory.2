<?php
session_start();
require_once "../config/database.php";

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: ../dashboard/user.php");
    exit();
}

// Validate input
if (!isset($_POST['item_id'], $_POST['quantity'])) {
    $_SESSION['error'] = "Missing required fields";
    header("Location: ../dashboard/user.php");
    exit();
}

$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if ($item_id === false || $item_id < 1 || $quantity === false || $quantity < 1) {
    $_SESSION['error'] = "Invalid item ID or quantity";
    header("Location: ../dashboard/user.php");
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check item availability with row lock
    $stmt = $conn->prepare("SELECT available_quantity, borrowed_quantity FROM items WHERE id = ? FOR UPDATE");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Item not found");
    }

    if ($item['available_quantity'] < $quantity) {
        throw new Exception("Not enough items available");
    }

    // Calculate new quantities
    $new_available = $item['available_quantity'] - $quantity;
    $new_borrowed = $item['borrowed_quantity'] + $quantity;

    // Update item quantities
    $update = $conn->prepare("UPDATE items SET available_quantity = ?, borrowed_quantity = ? WHERE id = ?");
    $update->execute([$new_available, $new_borrowed, $item_id]);

    // Record transaction
    $insert = $conn->prepare("INSERT INTO transactions 
        (user_id, item_id, borrowed_quantity, transaction_date, status) 
        VALUES (?, ?, ?, NOW(), 'Borrowed')");
    $insert->execute([
        $_SESSION['user_id'],
        $item_id,
        $quantity
    ]);

    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = "Successfully borrowed {$quantity} item(s)";
    
} catch (PDOException $e) {
    // Rollback transaction on database error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log("Borrow Error: " . $e->getMessage());
    
} catch (Exception $e) {
    // Rollback transaction on other errors
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to user dashboard
header("Location: ../dashboard/user.php");
exit();
?>