<?php
// Kết nối CSDL
require_once __DIR__ . '/../config/db.php';
session_start();

$msg = "";

// ====== Lấy dữ liệu môn học cần sửa ======
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM monhoc WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    $edit_data = $result_edit->fetch_assoc();
}

// ====== Thêm môn học ======
if (isset($_POST['add'])) {
    $mamon = $_POST['mamon'];
    $tenmon = $_POST['tenmon'];
    $sotinchi = $_POST['sotinchi'];
    $giaovien_id = $_POST['giaovien_id'];

    $stmt = $conn->prepare("INSERT INTO monhoc (mamon, tenmon, sotinchi, giaovien_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $mamon, $tenmon, $sotinchi, $giaovien_id);
    $msg = $stmt->execute() ? "✅ Thêm môn học thành công!" : "❌ Lỗi khi thêm môn học: " . $stmt->error;
}

// ====== Xóa môn học ======
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM monhoc WHERE id = ?");
    $stmt->bind_param("i", $id);
    $msg = $stmt->execute() ? "🗑 Xóa môn học thành công!" : "❌ Lỗi khi xóa môn học!";
}

// ====== Cập nhật môn học ======
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $mamon = $_POST['mamon'];
    $tenmon = $_POST['tenmon'];
    $sotinchi = $_POST['sotinchi'];
    $giaovien_id = $_POST['giaovien_id'];

    $stmt = $conn->prepare("UPDATE monhoc SET mamon=?, tenmon=?, sotinchi=?, giaovien_id=? WHERE id=?");
    $stmt->bind_param("ssiii", $mamon, $tenmon, $sotinchi, $giaovien_id, $id);
    $msg = $stmt->execute() ? "✏️ Cập nhật môn học thành công!" : "❌ Lỗi khi cập nhật môn học!";
}

// ====== Lấy danh sách môn học ======
$result = $conn->query("SELECT monhoc.*, giaovien.hoten AS ten_gv FROM monhoc 
    LEFT JOIN giaovien ON monhoc.giaovien_id = giaovien.id");

// ====== Thống kê ======
$stats = $conn->query("SELECT COUNT(*) AS total_subjects, SUM(sotinchi) AS total_credits FROM monhoc");
$stat_data = $stats->fetch_assoc();
$total_subjects = $stat_data['total_subjects'] ?? 0;
$total_credits = $stat_data['total_credits'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý môn học</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=school" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f7f9fc] min-h-screen text-[#1e293b]">

<!-- Header -->
<header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
    <div class="flex items-center space-x-6">
        <a href="dashboard.php" class="text-blue-600 font-bold text-lg select-none">
            <span class="material-symbols-outlined text-blue-600 font-extrabold text-3xl">school</span>
        </a>
        <ul class="hidden md:flex space-x-6 text-sm text-gray-700 font-normal">
            <li><a class="hover:text-gray-900" href="dashboard.php">Trang chủ</a></li>
            <li><a class="hover:text-gray-900" href="student_manage.php">Quản lý sinh viên</a></li>
            <li><a class="hover:text-gray-900" href="sub.php">Môn học</a></li>
            <li><a class="hover:text-gray-900" href="grades.php">Quản lý điểm</a></li>
        </ul>
    </div>
    <div class="flex items-center space-x-6 text-gray-500 text-lg relative">
        <i class="fas fa-bell hover:text-black cursor-pointer"></i>
        <div class="relative">
            <i class="fas fa-user-circle hover:text-black cursor-pointer"></i>
        </div>
    </div>
</header>

<!-- Main -->
<main class="max-w-6xl mx-auto p-6">

    <?php if (!empty($msg)): ?>
        <div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-md shadow">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <!-- Thống kê -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-4 rounded-lg shadow flex flex-col items-center">
            <div class="text-3xl">📄</div>
            <p class="text-gray-500">Tổng môn</p>
            <p class="text-2xl font-bold text-blue-600"><?= $total_subjects ?></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow flex flex-col items-center">
            <div class="text-3xl">🎓</div>
            <p class="text-gray-500">Tổng tín chỉ</p>
            <p class="text-2xl font-bold text-green-600"><?= $total_credits ?></p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-lg font-semibold mb-4"><?= isset($edit_data) ? '✏️ Cập nhật môn học' : '➕ Thêm môn học' ?></h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">

            <div>
                <label class="block text-gray-600 mb-1">Mã môn</label>
                <input type="text" name="mamon" required class="w-full border rounded-md px-3 py-2"
                       value="<?= $edit_data['mamon'] ?? '' ?>">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Tên môn</label>
                <input type="text" name="tenmon" required class="w-full border rounded-md px-3 py-2"
                       value="<?= $edit_data['tenmon'] ?? '' ?>">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Số tín chỉ</label>
                <input type="number" name="sotinchi" min="1" required class="w-full border rounded-md px-3 py-2"
                       value="<?= $edit_data['sotinchi'] ?? '' ?>">
            </div>
            <div>
                <label class="block text-gray-600 mb-1">Giáo viên ID</label>
                <input type="number" name="giaovien_id" required class="w-full border rounded-md px-3 py-2"
                       value="<?= $edit_data['giaovien_id'] ?? '' ?>">
            </div>

            <div class="md:col-span-4 flex justify-end">
                <button type="submit" name="<?= isset($edit_data) ? 'update' : 'add' ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md">
                    <?= isset($edit_data) ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
                <tr>
                    <th class="px-6 py-3 text-left text-gray-700 font-semibold text-sm uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-gray-700 font-semibold text-sm uppercase tracking-wider">Mã môn</th>
                    <th class="px-6 py-3 text-gray-700 font-semibold text-sm uppercase tracking-wider">Tên môn</th>
                    <th class="px-6 py-3 text-gray-700 font-semibold text-sm uppercase tracking-wider">Số tín chỉ</th>
                    <th class="px-6 py-3 text-gray-700 font-semibold text-sm uppercase tracking-wider">Giáo viên</th>
                    <th class="px-6 py-3 text-center text-gray-700 font-semibold text-sm uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-blue-50 transition duration-200 ease-in-out">
                        <td class="px-6 py-3 text-gray-600"><?= $row['id'] ?></td>
                        <td class="px-6 py-3 text-center font-medium text-blue-600"><?= $row['mamon'] ?></td>
                        <td class="px-6 py-3 text-gray-800"><?= $row['tenmon'] ?></td>
                        <td class="px-6 py-3 text-center text-gray-700"><?= $row['sotinchi'] ?></td>
                        <td class="px-6 py-3 text-center text-gray-600 italic"><?= $row['ten_gv'] ?? 'Chưa phân công' ?></td>
                        <td class="px-6 py-3 text-center">
                            <a href="?edit_id=<?= $row['id'] ?>" 
                               class="inline-block px-3 py-1 bg-blue-500 text-white text-sm rounded-lg shadow hover:bg-blue-600 transition">
                               ✏️ Sửa
                            </a>
                            <a href="?delete=<?= $row['id'] ?>" 
                               onclick="return confirm('Xác nhận xóa môn học này?')" 
                               class="inline-block px-3 py-1 bg-red-500 text-white text-sm rounded-lg shadow hover:bg-red-600 transition ml-2">
                               🗑 Xóa
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html>
