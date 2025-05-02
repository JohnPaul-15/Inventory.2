<?php
session_start();

// If already logged in, redirect to the correct dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header('Location: dashboard/admin_requests.php');
        exit();
    } else {
        header('Location: dashboard/user_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded shadow-md w-full max-w-sm text-center">
    <h2 class="text-2xl font-bold mb-6">Welcome to Inventory System</h2>

    <div class="flex flex-col gap-4">
        <a href="auth/login.php" class="bg-blue-500 text-white p-3 rounded hover:bg-blue-600">Login</a>
        <a href="auth/register.php" class="bg-green-500 text-white p-3 rounded hover:bg-green-600">Register</a>
    </div>
</div>

</body>
</html>
