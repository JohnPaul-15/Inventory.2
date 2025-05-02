<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch all items
$stmt = $conn->prepare("SELECT * FROM items");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-white p-4 shadow flex justify-between items-center">
    <h1 class="text-xl font-bold">Admin Dashboard</h1>
    <a href="../logout.php" class="bg-red-500 text-white px-4 py-2 rounded">Logout</a>
</nav>

<div class="p-8">
    <h2 class="text-2xl font-bold mb-4">Manage Items</h2>

    <a href="../items/add_item.php" class="mb-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">Add New Item</a>

    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2">Item Name</th>
                <th class="py-2">Category</th>
                <th class="py-2">Available Quantity</th>
                <th class="py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr class="text-center border-t">
                    <td class="py-2"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($item['category']); ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($item['available_quantity']); ?></td>
                    <td class="py-2">
                        <a href="../items/edit_item.php?id=<?php echo $item['id']; ?>" class="text-yellow-500 hover:underline">Edit</a> |
                        <a href="../items/delete_item.php?id=<?php echo $item['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

</body>
</html>
