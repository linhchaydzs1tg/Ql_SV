<?php
// giangvien/_auth.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['vai_tro'] ?? '') !== 'giaovien') {
    header("Location: ../login.php"); exit();
}
$giaovien_id = (int)($_SESSION['id_thamchieu'] ?? 0);

$mysqli = new mysqli("localhost", "root", "", "student");
if ($mysqli->connect_errno) die("Lỗi kết nối MySQL: " . $mysqli->connect_error);
$mysqli->set_charset("utf8mb4");

// Thông tin giáo viên
$gv = ['hoten' => 'Giảng viên', 'email' => 'Chưa rõ'];
if ($giaovien_id > 0 && ($st = $mysqli->prepare("SELECT hoten,email FROM giaovien WHERE id=? LIMIT 1"))) {
    $st->bind_param("i", $giaovien_id);
    $st->execute();
    if ($row = $st->get_result()->fetch_assoc()) $gv = $row;
    $st->close();
}
