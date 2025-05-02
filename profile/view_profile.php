<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="p-8">
    <h2 class="text-2xl font-bold mb-4">Your Profile</h2>

    <form action="update_profile.php" method="POST" class="bg-white p-6 rounded shadow-md">
        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

        <div class="mb-4">
            <label class="block">First Name</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="w-full border p-2 rounded">
        </div>
        
        <div class="mb-4">
            <label class="block">Last Name</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block">Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full border p-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block">Department</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($user['department']); ?>" class="w-full border p-2 rounded">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Profile</button>
    </form>
</div>

</body>
</html>
