<?php
session_start(); // Khởi tạo phiên

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'student'); // Thay đổi thông tin đăng nhập nếu cần

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy email từ phiên
$email = isset($_SESSION['email']) ? $_SESSION['email'] : ''; // Lấy email từ session

if ($email) {
    // Truy vấn dữ liệu người dùng
    $sql = "SELECT * FROM nguoidung WHERE email = '$email'";
    $result = $conn->query($sql);

    $row = null; // Khởi tạo biến $row

    if ($result && $result->num_rows > 0) {
        // Lấy dữ liệu
        $row = $result->fetch_assoc();
    } else {
        echo "<p>Người dùng không tồn tại</p>";
    }
} else {
    echo "<p>Email không được cung cấp</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Profile Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-[#F5F7FA] p-6">
    <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 sm:gap-0 rounded-t-lg px-8 py-6" style="background: linear-gradient(90deg, #3B82F6 0%, #8B5CF6 100%)">
            <div class="flex items-center gap-4">
                <img alt="Profile image" class="w-16 h-16 rounded-full border-4 border-white object-cover" src="https://storage.googleapis.com/a1aa/image/4847abc2-c3d7-4a02-f729-3770779c40a5.jpg"/>
                <div class="text-white">
                    <h1 class="font-semibold text-lg leading-tight"><?php echo $row ? $row['vaitro'] : 'Người dùng không xác định'; ?></h1>
                    <p class="text-sm leading-tight">Hệ thống quản trị viên</p>
                    <p class="text-xs leading-tight mt-0.5 opacity-80">Phòng Đào tạo</p>
                </div>
            </div>
        </div>
        <div class="border-b border-gray-200 px-8">
            <nav class="flex gap-6 text-sm font-medium text-gray-600 pt-4">
                <button aria-current="page" class="flex items-center gap-1 border border-blue-600 text-blue-600 rounded px-3 py-1.5">
                    <i class="fas fa-user text-xs"></i>
                    Thông tin cá nhân
                </button>
                <button class="flex items-center gap-1 hover:text-gray-900" onclick="window.location.href='quyenhan.php'">
                    <i class="fas fa-shield-alt text-xs"></i>
                    Quyền hạn
                </button>
            </nav>
        </div>
        <div class="px-8 py-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-semibold text-gray-900 text-sm">Thông tin cá nhân</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-12 text-xs text-gray-700">
                <div>
                    <p class="font-semibold mb-1 text-[10px] text-gray-500">Phòng ban</p>
                    <p>Phòng Đào tạo</p>
                </div>
                <div>
                    <p class="font-semibold mb-1 text-[10px] text-gray-500">Email</p>
                    <p><?php echo $row ? $row['email'] : 'N/A'; ?></p>
                </div>
                <div>
                    <p class="font-semibold mb-1 text-[10px] text-gray-500">Chức vụ</p>
                    <p><?php echo $row ? $row['vaitro'] : 'N/A'; ?></p>
                </div>
                <div>
                    <p class="font-semibold mb-1 text-[10px] text-gray-500">Trạng thái tài khoản</p>
                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-600 rounded-full px-2 py-0.5 text-[10px] font-semibold">
                        <span class="w-2 h-2 rounded-full bg-green-400 block"></span>
                        Hoạt động
                    </span>
                </div>
                <div>
                    <p class="font-semibold mb-1 text-[10px] text-gray-500">Ngày vào làm</p>
                    <p>15/1/2020</p>
                </div>
                <div></div>
            </div>
        </div>
    </div>
</body>
</html>