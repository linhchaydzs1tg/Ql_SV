<?php
require_once '../config/db.php'; // Kết nối DB

$id = $_GET['id'];

// Xóa giáo viên
$sql = "DELETE FROM giaovien WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: teacher_manage.php?success=Xóa giáo viên thành công");
    exit();
} else {
    echo "Lỗi: " . $stmt->error;
}
?>