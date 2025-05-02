<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

// Fetch available items
$stmt = $conn->prepare("SELECT id, name, category, available_quantity FROM items WHERE available_quantity > 0");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Navigation remains the same -->
<nav class="bg-white p-4 shadow flex justify-between items-center">
    <h1 class="text-xl font-bold">User Dashboard</h1>
    <div class="relative">
        <button id="dropdownButton" class="bg-blue-500 text-white px-4 py-2 rounded">
            Menu
        </button>
        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white shadow rounded">
            <a href="../profile/view_profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
            <a href="../logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
        </div>
    </div>
</nav>

<div class="p-8">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <h2 class="text-2xl font-bold mb-4">Available Items</h2>

    <?php if (empty($items)): ?>
        <p class="text-gray-500">No items currently available for borrowing</p>
    <?php else: ?>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Item Name</th>
                    <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Available Quantity</th>
                    <th class="py-2 text-xs font-large text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="text-center border-t">
                        <td class="py-2"><?= htmlspecialchars($item['name']) ?></td>
                        <td class="py-2"><?= htmlspecialchars($item['category']) ?></td>
                        <td class="py-2"><?= htmlspecialchars($item['available_quantity']) ?></td>
                        <td class="py-2">
                            <form action="../items/borrow_item.php" method="POST">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                
                                <label for="quantity">Quantity:</label>
                                <input type="number" name="quantity" min="1" max="<?= $item['available_quantity'] ?>" required>
                                
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Borrow</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div id="borrow-tab" class="user-tab-content pt-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Available Items</h2>
    
    <!-- Search and Filter -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <div class="relative flex-grow">
            <input type="text" id="search-items" placeholder="Search items..." 
                   class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <i class="ri-search-line absolute left-3 top-3 text-gray-400"></i>
        </div>
        <select id="category-filter" class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
            <option value="">All Categories</option>
            <!-- Categories will be populated via JavaScript -->
        </select>
    </div>

    <!-- Items Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody id="available-items" class="bg-white divide-y divide-gray-200">
                    <!-- Items will be loaded here via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('dropdownButton').addEventListener('click', function() {
    document.getElementById('dropdownMenu').classList.toggle('hidden');
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Load available items when page loads
    loadAvailableItems();
    
    // Tab switching functionality
    document.querySelectorAll('.user-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all tabs
            document.querySelectorAll('.user-tab-btn').forEach(t => 
                t.classList.remove('border-primary', 'text-primary'));
            
            // Add active class to clicked tab
            this.classList.add('border-primary', 'text-primary');
            
            // Hide all tab contents
            document.querySelectorAll('.user-tab-content').forEach(c => 
                c.classList.add('hidden'));
            
            // Show selected tab content
            const tabId = this.getAttribute('data-tab') + '-tab';
            document.getElementById(tabId)?.classList.remove('hidden');
        });
    });

    // Search functionality
    document.getElementById('search-items').addEventListener('input', function() {
        loadAvailableItems(this.value, document.getElementById('category-filter').value);
    });

    // Category filter
    document.getElementById('category-filter').addEventListener('change', function() {
        loadAvailableItems(document.getElementById('search-items').value, this.value);
    });
});

function loadAvailableItems(searchTerm = '', category = '') {
    fetch(`../api/get_items.php?search=${encodeURIComponent(searchTerm)}&category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(items => {
            const tbody = document.getElementById('available-items');
            tbody.innerHTML = '';
            
            items.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="ri-box-2-line text-gray-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${item.name}</div>
                                <div class="text-sm text-gray-500">${item.department}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${item.category}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            ${item.available_quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${item.available_quantity} available
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="borrowItem(${item.id})" 
                            class="text-primary hover:text-primary-dark ${item.available_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : ''}"
                            ${item.available_quantity <= 0 ? 'disabled' : ''}>
                            Borrow
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        });
}

function borrowItem(itemId) {
    const quantity = prompt('How many items would you like to borrow?', '1');
    if (!quantity || isNaN(quantity) || quantity < 1) return;
    
    fetch('../actions/borrow_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            item_id: itemId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item borrowed successfully!', 'success');
            loadAvailableItems();
        } else {
            showNotification(data.message || 'Error borrowing item', 'error');
        }
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white 
        ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>

</body>
</html>
