<?php
require_once '../config/db.php'; // Kết nối DB

$id = $_GET['id'];
$sql = "SELECT * FROM giaovien WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoten = $_POST['hoten'];
    $sodienthoai = $_POST['sodienthoai'];
    $email = $_POST['email'];
    $trang_thai = $_POST['trang_thai'];

    $sql = "UPDATE giaovien SET hoten = ?, sodienthoai = ?, email = ?, trang_thai = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $hoten, $sodienthoai, $email, $trang_thai, $id);
    
    if ($stmt->execute()) {
        header("Location: teacher_manage.php");
        exit();
    } else {
        echo "Lỗi: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Sửa Giáo Viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-96">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Sửa Giáo Viên</h2>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700">Họ tên</label>
                <input type="text" name="hoten" value="<?= htmlspecialchars($row['hoten']) ?>" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Số điện thoại</label>
                <input type="text" name="sodienthoai" value="<?= htmlspecialchars($row['sodienthoai']) ?>" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Trạng thái</label>
                <select name="trang_thai" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    <option value="Đang làm việc" <?= $row['trang_thai'] == 'Đang làm việc' ? 'selected' : '' ?>>Đang làm việc</option>
                    <option value="Ngừng làm việc" <?= $row['trang_thai'] == 'Ngừng làm việc' ? 'selected' : '' ?>>Ngừng làm việc</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded">Cập nhật</button>
        </form>
    </div>
</body>
</html>