<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

// Fetch available items and categories
$stmt = $conn->prepare("SELECT id, name, category, available_quantity, description FROM items WHERE available_quantity > 0");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filter
$categories = array_unique(array_column($items, 'category'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard | User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .smooth-transition {
            transition: all 0.3s ease;
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
                    <i class="ri-box-3-line text-blue-600 text-2xl mr-2"></i>
                    <span class="text-xl font-semibold text-gray-900">InventoryPro</span>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <div class="ml-3 relative">
                    <div>
                        <button id="user-menu" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="sr-only">Open user menu</span>
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                <i class="ri-user-line"></i>
                            </div>
                            <span class="ml-2 text-gray-700">User</span>
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
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2 hidden"></div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-checkbox-circle-fill text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= $_SESSION['success'] ?></p>
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
                    <p class="text-sm text-red-700"><?= $_SESSION['error'] ?></p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Inventory Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Browse and request items from the inventory</p>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="ri-search-line text-gray-400"></i>
            </div>
            <input type="text" id="search-input" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search items...">
        </div>
        
        <select id="category-filter" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
            <?php endforeach; ?>
        </select>
        
        <button id="reset-filters" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="ri-refresh-line mr-2"></i> Reset Filters
        </button>
    </div>

    <!-- Inventory Items -->
    <?php if (empty($items)): ?>
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <i class="ri-inbox-line text-4xl text-gray-400 mb-3"></i>
            <h3 class="text-lg font-medium text-gray-900">No items available</h3>
            <p class="mt-1 text-sm text-gray-500">There are currently no items available for borrowing.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $item): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100 smooth-transition card-hover">
                    <div class="p-5">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                                <i class="ri-box-2-line text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="mt-1 flex items-center text-sm text-gray-500">
                                    <i class="ri-price-tag-3-line mr-1"></i>
                                    <?= htmlspecialchars($item['category']) ?>
                                </div>
                                <?php if (!empty($item['description'])): ?>
                                    <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($item['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex items-center justify-between">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $item['available_quantity'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $item['available_quantity'] > 0 ? 'Available' : 'Out of stock' ?>: <?= htmlspecialchars($item['available_quantity']) ?>
                                </span>
                            </div>
                            
                            <button onclick="openBorrowModal(<?= $item['id'] ?>, <?= $item['available_quantity'] ?>, '<?= htmlspecialchars($item['name']) ?>')" 
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 <?= $item['available_quantity'] <= 0 ? 'opacity-50 cursor-not-allowed' : '' ?>" 
                                <?= $item['available_quantity'] <= 0 ? 'disabled' : '' ?>>
                                <i class="ri-shopping-basket-line mr-1"></i> Borrow
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Borrow Modal -->
<div id="borrow-modal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="ri-shopping-basket-line text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Borrow Item</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">You are about to borrow: <span id="modal-item-name" class="font-medium"></span></p>
                            <div class="mt-4">
                                <label for="borrow-quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input type="number" id="borrow-quantity" min="1" value="1" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p id="max-quantity" class="mt-1 text-sm text-gray-500"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirm-borrow" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm Borrow
                </button>
                <button type="button" id="cancel-borrow" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// DOM Elements
const userMenu = document.getElementById('user-menu');
const userDropdown = document.getElementById('user-dropdown');
const searchInput = document.getElementById('search-input');
const categoryFilter = document.getElementById('category-filter');
const resetFilters = document.getElementById('reset-filters');
const borrowModal = document.getElementById('borrow-modal');
const cancelBorrow = document.getElementById('cancel-borrow');
const confirmBorrow = document.getElementById('confirm-borrow');
const notificationContainer = document.getElementById('notification-container');

// Current modal state
let currentModalItemId = null;
let currentModalMaxQuantity = 1;

// Toggle user dropdown
userMenu.addEventListener('click', () => {
    userDropdown.classList.toggle('hidden');
});

// Close dropdown when clicking outside
document.addEventListener('click', (event) => {
    if (!userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
        userDropdown.classList.add('hidden');
    }
});

// Filter items
function filterItems() {
    const searchTerm = searchInput.value.toLowerCase();
    const category = categoryFilter.value.toLowerCase();
    
    document.querySelectorAll('.grid > div').forEach(item => {
        const name = item.querySelector('h3').textContent.toLowerCase();
        const itemCategory = item.querySelector('div.flex.items-center.text-sm.text-gray-500').textContent.toLowerCase();
        
        const matchesSearch = name.includes(searchTerm);
        const matchesCategory = category === '' || itemCategory.includes(category);
        
        if (matchesSearch && matchesCategory) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}

// Event listeners for filtering
searchInput.addEventListener('input', filterItems);
categoryFilter.addEventListener('change', filterItems);
resetFilters.addEventListener('click', () => {
    searchInput.value = '';
    categoryFilter.value = '';
    filterItems();
});

// Modal functions
function openBorrowModal(itemId, maxQuantity, itemName) {
    if (maxQuantity <= 0) return;
    
    currentModalItemId = itemId;
    currentModalMaxQuantity = maxQuantity;
    
    document.getElementById('modal-item-name').textContent = itemName;
    document.getElementById('borrow-quantity').max = maxQuantity;
    document.getElementById('borrow-quantity').value = 1;
    document.getElementById('max-quantity').textContent = `Maximum available: ${maxQuantity}`;
    
    borrowModal.classList.remove('hidden');
}

function closeBorrowModal() {
    borrowModal.classList.add('hidden');
}

cancelBorrow.addEventListener('click', closeBorrowModal);

confirmBorrow.addEventListener('click', () => {
    const quantity = parseInt(document.getElementById('borrow-quantity').value);
    
    if (isNaN(quantity)) {
        showNotification('Please enter a valid quantity', 'error');
        return;
    }
    
    if (quantity < 1) {
        showNotification('Quantity must be at least 1', 'error');
        return;
    }
    
    if (quantity > currentModalMaxQuantity) {
        showNotification(`Cannot borrow more than ${currentModalMaxQuantity} items`, 'error');
        return;
    }
    
    // Submit borrow request
    fetch('../items/borrow_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${currentModalItemId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Item borrowed successfully!', 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showNotification(data.error || 'Error borrowing item', 'error');
        }
    })
    .catch(error => {
        showNotification('Network error. Please try again.', 'error');
    });
    
    closeBorrowModal();
});

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `rounded-md p-4 ${type === 'success' ? 'bg-green-50' : 'bg-red-50'}`;
    
    notification.innerHTML = `
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="${type === 'success' ? 'ri-checkbox-circle-fill text-green-400' : 'ri-error-warning-fill text-red-400'}"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium ${type === 'success' ? 'text-green-800' : 'text-red-800'}">${message}</p>
            </div>
        </div>
    `;
    
    notificationContainer.appendChild(notification);
    notificationContainer.classList.remove('hidden');
    
    setTimeout(() => {
        notification.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        setTimeout(() => {
            notification.remove();
            if (notificationContainer.children.length === 0) {
                notificationContainer.classList.add('hidden');
            }
        }, 300);
    }, 3000);
}

// Close modal when clicking outside
window.addEventListener('click', (event) => {
    if (event.target === borrowModal) {
        closeBorrowModal();
    }
});
</script>

</body>
</html>