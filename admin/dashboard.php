<?php
session_start();

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin thì chuyển về login
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php'; // File kết nối CSDL

// Truy vấn tổng số sinh viên
$result_sv = $conn->query("SELECT COUNT(*) AS total FROM sinhvien");
$row_sv = $result_sv->fetch_assoc();
$total_sv = $row_sv['total'];

// Truy vấn tổng số lớp
$result_lop = $conn->query("SELECT COUNT(*) AS total FROM lop");
$row_lop = $result_lop->fetch_assoc();
$total_lop = $row_lop['total'];

// Truy vấn tổng số môn học
$result_mon = $conn->query("SELECT COUNT(*) AS total FROM monhoc");
$row_mon = $result_mon->fetch_assoc();
$total_mon = $row_mon['total'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Trang chủ quản trị</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=school" />
</head>
</head>
<body class="bg-[#f7f9fc] min-h-screen text-[#1e293b]">
   <header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
    <div class="flex items-center space-x-6">
        <a href="dashboard.php" class="text-blue-600 font-bold text-lg select-none">
            <span class="material-symbols-outlined text-blue-600 font-extrabold text-3xl">school</span>
        </a>
        <nav class="hidden sm:flex space-x-6 text-sm text-[#475569] font-normal">
            <li><a class="hover:text-gray-900" href="dashboard.php">Trang chủ</a></li>
            <a href="student_manage.php" class="hover:text-black">Quản lý sinh viên</a>
            <a href="teacher_manage.php" class="hover:text-black">Quản lý giáo viên</a>
            <a href="sub.php" class="hover:text-black">Môn học</a>
            <a href="grades.php" class="hover:text-black">Quản lý điểm</a>
        </nav>
        </div>
        <div class="flex items-center space-x-6 text-gray-500 text-lg relative">
            <button aria-label="Thông báo" class="hover:text-black focus:outline-none">
                <i class="fas fa-bell"></i>
            </button>
            <div class="relative" x-data="{ open: false }">
                <button id="userMenuButton" aria-haspopup="true" aria-expanded="false" class="hover:text-black focus:outline-none" onclick="toggleUserMenu()">
                    <i class="fas fa-user-circle"></i>
                </button>
                <div id="userMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 text-sm text-gray-700 z-10">
                    <div class="flex items-center space-x-3 px-4 py-3 border-b border-gray-200">
                       <div class="flex items-center space-x-3 px-4 py-3 border-b border-gray-200">
    <div>
        <div class="text-black font-semibold text-sm"><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></div>
        <div class="text-xs leading-4"><?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?></div>
        <a href="#" class="text-xs text-blue-600 hover:underline">Quản Trị Viên</a>
    </div>
</div>
                    </div>
                    <ul class="py-2">
                        <li>
                            <a href="ttcanhan.php" class="flex items-center px-4 py-2 hover:bg-gray-100">
                                <i class="fas fa-key mr-2 text-gray-500"></i> Thông tin cá nhân
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center px-4 py-2 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2 text-gray-500"></i> Cài đặt hệ thống
                            </a>
                        </li>
                        <li>
                            <a href="../auth/logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="px-6 py-6 max-w-7xl mx-auto">
        <h1 class="text-xl font-extrabold text-[#0f172a] mb-1">Trang chủ quản trị</h1>
        <p class="text-[#475569] text-sm mb-6">Tổng quan hệ thống quản lý sinh viên</p>

        <section class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg p-4 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-xs text-gray-600 font-semibold mb-1">Tổng sinh viên</div>
                    <div class="text-lg font-extrabold text-black"><?php echo $total_sv; ?></div>
                </div>
                <button class="bg-blue-500 hover:bg-blue-600 text-white rounded-md p-3">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="bg-white rounded-lg p-4 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-xs text-gray-600 font-semibold mb-1">Lớp học</div>
                    <div class="text-lg font-extrabold text-black"><?php echo $total_lop; ?></div>
                </div>
                <button class="bg-green-500 hover:bg-green-600 text-white rounded-md p-3">
                    <i class="fas fa-book-open"></i>
                </button>
            </div>
            <div class="bg-white rounded-lg p-4 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-xs text-gray-600 font-semibold mb-1">Môn học</div>
                    <div class="text-lg font-extrabold text-black"><?php echo $total_mon; ?></div>
                </div>
                <button class="bg-purple-600 hover:bg-purple-700 text-white rounded-md p-3">
                    <i class="fas fa-book"></i>
                </button>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h2 class="font-semibold text-sm text-[#0f172a] mb-4">Hoạt động gần đây</h2>
                <ul class="space-y-4 text-xs text-[#475569]">
                    <li class="flex items-start space-x-3">
                        <div class="text-blue-500 mt-1">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <div class="text-black font-semibold leading-tight">Cập nhật thông tin sinh viên</div>
                            <div>5 phút trước</div>
                        </div>
                    </li>
                    <li class="flex items-start space-x-3">
                        <div class="text-green-500 mt-1">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div>
                            <div class="text-black font-semibold leading-tight">Đăng ký lớp học mới</div>
                            <div>15 phút trước</div>
                        </div>
                    </li>
                    <li class="flex items-start space-x-3">
                        <div class="text-purple-600 mt-1">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <div class="text-black font-semibold leading-tight">Cập nhật điểm cho sinh viên</div>
                            <div>1 giờ trước</div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h2 class="font-semibold text-sm text-[#0f172a] mb-4">Thông báo hệ thống</h2>
                <div class="mb-4 rounded border-l-4 border-yellow-400 bg-yellow-50 p-3 text-xs text-yellow-800">
                    <div class="flex items-center space-x-2 mb-1">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Cần cập nhật thông tin sinh viên năm cuối</span>
                    </div>
                    <div class="pl-6">Hạn chót: <span class="font-semibold">15/01/2024</span></div>
                </div>
                <div class="rounded border-l-4 border-blue-400 bg-blue-50 p-3 text-xs text-blue-700">
                    <div class="flex items-center space-x-2 mb-1">
                        <i class="fas fa-info-circle"></i>
                        <span>Bảo trì hệ thống vào 2:00 AM ngày mai</span>
                    </div>
                    <div class="pl-6 text-blue-600 font-semibold">Thời gian dự kiến: 2 giờ</div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            const expanded = menu.classList.contains('hidden');
            if (expanded) {
                menu.classList.remove('hidden');
                document.getElementById('userMenuButton').setAttribute('aria-expanded', 'true');
            } else {
                menu.classList.add('hidden');
                document.getElementById('userMenuButton').setAttribute('aria-expanded', 'false');
            }
        }
        // Đóng menu nếu click ra ngoài
        window.addEventListener('click', function(e) {
            const menu = document.getElementById('userMenu');
            const button = document.getElementById('userMenuButton');
            if (!menu.contains(e.target) && !button.contains(e.target)) {
                menu.classList.add('hidden');
                button.setAttribute('aria-expanded', 'false');
            }
        });
    </script>
</body>
</html>