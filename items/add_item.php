<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $available_quantity = $quantity;

    $stmt = $conn->prepare("INSERT INTO items (name, category, quantity, available_quantity) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $category, $quantity, $available_quantity]);

    header('Location: ../dashboard/admin_requests.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Item</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="p-8">
    <h2 class="text-2xl font-bold mb-4">Add New Item</h2>

    <form method="POST" class="bg-white p-6 rounded shadow-md">
        <div class="mb-4">
            <label class="block">Item Name</label>
            <input type="text" name="name" required class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block">Category</label>
            <input type="text" name="category" required class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block">Quantity</label>
            <input type="number" name="quantity" min="1" required class="w-full border p-2 rounded">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Item</button>
    </form>
</div>

</body>
</html>
