<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "student";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// ===== XUẤT FILE CSV =====
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=diem_sinhvien.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'MSSV', 'Tên sinh viên', 'Mã môn', 'Tên môn học', 'Điểm CC', 'Điểm KT', 'Điểm Thi', 'Điểm TB', 'Trạng thái']);

    $sql_export = "SELECT d.id, d.mssv, s.hoten AS ten_sv, m.mamon AS ma_mon, m.tenmon AS ten_mon, 
                          d.diem_cc, d.diem_kt, d.diem_thi, d.diem_tb
                   FROM diem d
                   LEFT JOIN sinhvien s ON d.mssv = s.mssv
                   LEFT JOIN monhoc m ON d.monhoc_id = m.id
                   ORDER BY d.id DESC";
    $result_export = $conn->query($sql_export);

    while ($row = $result_export->fetch_assoc()) {
        $trangthai = $row['diem_tb'] >= 5.0 ? 'Đạt' : 'Không đạt';
        fputcsv($output, [
            $row['id'],
            $row['mssv'],
            $row['ten_sv'],
            $row['ma_mon'],
            $row['ten_mon'],
            $row['diem_cc'],
            $row['diem_kt'],
            $row['diem_thi'],
            number_format($row['diem_tb'], 2),
            $trangthai
        ]);
    }
    fclose($output);
    exit;
}


// Bảo vệ trang chỉ cho phép đăng nhập

// ===== Hàm tiện ích tính điểm trung bình =====
function calc_diem_tb($cc, $kt, $thi) {
    $tb = ($cc + 3 * $kt + 6 * $thi) / 10;
    return round($tb, 2);
}

// ===== Xử lý Thêm / Cập nhật =====
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // bảo vệ input cơ bản
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
    $mssv = $_POST['mssv'] ?? '';
    $monhoc_id = isset($_POST['monhoc_id']) ? intval($_POST['monhoc_id']) : 0;
    $diem_cc = is_numeric($_POST['diem_cc']) ? floatval($_POST['diem_cc']) : 0;
    $diem_kt = is_numeric($_POST['diem_kt']) ? floatval($_POST['diem_kt']) : 0;
    $diem_thi = is_numeric($_POST['diem_thi']) ? floatval($_POST['diem_thi']) : 0;

    $diem_tb = calc_diem_tb($diem_cc, $diem_kt, $diem_thi);
    $trangthai = $diem_tb >= 5.0 ? 'Đạt' : 'Không đạt';

    if ($id) {
        // update
        $stmt = $conn->prepare("UPDATE diem SET mssv=?, monhoc_id=?, diem_cc=?, diem_kt=?, diem_thi=?, diem_tb=? WHERE id=?");
        $stmt->bind_param("siddddi", $mssv, $monhoc_id, $diem_cc, $diem_kt, $diem_thi, $diem_tb, $id);
        if ($stmt->execute()) {
            $msg = "✏️ Cập nhật điểm thành công.";
        } else {
            $msg = "❌ Lỗi khi cập nhật: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // insert
        $stmt = $conn->prepare("INSERT INTO diem (mssv, monhoc_id, diem_cc, diem_kt, diem_thi, diem_tb) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidddd", $mssv, $monhoc_id, $diem_cc, $diem_kt, $diem_thi, $diem_tb);
        if ($stmt->execute()) {
            $msg = "✅ Thêm điểm thành công.";
        } else {
            $msg = "❌ Lỗi khi thêm: " . $stmt->error;
        }
        $stmt->close();
    }

    // redirect tránh re-submit
    header("Location: " . $_SERVER['PHP_SELF'] . (isset($_GET['page']) ? '?page='.$_GET['page'] : ''));
    exit;
}

