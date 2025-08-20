<?php
// giangvien/dashboard_gv.php
session_start();

/* ========= BẢO VỆ ĐĂNG NHẬP ========= */
if (!isset($_SESSION['user_id']) || ($_SESSION['vai_tro'] ?? '') !== 'giaovien') {
    header("Location: ../auth/login.php"); // login.php nằm trong /auth
    exit();
}
$giaovien_id = (int)($_SESSION['id_thamchieu'] ?? 0);

/* ========= KẾT NỐI CSDL ========= */
$mysqli = new mysqli("localhost", "root", "", "student");
if ($mysqli->connect_errno) {
    die("Lỗi kết nối MySQL: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

/* ========= TIỆN ÍCH ========= */
function time_range_from_tiet(int $tietbatdau, int $sotiet, string $base = "08:00", int $mins_per_tiet = 45): array {
    // Quy ước: Tiết 1 bắt đầu 08:00, mỗi tiết 45 phút
    $base_ts  = strtotime($base);
    $start_ts = $base_ts + ($tietbatdau - 1) * $mins_per_tiet * 60;
    $end_ts   = $start_ts + $sotiet * $mins_per_tiet * 60;
    return [date("H:i", $start_ts), date("H:i", $end_ts)];
}

/* ========= THÔNG TIN GIÁO VIÊN ========= */
$hoTen = "Giảng viên";
$email = "Chưa rõ";
if ($giaovien_id > 0) {
    if ($st = $mysqli->prepare("SELECT hoten, email FROM giaovien WHERE id = ? LIMIT 1")) {
        $st->bind_param("i", $giaovien_id);
        $st->execute();
        if ($r = $st->get_result()->fetch_assoc()) {
            $hoTen = $r['hoten'] ?: $hoTen;
            $email = $r['email'] ?: $email;
        }
        $st->close();
    }
}

/* ========= THỐNG KÊ ========= */
$soSinhVien = 0;
$soLop      = 0;
$soBaiKT    = 0;
$tiLeCoMat  = 94; // demo

if ($res = $mysqli->query("SELECT COUNT(*) c FROM sinhvien")) {
    $soSinhVien = (int)$res->fetch_assoc()['c'];
}
if ($res = $mysqli->query("SELECT COUNT(*) c FROM lop")) {
    $soLop = (int)$res->fetch_assoc()['c'];
}
// Tạm coi "Bài kiểm tra" = số môn GV phụ trách (chưa có bảng bài kiểm tra riêng)
if ($st = $mysqli->prepare("SELECT COUNT(*) c FROM monhoc WHERE giaovien_id = ?")) {
    $st->bind_param("i", $giaovien_id);
    $st->execute();
    $soBaiKT = (int)$st->get_result()->fetch_assoc()['c'];
    $st->close();
}

/* ========= LỊCH DẠY HÔM NAY ========= */
$thuToday = (string)date('N'); // 1..7
$lichDay = [];
if ($st = $mysqli->prepare("
    SELECT m.tenmon, l.tenlop, lh.tietbatdau, lh.sotiet, lh.phong
    FROM lichhoc lh
    JOIN monhoc m ON m.id = lh.monhoc_id
    JOIN lop   l ON l.id = lh.lop_id
    WHERE lh.giaovien_id = ? AND (lh.thu = ? OR lh.thu = CONCAT('Thu', ?))
    ORDER BY lh.tietbatdau ASC
")) {
    $st->bind_param("iss", $giaovien_id, $thuToday, $thuToday);
    $st->execute();
    $lichDay = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
}

/* ========= HOẠT ĐỘNG GẦN ĐÂY (demo) ========= */
$hoatDong = [
    ["text" => "Điểm danh lớp 12A1 - 30/32 sinh viên có mặt", "time" => "2 giờ trước"],
    ["text" => "Nhập điểm Toán học lớp 11B2", "time" => "4 giờ trước"],
    ["text" => "Add new user vào lớp 10A3", "time" => "1 ngày trước"],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Giảng viên</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f7f9fc] text-slate-900">
    <!-- Topbar -->
    <header class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold">Q</div>
                <span class="font-semibold">QuanLySV</span>
                <nav class="ml-6 hidden md:flex items-center gap-6 text-sm text-slate-600">
                    <a class="text-blue-600 font-medium" href="#">Trang chủ</a>
                    <a href="sinhvien.php">Danh sách sinh viên</a>
                    <a href="lophoc.php">Lớp học</a>
                    <a href="diemso.php">Điểm số</a>
                    <a href="diemdanh.php">Điểm danh</a>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-sm text-slate-500">
                    <div class="font-medium text-slate-700"><?= htmlspecialchars($hoTen) ?></div>
                    <div><?= htmlspecialchars($email) ?></div>
                </div>
                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center">👩‍🏫</div>
                <!-- Link đăng xuất: dùng file trong /auth như bạn yêu cầu -->
                <a href="/QL_SV/auth/logout.php" class="hidden sm:inline-block text-sm px-3 py-1.5 rounded-md border text-slate-700 hover:bg-slate-50">Đăng xuất</a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
        <h1 class="text-2xl font-extrabold">Chào mừng trở lại!</h1>
        <p class="text-sm text-slate-500 mt-1 mb-5">Tổng quan hoạt động quản lý sinh viên</p>

        <!-- Thẻ thống kê -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 text-sm">Sinh viên</span>
                    <span class="w-9 h-9 rounded-lg bg-blue-600/10 flex items-center justify-center">🔎</span>
                </div>
                <div class="text-3xl font-extrabold mt-2"><?= number_format($soSinhVien) ?></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 text-sm">Lớp học</span>
                    <span class="w-9 h-9 rounded-lg bg-emerald-600/10 flex items-center justify-center">📋</span>
                </div>
                <div class="text-3xl font-extrabold mt-2"><?= number_format($soLop) ?></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 text-sm">Bài kiểm tra</span>
                    <span class="w-9 h-9 rounded-lg bg-violet-600/10 flex items-center justify-center">🗂️</span>
                </div>
                <div class="text-3xl font-extrabold mt-2"><?= number_format($soBaiKT) ?></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 text-sm">Tỷ lệ có mặt</span>
                    <span class="w-9 h-9 rounded-lg bg-orange-500/10 flex items-center justify-center">⚙️</span>
                </div>
                <div class="text-3xl font-extrabold mt-2"><?= (int)$tiLeCoMat ?>%</div>
            </div>
        </section>

        <!-- Hoạt động gần đây & Lịch dạy hôm nay -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold mb-3">Hoạt động gần đây</h2>
                <ul class="space-y-3">
                    <?php foreach ($hoatDong as $hd): ?>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5">🔔</span>
                            <div>
                                <div class="font-medium"><?= htmlspecialchars($hd['text']) ?></div>
                                <div class="text-xs text-slate-500"><?= htmlspecialchars($hd['time']) ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold mb-3">Lịch dạy hôm nay</h2>
                <?php if (!$lichDay): ?>
                    <div class="text-slate-500 text-sm">Hôm nay không có lịch dạy.</div>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($lichDay as $ld):
                            [$tStart, $tEnd] = time_range_from_tiet((int)$ld['tietbatdau'], (int)$ld['sotiet']);
                        ?>
                            <li class="rounded-lg border p-3">
                                <div class="font-semibold"><?= htmlspecialchars($ld['tenmon']) ?></div>
                                <div class="text-sm text-slate-600">
                                    <?= htmlspecialchars($ld['tenlop']) ?> •
                                    <?= $tStart ?> - <?= $tEnd ?> •
                                    Phòng <?= htmlspecialchars($ld['phong']) ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

        <!-- Lối tắt -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <a href="sinhvien.php" class="bg-white rounded-xl shadow-sm p-5 hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-blue-600/10 flex items-center justify-center mb-2">🔎</div>
                <div class="font-semibold">Sinh viên</div>
                <div class="text-sm text-slate-500">Quản lý danh sách</div>
            </a>
            <a href="lophoc.php" class="bg-white rounded-xl shadow-sm p-5 hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-emerald-600/10 flex items-center justify-center mb-2">📋</div>
                <div class="font-semibold">Lớp học</div>
                <div class="text-sm text-slate-500">Quản lý lớp học</div>
            </a>
            <a href="diemso.php" class="bg-white rounded-xl shadow-sm p-5 hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-violet-600/10 flex items-center justify-center mb-2">🗂️</div>
                <div class="font-semibold">Điểm số</div>
                <div class="text-sm text-slate-500">Nhập/Xem điểm</div>
            </a>
            <a href="diemdanh.php" class="bg-white rounded-xl shadow-sm p-5 hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center mb-2">🕒</div>
                <div class="font-semibold">Điểm danh</div>
                <div class="text-sm text-slate-500">Ghi nhận chuyên cần</div>
            </a>
        </section>
    </main>

    <?php include '../chat/chat.php'; ?>
</body>
</html>
