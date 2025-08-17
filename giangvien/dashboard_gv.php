<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 'giangvien') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Giảng viên</title>
</head>
<body>
    <h1>Chào Giảng viên: <?php echo $_SESSION['ho_ten']; ?></h1>
    <p>Trang dành cho giảng viên.</p>
    <a href="auth/logout.php" class="btn btn-danger">Đăng Xuất</a>
</body>
</html>
