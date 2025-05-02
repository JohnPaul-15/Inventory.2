<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get the request ID
if (!isset($_GET['id'])) {
    header('Location: admin_requests.php');
    exit();
}

$request_id = (int)$_GET['id'];

// Fetch the request details
$stmt = $conn->prepare("
    SELECT br.*, u.first_name, u.last_name, i.name AS item_name
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN items i ON br.item_id = i.id
    WHERE br.id = ?
");
$stmt->execute([$request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    header('Location: admin_requests.php');
    exit();
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks']);
    $returned = (int)$_POST['quantity_returned'];

    // Update the borrow_requests
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = ?, remarks = ?, quantity_returned = ? WHERE id = ?");
    $stmt->execute([$status, $remarks, $returned, $request_id]);

    // Update the available items in the items table
    if ($returned > 0) {
        $updateItem = $conn->prepare("UPDATE items SET available_quantity = available_quantity + ? WHERE id = ?");
        $updateItem->execute([$returned, $request['item_id']]);
    }

    header('Location: admin_requests.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Borrow Request</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="p-8">
    <h2 class="text-2xl font-bold mb-6">Edit Borrow Request</h2>

    <form method="POST" class="bg-white p-6 rounded shadow-md space-y-4">
        <div>
            <label class="block text-sm font-bold mb-1">User:</label>
            <p class="border p-2 rounded"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></p>
        </div>

        <div>
            <label class="block text-sm font-bold mb-1">Item:</label>
            <p class="border p-2 rounded"><?php echo htmlspecialchars($request['item_name']); ?></p>
        </div>

        <div>
            <label for="status" class="block text-sm font-bold mb-1">Status:</label>
            <select name="status" id="status" class="w-full border p-2 rounded">
                <option value="Available" <?php if ($request['status'] === 'Available') echo 'selected'; ?>>Available</option>
                <option value="Not Available" <?php if ($request['status'] === 'Not Available') echo 'selected'; ?>>Not Available</option>
                <option value="For Delivery" <?php if ($request['status'] === 'For Delivery') echo 'selected'; ?>>For Delivery</option>
            </select>
        </div>

        <div>
            <label for="remarks" class="block text-sm font-bold mb-1">Remarks:</label>
            <input type="text" name="remarks" id="remarks" value="<?php echo htmlspecialchars($request['remarks']); ?>" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label for="quantity_returned" class="block text-sm font-bold mb-1">Number of Items Returned:</label>
            <input type="number" name="quantity_returned" id="quantity_returned" value="<?php echo htmlspecialchars($request['quantity_returned']); ?>" min="0" max="<?php echo $request['quantity_borrowed']; ?>" class="w-full border p-2 rounded">
        </div>

        <div class="flex justify-between mt-6">
            <a href="admin_requests.php" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save Changes</button>
        </div>
    </form>
</div>

</body>
</html>
