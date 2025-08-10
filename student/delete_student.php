<?php
require_once '../config/db.php';

$mssv = $_GET['mssv'] ?? '';
if (!$mssv) {
    die("Không tìm thấy MSSV.");
}

$sql = "DELETE FROM sinhvien WHERE mssv = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mssv);

if ($stmt->execute()) {
    header("Location: ../admin/student_manage.php");
    exit;
} else {
    echo "Lỗi: " . $conn->error;
}
