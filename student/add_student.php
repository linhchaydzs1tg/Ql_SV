<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mssv = $_POST['mssv'];
    $hoten = $_POST['hoten'];
    $email = $_POST['email'];
    $khoa = $_POST['khoa'];
    $ngaysinh = $_POST['ngaysinh'];
    $diem_tb = $_POST['diem_tb'];
    $trang_thai = $_POST['trang_thai'];
    $lop_id = $_POST['lop_id'];

    $sql = "INSERT INTO sinhvien (mssv, hoten, email, khoa, ngaysinh, diem_tb, trang_thai, lop_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssdsi", $mssv, $hoten, $email, $khoa, $ngaysinh, $diem_tb, $trang_thai, $lop_id);

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
    <title>Thêm sinh viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
        <h2 class="text-2xl font-bold mb-6 text-gray-700">Thêm sinh viên</h2>
        <form method="POST" class="space-y-4">
            <input type="text" name="mssv" placeholder="MSSV" required class="w-full p-2 border rounded">
            <input type="text" name="hoten" placeholder="Họ tên" required class="w-full p-2 border rounded">
            <input type="email" name="email" placeholder="Email" required class="w-full p-2 border rounded">
            <input type="text" name="khoa" placeholder="Khoa" required class="w-full p-2 border rounded">
            <input type="date" name="ngaysinh" required class="w-full p-2 border rounded">
            <input type="number" step="0.01" name="diem_tb" placeholder="Điểm trung bình" required class="w-full p-2 border rounded">
            <select name="trang_thai" class="w-full p-2 border rounded">
                <option value="Đang học">Đang học</option>
                <option value="Tốt nghiệp">Tốt nghiệp</option>
            </select>
            <input type="number" name="lop_id" placeholder="Lớp ID" class="w-full p-2 border rounded">
            
            <div class="flex justify-between">
                <a href="../admin/student_manage.php" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Quay lại</a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Thêm mới</button>
            </div>
        </form>
    </div>
</body>
</html>
