<?php include 'header.php'; ?>
<?php
require_once '../config/db.php';

$email = $_SESSION['email'];

$sql = "SELECT * FROM giaovien WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$gv = $result->fetch_assoc();
?>

<h2>Thông tin cá nhân</h2>
<table border="1" cellpadding="8">
    <tr><th>Họ tên</th><td><?php echo $gv['hoten']; ?></td></tr>
    <tr><th>Email</th><td><?php echo $gv['email']; ?></td></tr>
    <tr><th>Ngày sinh</th><td><?php echo $gv['ngaysinh']; ?></td></tr>
    <tr><th>Trạng thái</th><td><?php echo $gv['trang_thai'] ? 'Đang công tác' : 'Nghỉ'; ?></td></tr>
</table>

<?php include 'footer.php'; ?>
