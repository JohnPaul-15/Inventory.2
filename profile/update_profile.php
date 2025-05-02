<?php
session_start();
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, department = ? WHERE id = ?");
    $stmt->execute([$first_name, $last_name, $email, $department, $id]);

    $_SESSION['success'] = "Profile updated successfully!";
    header('Location: view_profile.php');
    exit();
}
?>
