<?php
session_start();
require_once "../config/database.php";

// Check Admin Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Initialize variables
$items = []; // Initialize empty array to prevent undefined variable errors
$categories = [];
$total = 0;
$pages = 1;
$limit = 10;

// Handle Item Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $item_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($item_id === false || $item_id < 1) {
        $_SESSION['error'] = "Invalid item ID";
    } else {
        try {
            // Check for existing transactions
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE item_id = ?");
            $check_stmt->execute([$item_id]);
            $transaction_count = $check_stmt->fetchColumn();
            
            if ($transaction_count > 0) {
                $_SESSION['error'] = "Cannot delete: Item has $transaction_count related transaction(s)";
            } else {
                // Proceed with deletion
                $delete_stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
                $delete_stmt->execute([$item_id]);
                
                if ($delete_stmt->rowCount() > 0) {
                    $_SESSION['success'] = "Item deleted successfully";
                } else {
                    $_SESSION['error'] = "Item not found or already deleted";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            error_log("Delete Error: " . $e->getMessage());
        }
    }
    
    header("Location: admin_requests.php");
    exit();
}

// Display Messages
if (isset($_SESSION['success'])) {
    echo '<div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">'
        . htmlspecialchars($_SESSION['success'])
        . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">'
        . htmlspecialchars($_SESSION['error'])
        . '</div>';
    unset($_SESSION['error']);
}

// Get Category Filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : "";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

try {
    // Build Main Query
    $query = "SELECT id, name, category, department, quantity, borrowed, (quantity - borrowed) AS available_quantity, remarks FROM items";
    $params = [];
    
    if (!empty($category_filter)) {
        $query .= " WHERE category = ?";
        $params[] = $category_filter;
    }

    $query .= " ORDER BY id DESC LIMIT ?, ?";
    $stmt = $conn->prepare($query);

    // Bind Parameters
    foreach ($params as $index => $param) {
        $stmt->bindValue($index + 1, $param);
    }
    $stmt->bindValue(count($params) + 1, $start, PDO::PARAM_INT);
    $stmt->bindValue(count($params) + 2, $limit, PDO::PARAM_INT);

    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count for Pagination
    $countQuery = "SELECT COUNT(*) FROM items";
    if (!empty($category_filter)) {
        $countQuery .= " WHERE category = ?";
    }
    $countStmt = $conn->prepare($countQuery);
    if (!empty($category_filter)) {
        $countStmt->execute([$category_filter]);
    } else {
        $countStmt->execute();
    }
    $total = $countStmt->fetchColumn();
    $pages = ceil($total / $limit);

    // Fetch distinct categories
    $categoryStmt = $conn->query("SELECT DISTINCT category FROM items");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to load items: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory Management | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-white p-4 shadow flex justify-between items-center">
    <h1 class="text-xl font-bold">Admin Dashboard</h1>
    <a href="../logout.php" class="bg-red-500 text-white px-4 py-2 rounded">Logout</a>
</nav>

<div class="p-8">
    <h2 class="text-2xl font-bold mb-6">Inventory Management</h2>

    <!-- Filter Dropdown -->
    <form method="GET" class="mb-6 flex space-x-4">
        <select name="category" onchange="this.form.submit()" class="border p-2 rounded">
            <option value="">-- Filter by Category --</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>" <?= $category_filter == $category ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($category_filter)): ?>
            <a href="admin_requests.php" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Reset</a>
        <?php endif; ?>
    </form>

    <div class="flex justify-end mb-4">
        <a href="../items/add_item.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Add New Item
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow rounded">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Item Name</th>
                    <th class="px-4 py-2 text-left">Category</th>
                    <th class="px-4 py-2 text-left">Department</th>
                    <th class="px-4 py-2 text-center">On Hand</th>
                    <th class="px-4 py-2 text-center">Borrowed</th>
                    <th class="px-4 py-2 text-center">Returned</th>
                    <th class="px-4 py-2 text-center">Available</th>
                    <th class="px-4 py-2 text-center">Status</th>
                    <th class="px-4 py-2 text-center">Remarks</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($item['category']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($item['department']) ?></td>
                            <!-- "On Hand" column showing the total quantity -->
                            <td class="px-4 py-2 text-center"><?= isset($item['quantity']) ? htmlspecialchars($item['quantity']) : 'N/A' ?></td>
                            <!-- "Borrowed" column showing the quantity borrowed by users -->
                            <td class="px-4 py-2 text-center"><?= isset($item['borrowed']) ? htmlspecialchars($item['borrowed']) : 'N/A' ?></td>
                            <!-- "Returned" column showing the quantity returned -->
                            <td class="px-4 py-2 text-center"><?= isset($item['returned']) ? htmlspecialchars($item['returned']) : 'N/A' ?></td>
                            <!-- "Available" column showing available quantity (calculated dynamically) -->
                            <td class="px-4 py-2 text-center"><?= isset($item['available_quantity']) ? htmlspecialchars($item['available_quantity']) : 'N/A' ?></td>
                            <td class="px-4 py-2 text-center">
                                <?php
                                    if (isset($item['available_quantity']) && $item['available_quantity'] > 0) {
                                        echo "<span class='text-green-600 font-semibold'>Available</span>";
                                    } else {
                                        echo "<span class='text-red-600 font-semibold'>Not Available</span>";
                                    }
                                ?>
                            </td>
                            <td class="px-4 py-2 text-center"><?= htmlspecialchars($item['remarks'] ?? 'N/A') ?></td>
                            <td class="px-4 py-2 text-center">
                                <a href="../items/edit_item.php?id=<?= $item['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
                                <a href="admin_requests.php?action=delete&id=<?= $item['id'] ?>" 
                               class="text-red-600 hover:text-red-900"
                               onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($item['name']) ?>?');">
                               Delete
                            </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-gray-500">No items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?= $i ?>&category=<?= urlencode($category_filter) ?>"
               class="px-4 py-2 mx-1 rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 border'; ?>">
               <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

</div>

</body>
</html>
