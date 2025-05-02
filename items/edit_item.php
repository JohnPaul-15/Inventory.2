<?php
session_start();
require_once "../config/database.php";

// Check if Admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get item ID
if (!isset($_GET['id'])) {
    header('Location: ../dashboard/admin_requests.php');
    exit();
}

$item_id = $_GET['id'];

// Fetch the item
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "Item not found.";
    exit();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $remarks = $_POST['remarks'];

    $updateStmt = $conn->prepare("UPDATE items SET name = ?, category = ?, remarks = ? WHERE id = ?");
    $updateStmt->execute([$name, $category, $remarks, $item_id]);

    // Redirect back to admin page
    header('Location: ../dashboard/admin_requests.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex justify-center items-center">
<div class="bg-white p-8 rounded shadow-md w-96">
    <h2 class="text-2xl font-bold mb-6 text-center">Edit Item</h2>

    <form method="POST">
        <div class="mb-4">
            <label for="name" class="block font-semibold mb-2">Item Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required class="border p-2 w-full rounded">
        </div>

        <div class="mb-4">
            <label for="category" class="block font-semibold mb-2">Category:</label>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($item['category']) ?>" required class="border p-2 w-full rounded">
        </div>

        <div class="mb-4">
            <label for="remarks" class="block font-semibold mb-2">Remarks:</label>
            <textarea id="remarks" name="remarks" class="border p-2 w-full rounded"><?= htmlspecialchars($item['remarks']) ?></textarea>
        </div>

        <div class="flex justify-between">
            <a href="../dashboard/admin_requests.php" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancel</a>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Save Changes</button>
        </div>
    </form>
</div>
</body>
</html>