// ===== Xử lý Xóa =====
if (isset($_GET['delete_id'])) {
    $del = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM diem WHERE id = ?");
    $stmt->bind_param("i", $del);
    $ok = $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ===== Lấy dữ liệu để sửa (nếu có) =====
$edit = null;
if (isset($_GET['edit_id'])) {
    $eid = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM diem WHERE id = ?");
    $stmt->bind_param("i", $eid);
    $stmt->execute();
    $res = $stmt->get_result();
    $edit = $res->fetch_assoc();
    $stmt->close();
}

// ===== Lấy danh sách Sinh viên & Môn học cho form select =====
$sv_list = $conn->query("SELECT mssv, hoten FROM sinhvien ORDER BY hoten");
$mh_list = $conn->query("SELECT id, mamon, tenmon FROM monhoc ORDER BY tenmon");

// ===== Lấy danh sách điểm (JOIN sinhvien & monhoc) =====
$sql = "SELECT d.*, s.hoten AS ten_sv, m.tenmon AS ten_mon, m.mamon AS ma_mon
        FROM diem d
        LEFT JOIN sinhvien s ON d.mssv = s.mssv
        LEFT JOIN monhoc m ON d.monhoc_id = m.id
        ORDER BY d.id DESC";
$diem_list = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Quản lý điểm</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style> body { font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; } </style>

    </nav>
</header>
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

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-12">
    <section>
        <h1 class="text-xl font-semibold text-gray-900 mb-1">Quản lý điểm</h1>
        <p class="text-xs text-gray-600 mb-6">Nhập và quản lý điểm số các môn học của sinh viên</p>

        <?php if ($msg): ?>
            <div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-md shadow"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0 mb-6">
            <!-- Dùng anchor để mở form "Nhập điểm" ở cùng trang (scroll) -->
            <a href="#form" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-semibold hover:bg-blue-700 focus:outline-none">
                <i class="fas fa-plus mr-2"></i> Nhập điểm
            </a>
            <!-- Xuất báo cáo: placeholder (bạn có thể nối chức năng xuất CSV/PDF sau) -->
            <a href="?export=csv" class="inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-white text-sm font-semibold hover:bg-green-700 focus:outline-none">
                <i class="fas fa-download mr-2"></i> Xuất CSV
            </a>
        </div>

        <!-- Thống kê đơn giản -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
            <div class="flex items-center justify-between bg-blue-50 rounded-lg p-4">
                <div>
                    <p class="text-xs text-gray-600 mb-1">Tổng bản ghi</p>
                    <p class="text-2xl font-bold text-gray-900 leading-none"><?= $diem_list->num_rows ?></p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 rounded-md bg-blue-600 text-white"><i class="far fa-file-alt text-lg"></i></div>
            </div>
            <div class="flex items-center justify-between bg-green-50 rounded-lg p-4">
                <?php
                // nhanh: đếm số đạt
                $r = $conn->query("SELECT COUNT(*) AS c FROM diem WHERE diem_tb >= 5");
                $c_dat = $r->fetch_assoc()['c'] ?? 0;
                ?>
                <div>
                    <p class="text-xs text-gray-600 mb-1">Đạt</p>
                    <p class="text-2xl font-bold text-gray-900 leading-none"><?= $c_dat ?></p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 rounded-md bg-green-500 text-white"><i class="fas fa-check-circle text-lg"></i></div>
            </div>
            <div class="flex items-center justify-between bg-red-50 rounded-lg p-4">
                <?php
                $r2 = $conn->query("SELECT COUNT(*) AS c FROM diem WHERE diem_tb < 5");
                $c_kdat = $r2->fetch_assoc()['c'] ?? 0;
                ?>
                <div>
                    <p class="text-xs text-gray-600 mb-1">Không đạt</p>
                    <p class="text-2xl font-bold text-gray-900 leading-none"><?= $c_kdat ?></p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 rounded-md bg-red-600 text-white"><i class="fas fa-times-circle text-lg"></i></div>
            </div>
            <div class="flex items-center justify-between bg-orange-50 rounded-lg p-4">
                <?php
                // trung bình hệ thống (trung bình của các diem_tb)
                $r3 = $conn->query("SELECT AVG(diem_tb) AS avg_tb FROM diem");
                $avg_tb = $r3->fetch_assoc()['avg_tb'] ?? 0;
                ?>
                <div>
                    <p class="text-xs text-gray-600 mb-1">Điểm TB hệ thống</p>
                    <p class="text-2xl font-bold text-gray-900 leading-none"><?= $avg_tb ? number_format($avg_tb,2) : '0.00' ?></p>
                </div>
                <div class="flex items-center justify-center w-10 h-10 rounded-md bg-orange-500 text-white"><i class="fas fa-chart-bar text-lg"></i></div>
            </div>
        </div>

        <!-- FORM Nhập / Sửa điểm -->
        <div id="form" class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4"><?= $edit ? '✏️ Cập nhật điểm' : '➕ Nhập điểm mới' ?></h2>
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

                <div>
                    <label class="block text-gray-600 mb-1">Sinh viên</label>
                    <select name="mssv" required class="w-full border rounded-md px-3 py-2">
                        <option value="">-- Chọn sinh viên --</option>
                        <?php
                        $sv_list->data_seek(0);
                        while ($sv = $sv_list->fetch_assoc()) :
                            $selected = ($edit && $edit['mssv'] === $sv['mssv']) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($sv['mssv']) ?>" <?= $selected ?>><?= htmlspecialchars($sv['hoten'] . ' — ' . $sv['mssv']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Môn học</label>
                    <select name="monhoc_id" required class="w-full border rounded-md px-3 py-2">
                        <option value="">-- Chọn môn học --</option>
                        <?php
                        $mh_list->data_seek(0);
                        while ($mh = $mh_list->fetch_assoc()) :
                            $sel = ($edit && $edit['monhoc_id'] == $mh['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= intval($mh['id']) ?>" <?= $sel ?>><?= htmlspecialchars($mh['tenmon'] . ' (' . $mh['mamon'] . ')') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Điểm chuyên cần (CC)</label>
                    <input type="number" step="0.1" min="0" max="10" name="diem_cc" required class="w-full border rounded-md px-3 py-2" value="<?= isset($edit['diem_cc']) ? $edit['diem_cc'] : '' ?>">
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Điểm kiểm tra (KT)</label>
                    <input type="number" step="0.1" min="0" max="10" name="diem_kt" required class="w-full border rounded-md px-3 py-2" value="<?= isset($edit['diem_kt']) ? $edit['diem_kt'] : '' ?>">
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Điểm thi (CK)</label>
                    <input type="number" step="0.1" min="0" max="10" name="diem_thi" required class="w-full border rounded-md px-3 py-2" value="<?= isset($edit['diem_thi']) ? $edit['diem_thi'] : '' ?>">
                </div>

                <div class="sm:col-span-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md">
                        <?= $edit ? 'Cập nhật' : 'Thêm điểm' ?>
                    </button>
                    <?php if ($edit): ?>
                        <a href="diem.php" class="ml-3 inline-flex items-center px-4 py-2 border rounded text-gray-700">Hủy</a>
                    <?php endif; ?>
                </div>
            </form>
            <p class="text-xs text-gray-500 mt-3">Công thức điểm TB: (CC + 3×KT + 6×THI) / 10 — làm tròn 2 chữ số.</p>
        </div>

        <!-- Bảng danh sách điểm -->
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3 p-4">
                <input id="search" aria-label="Search" class="flex-grow border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="Tìm kiếm theo tên, mã SV, môn học..." type="search"/>
                <select id="filterStatus" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Đạt">Đạt</option>
                    <option value="Không đạt">Không đạt</option>
                </select>
            </div>

            <table id="tbl" class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Sinh viên</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Môn học</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-900">CC</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-900">KT</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-900">THI</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-900">Điểm TB</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Trạng thái</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-900">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                <?php while ($r = $diem_list->fetch_assoc()): 
                    $status = floatval($r['diem_tb']) >= 5.0 ? 'Đạt' : 'Không đạt';
                ?>
                    <tr class="hover:bg-blue-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($r['ten_sv'] ?: '—') ?></p>
                            <p class="text-xs text-gray-400"><?= htmlspecialchars($r['mssv']) ?></p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-semibold text-gray-900"><?= htmlspecialchars($r['ten_mon'] ?: '—') ?></p>
                            <p class="text-xs text-gray-400"><?= htmlspecialchars($r['ma_mon'] ?: '') ?></p>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-center text-gray-700"><?= $r['diem_cc'] ?></td>
                        <td class="px-3 py-4 whitespace-nowrap text-center text-gray-700"><?= $r['diem_kt'] ?></td>
                        <td class="px-3 py-4 whitespace-nowrap text-center text-gray-700"><?= $r['diem_thi'] ?></td>
                        <td class="px-3 py-4 whitespace-nowrap text-center text-gray-900 font-semibold"><?= number_format($r['diem_tb'],2) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($status === 'Đạt'): ?>
                                <span class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full"><?= $status ?></span>
                            <?php else: ?>
                                <span class="inline-block bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded-full"><?= $status ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <a href="?edit_id=<?= $r['id'] ?>" class="text-yellow-500 mr-2">✏️</a>
                            <a href="?delete_id=<?= $r['id'] ?>" onclick="return confirm('Xóa bản ghi điểm này?')" class="text-red-500">🗑️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<!-- JS: Tìm kiếm/Filter trên client để tiện dùng -->
<script>
document.getElementById('search').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const rows = document.querySelectorAll('#tbl tbody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(q) ? '' : 'none';
    });
});
document.getElementById('filterStatus').addEventListener('change', function() {
    const val = this.value;
    const rows = document.querySelectorAll('#tbl tbody tr');
    rows.forEach(r => {
        if (!val) { r.style.display = ''; return; }
        const status = r.querySelector('td:nth-child(7)').innerText.trim();
        r.style.display = status === val ? '' : 'none';
    });
});
</script>
</body>
</html>
