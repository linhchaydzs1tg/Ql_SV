<?php
require_once '../config/db.php';

$mssv = $_GET['mssv'] ?? '';
if (!$mssv) die("Không tìm thấy MSSV.");

$sql = "SELECT * FROM sinhvien WHERE mssv = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mssv);
$stmt->execute();
$sv = $stmt->get_result()->fetch_assoc();
if (!$sv) die("Không tìm thấy sinh viên.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten = $_POST['hoten'];
    $email = $_POST['email'];
    $khoa = $_POST['khoa'];
    $ngaysinh = $_POST['ngaysinh'];
    $diem_tb = $_POST['diem_tb'];
    $trang_thai = $_POST['trang_thai'];
    $lop_id = $_POST['lop_id'];
    $gioitinh = $_POST['gioitinh'];
    $diachi = $_POST['diachi'];
    $sodienthoai = $_POST['sodienthoai'];

    $sql = "UPDATE sinhvien SET hoten=?, email=?, khoa=?, ngaysinh=?, diem_tb=?, trang_thai=?, lop_id=?, gioitinh=?, diachi=?, sodienthoai=? WHERE mssv=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssdissss", $hoten, $email, $khoa, $ngaysinh, $diem_tb, $trang_thai, $lop_id, $gioitinh, $diachi, $sodienthoai, $mssv);

    if ($stmt->execute()) {
        header("Location: ../admin/student_manage.php");
        exit;
    } else {
        echo "<div class='text-red-500'>Lỗi: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa sinh viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Sửa thông tin sinh viên</h2>
        <form method="POST" class="space-y-4">
            <input type="text" value="<?= htmlspecialchars($sv['mssv']) ?>" disabled class="w-full p-2 border rounded bg-gray-200">
            <input type="text" name="hoten" value="<?= htmlspecialchars($sv['hoten']) ?>" required class="w-full p-2 border rounded">
            <input type="email" name="email" value="<?= htmlspecialchars($sv['email']) ?>" required class="w-full p-2 border rounded">
            <input type="text" name="khoa" value="<?= htmlspecialchars($sv['khoa']) ?>" required class="w-full p-2 border rounded">
            <input type="date" name="ngaysinh" value="<?= htmlspecialchars($sv['ngaysinh']) ?>" required class="w-full p-2 border rounded">
            <input type="number" step="0.01" name="diem_tb" value="<?= htmlspecialchars($sv['diem_tb']) ?>" required class="w-full p-2 border rounded">
            <select name="trang_thai" class="w-full p-2 border rounded">
                <option value="Đang học" <?= $sv['trang_thai'] == 'Đang học' ? 'selected' : '' ?>>Đang học</option>
                <option value="Tốt nghiệp" <?= $sv['trang_thai'] == 'Tốt nghiệp' ? 'selected' : '' ?>>Tốt nghiệp</option>
            </select>
            <select name="gioitinh" class="w-full p-2 border rounded">
                <option value="Nam" <?= $sv['gioitinh'] == 'Nam' ? 'selected' : '' ?>>Nam</option>
                <option value="Nữ" <?= $sv['gioitinh'] == 'Nữ' ? 'selected' : '' ?>>Nữ</option>
            </select>
            <input type="text" name="diachi" value="<?= htmlspecialchars($sv['diachi']) ?>" class="w-full p-2 border rounded" placeholder="Địa chỉ">
            <input type="text" name="sodienthoai" value="<?= htmlspecialchars($sv['sodienthoai']) ?>" class="w-full p-2 border rounded" placeholder="Số điện thoại">
            <input type="number" name="lop_id" value="<?= htmlspecialchars($sv['lop_id']) ?>" class="w-full p-2 border rounded" placeholder="ID lớp">
            
            <div class="flex justify-between">
                <a href="../admin/student_manage.php" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Quay lại</a>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</body>
</html>