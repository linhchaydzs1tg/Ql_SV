<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khu vực giảng viên</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<nav>
    <a href="dashboard.php">Trang chủ</a> |
    <a href="profile.php">Thông tin cá nhân</a> |
    <a href="logout.php">Đăng xuất</a>
</nav>
<hr>

