<?php
require_once '../config/db.php'; // Kết nối DB

// Query: lấy giáo viên
$sql = "SELECT gv.id, gv.hoten, gv.email, gv.ngaysinh, gv.trang_thai
        FROM giaovien gv
        ORDER BY gv.hoten ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Quản lý giáo viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-[#f7f9fc] min-h-screen text-[#1e293b]">
    <header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
        <div class="flex items-center space-x-6">
            <a href="dashboard.php" class="text-blue-600 font-bold text-lg select-none">
                <span class="material-symbols-outlined text-blue-600 font-extrabold text-3xl">school</span>
            </a>
            <ul class="hidden md:flex space-x-6 text-sm text-gray-700 font-normal">
                <li><a class="hover:text-gray-900" href="dashboard.php">Trang chủ</a></li>
                <li><a class="hover:text-gray-900" href="student_manage.php">Quản lý sinh viên</a></li>
                <li><a class="hover:text-gray-900" href="teacher_manage.php">Quản lý giáo viên</a></li>
                <li><a class="hover:text-gray-900" href="sub.php">Môn học</a></li>
                <li><a class="hover:text-gray-900" href="grades.php">Quản lý điểm</a></li>
            </ul>
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
                        <div>
                            <div class="text-black font-semibold text-sm"><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></div>
                            <div class="text-xs leading-4"><?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?></div>
                            <a href="#" class="text-xs text-blue-600 hover:underline">Quản Trị Viên</a>
                        </div>
                    </div>
                    <ul class="py-2">
                        <li>
                            <a href="#" class="flex items-center px-4 py-2 hover:bg-gray-100">
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

    <main class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-2">
            <div>
                <h1 class="text-gray-900 font-semibold text-xl leading-tight">Quản lý giáo viên</h1>
                <p class="text-gray-500 text-xs mt-1">Quản lý thông tin và theo dõi giáo viên</p>
            </div>
            <a href="../teacher/add_teacher.php" class="mt-4 sm:mt-0 inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded">
                <i class="fas fa-plus mr-2"></i> Thêm giáo viên
            </a>
        </div>

        <section class="bg-white border border-gray-100 rounded-lg p-4">
            <table class="w-full text-left text-gray-700 text-xs border-separate border-spacing-y-2">
                <thead>
                    <tr>
                        <th class="pl-4 font-semibold">Họ tên</th>
                        <th class="font-semibold">Email</th>
                        <th class="font-semibold">Ngày sinh</th>
                        <th class="font-semibold">Trạng thái</th>
                        <th class="pr-4 font-semibold">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="bg-white rounded-lg shadow-sm">
                                <td class="pl-4 py-3">
                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($row['hoten']) ?></p>
                                </td>
                                <td class="py-3"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="py-3"><?= htmlspecialchars($row['ngaysinh']) ?></td>
                                <td class="py-3">
                                    <?php $tt = $row['trang_thai'] ?? 'Đang làm việc'; ?>
                                    <span class="inline-block <?= $tt == 'Đang làm việc' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?> text-[9px] font-semibold px-2 py-0.5 rounded-full">
                                        <?= htmlspecialchars($tt) ?>
                                    </span>
                                </td>
                                <td class="pr-4 py-3 flex items-center space-x-3 text-sm">
                                    <!-- Sửa: truyền id -->
                                    <a href="../teacher/edit_teacher.php?id=<?= urlencode($row['id']) ?>" class="text-gray-600 hover:text-gray-800" title="Sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    <!-- Xóa: truyền id -->
                                    <a href="../teacher/delete_teacher.php?id=<?= urlencode($row['id']) ?>" onclick="return confirm('Xóa giáo viên này?')" class="text-red-600 hover:text-red-700" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-500">Chưa có dữ liệu giáo viên.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>