<?php
require_once '../config/db.php'; // Kết nối DB

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoten = $_POST['hoten'];
    $sodienthoai = $_POST['sodienthoai'];
    $email = $_POST['email'];
    $trang_thai = $_POST['trang_thai'];

    $sql = "INSERT INTO giaovien (hoten, sodienthoai, email, trang_thai) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $hoten, $sodienthoai, $email, $trang_thai);
    
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
    <title>Thêm Giáo Viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-96">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Thêm Giáo Viên</h2>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700">Họ tên</label>
                <input type="text" name="hoten" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Số điện thoại</label>
                <input type="text" name="sodienthoai" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" required class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Trạng thái</label>
                <select name="trang_thai" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                    <option value="Đang làm việc">Đang làm việc</option>
                    <option value="Ngừng làm việc">Ngừng làm việc</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded">Thêm</button>
        </form>
    </div>
</body>
</html>