<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Check if item_id and quantity returned are posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $quantity = (int)$_POST['quantity'];

    // Fetch item
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item && $item['borrowed'] >= $quantity) {
        // Update available, borrowed, returned
        $new_available = $item['available'] + $quantity;
        $new_borrowed = $item['borrowed'] - $quantity;
        $new_returned = ($item['returned'] ?? 0) + $quantity;

        $update = $conn->prepare("UPDATE items SET available = ?, borrowed = ?, returned = ?, remarks = ? WHERE id = ?");
        $update->execute([$new_available, $new_borrowed, $new_returned, 'Successfully returned', $item_id]);

        header("Location: ../user/dashboard.php?success=Returned Successfully!");
        exit();
    } else {
        header("Location: ../user/dashboard.php?error=Invalid return quantity!");
        exit();
    }
}
?>