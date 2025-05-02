<?php
session_start();
require_once "../config/database.php";

$error = "";
$success = "";

// Handle register POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = "Email is already registered.";
            } else {
                // Insert new user
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, user_type) VALUES (?, ?, ?, ?, 'user')");
                if ($stmt->execute([$first_name, $last_name, $email, $hashedPassword])) {
                    $success = "Registration successful. You can now login.";
                } else {
                    $error = "Failed to register. Please try again.";
                }
            }
        } else {
            $error = "Passwords do not match.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Inventory Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div id="register-page" class="auth-card max-w-md w-full bg-white rounded-lg p-8 shadow-lg">
    <div class="text-center mb-8">
        <h1 class="font-['Pacifico'] text-3xl text-gray-800">Inventory System</h1>
        <h2 class="mt-4 text-2xl font-bold text-gray-900">Create your account</h2>

        <?php if ($error): ?>
            <div class="mt-2 text-sm text-red-600"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mt-2 text-sm text-green-600"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
    </div>

    <form action="" method="POST" class="space-y-6">
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
            <input id="first_name" name="first_name" type="text" required
                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary text-sm">
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input id="last_name" name="last_name" type="text" required
                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary text-sm">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input id="email" name="email" type="email" required
                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary text-sm">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input id="password" name="password" type="password" required
                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary text-sm">
        </div>

        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input id="confirm_password" name="confirm_password" type="password" required
                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary text-sm">
        </div>

        <div>
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Register
            </button>
        </div>
    </form>

    <div class="mt-6 text-center text-sm text-gray-600">
        Already have an account? 
        <a href="login.php" class="font-medium text-primary hover:text-primary/80">Sign in</a>
    </div>
</div>

</body>
</html>
