<?php
session_start();
require_once "../config/database.php";

// Check Admin Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Initialize variables
$items = [];
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

// Get Category Filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : "";
$search_query = isset($_GET['search']) ? $_GET['search'] : "";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

try {
    // Build Main Query
    $query = "SELECT id, name, category, department, quantity, borrowed, (quantity - borrowed) AS available_quantity, remarks FROM items";
    $where = [];
    $params = [];
    
    if (!empty($category_filter)) {
        $where[] = "category = ?";
        $params[] = $category_filter;
    }
    
    if (!empty($search_query)) {
        $where[] = "(name LIKE ? OR category LIKE ? OR department LIKE ?)";
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
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
    if (!empty($where)) {
        $countQuery .= " WHERE " . implode(" AND ", $where);
    }
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    $pages = ceil($total / $limit);

    // Fetch distinct categories
    $categoryStmt = $conn->query("SELECT DISTINCT category FROM items ORDER BY category");
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Management | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body {
        font-family: 'Inter', sans-serif;
    }
    .status-available {
        background-color: #f0fdf4;
        color: #166534;
    }
    .status-unavailable {
        background-color: #fef2f2;
        color: #991b1b;
    }
    .pagination-link {
        transition: all 0.2s ease;
    }
    .pagination-link:hover:not(.active) {
        background-color: #f3f4f6;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Navigation -->
<nav class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <i class="ri-dashboard-line text-blue-600 text-2xl mr-2"></i>
                    <span class="text-xl font-semibold text-gray-900">InventoryPro Admin</span>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <div class="ml-3 relative">
                    <div>
                        <button id="user-menu" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="sr-only">Open user menu</span>
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                <i class="ri-admin-line"></i>
                            </div>
                            <span class="ml-2 text-gray-700">Admin</span>
                            <i class="ri-arrow-down-s-line ml-1 text-gray-500"></i>
                        </button>
                    </div>
                    <div id="user-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <a href="../profile/view_profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="ri-user-line mr-2"></i>Profile</a>
                        <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="ri-logout-box-r-line mr-2"></i>Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Notifications -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-checkbox-circle-fill text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($_SESSION['success']) ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-error-warning-fill text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="mb-4 md:mb-0">
            <h1 class="text-2xl font-bold text-gray-900">Inventory Management</h1>
            <p class="mt-1 text-sm text-gray-500">Manage all inventory items and track their status</p>
        </div>
        <a href="../items/add_item.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="ri-add-line mr-2"></i> Add New Item
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" name="search" id="search" value="<?= htmlspecialchars($search_query) ?>" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                           placeholder="Search items...">
                </div>
            </div>
            
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" id="category" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" <?= $category_filter == $category ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="ri-filter-line mr-2"></i> Apply Filters
                </button>
                <?php if (!empty($category_filter) || !empty($search_query)): ?>
                    <a href="admin_requests.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="ri-refresh-line mr-2"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-md flex items-center justify-center">
                                            <i class="ri-box-2-line text-gray-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="text-sm text-gray-500">ID: <?= $item['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($item['category']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($item['department']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900 font-medium"><?= $item['quantity'] ?></div>
                                    <div class="text-xs text-gray-500">Borrowed: <?= $item['borrowed'] ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $item['available_quantity'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $item['available_quantity'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        <?= $item['available_quantity'] > 0 ? 'status-available' : 'status-unavailable' ?>">
                                        <?= $item['available_quantity'] > 0 ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    <?= !empty($item['remarks']) ? htmlspecialchars($item['remarks']) : '—' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center space-x-2">
                                        <a href="../items/edit_item.php?id=<?= $item['id'] ?>" class="text-blue-600 hover:text-blue-900" title="Edit">
                                            <i class="ri-edit-2-line"></i>
                                        </a>
                                        <a href="admin_requests.php?action=delete&id=<?= $item['id'] ?>" 
                                           class="text-red-600 hover:text-red-900"
                                           onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars(addslashes($item['name'])) ?>?');"
                                           title="Delete">
                                           <i class="ri-delete-bin-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <i class="ri-inbox-line text-4xl text-gray-400 mb-2"></i>
                                    <p>No items found</p>
                                    <?php if (!empty($category_filter) || !empty($search_query)): ?>
                                        <a href="admin_requests.php" class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                                            Clear filters and try again
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pages > 1): ?>
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium"><?= $start + 1 ?></span> to 
                            <span class="font-medium"><?= min($start + $limit, $total) ?></span> of 
                            <span class="font-medium"><?= $total ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search_query) ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Previous</span>
                                    <i class="ri-arrow-left-s-line"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            // Show first page and ellipsis if needed
                            if ($page > 3): ?>
                                <a href="?page=1&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search_query) ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    1
                                </a>
                                <?php if ($page > 4): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                        ...
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php 
                            // Show page numbers around current page
                            for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
                                <a href="?page=<?= $i ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search_query) ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium <?= $i === $page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php 
                            // Show last page and ellipsis if needed
                            if ($page < $pages - 2): ?>
                                <?php if ($page < $pages - 3): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                        ...
                                    </span>
                                <?php endif; ?>
                                <a href="?page=<?= $pages ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search_query) ?>" 
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <?= $pages ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($page < $pages): ?>
                                <a href="?page=<?= $page + 1 ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search_query) ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Next</span>
                                    <i class="ri-arrow-right-s-line"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Toggle user dropdown
document.getElementById('user-menu').addEventListener('click', function() {
    document.getElementById('user-dropdown').classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!document.getElementById('user-menu').contains(event.target) && 
        !document.getElementById('user-dropdown').contains(event.target)) {
        document.getElementById('user-dropdown').classList.add('hidden');
    }
});
</script>

</body>
</html>