<?php
session_start();

// Nếu chưa đăng nhập hoặc không phải giáo viên thì quay lại login
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] !== 'giaovien') {
    header("Location: ../login.php");
    exit();
}

$hoTen = $_SESSION['ho_ten'] ?? "Giảng viên";
$email = $_SESSION['email'] ?? "Chưa rõ";
$giaovien_id = $_SESSION['id_thamchieu'] ?? 0;

// Kết nối database
$mysqli = new mysqli("localhost", "root", "", "student");
if ($mysqli->connect_errno) {
    die("Lỗi kết nối MySQL: " . $mysqli->connect_error);
}

// Thống kê
$soSinhVien = $mysqli->query("SELECT COUNT(*) as total FROM sinhvien")->fetch_assoc()['total'];
$soLop      = $mysqli->query("SELECT COUNT(*) as total FROM lop")->fetch_assoc()['total'];
$soBaiKT    = $mysqli->query("SELECT COUNT(*) as total FROM monhoc")->fetch_assoc()['total']; // tạm coi là số môn
$tiLeCoMat  = 94; // Demo vì chưa có bảng điểm danh

// Lịch dạy hôm nay (giả sử thứ hiện tại)
$today = date('N'); // 1=Thứ 2 ... 7=CN
$sqlLich = "
    SELECT m.tenmon, l.tenlop, lh.tietbatdau, lh.sotiet, lh.phong
    FROM lichhoc lh
    JOIN monhoc m ON lh.monhoc_id = m.id
    JOIN lop l ON lh.lop_id = l.id
    WHERE lh.giaovien_id = $giaovien_id AND lh.thu = '$today'
";
$lichDay = $mysqli->query($sqlLich)->fetch_all(MYSQLI_ASSOC);

// Hoạt động gần đây (demo, bạn có thể thêm bảng log sau này)
$hoatDong = [
    "Điểm danh lớp 12A1 - 30/32 sinh viên có mặt (2 giờ trước)",
    "Nhập điểm Toán học lớp 11B2 (4 giờ trước)",
    "Thêm sinh viên mới vào lớp 10A3 (1 ngày trước)"
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Giảng viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .card { border-radius: 12px; }
        .stat-card { text-align: center; padding: 20px; }
        .stat-card h3 { margin: 0; font-size: 28px; font-weight: bold; }
        .stat-card p { margin: 0; font-size: 16px; color: #666; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h3>Xin chào, <?php echo htmlspecialchars($hoTen); ?> 👋</h3>
        <p>Email: <?php echo htmlspecialchars($email); ?> | Vai trò: Giảng viên</p>
        <hr>

        <!-- Thống kê -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card shadow-sm">
                    <h3><?php echo $soSinhVien; ?></h3>
                    <p>Sinh viên</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm">
                    <h3><?php echo $soLop; ?></h3>
                    <p>Lớp học</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm">
                    <h3><?php echo $soBaiKT; ?></h3>
                    <p>Bài kiểm tra</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card shadow-sm">
                    <h3><?php echo $tiLeCoMat; ?>%</h3>
                    <p>Tỷ lệ có mặt</p>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Hoạt động gần đây -->
            <div class="col-md-6">
                <div class="card shadow-sm p-3">
                    <h5>Hoạt động gần đây</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($hoatDong as $hd): ?>
                            <li class="list-group-item"><?php echo $hd; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Lịch dạy hôm nay -->
            <div class="col-md-6">
                <div class="card shadow-sm p-3">
                    <h5>Lịch dạy hôm nay</h5>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($lichDay)): ?>
                            <li class="list-group-item">Hôm nay không có lịch dạy</li>
                        <?php else: ?>
                            <?php foreach ($lichDay as $lich): ?>
                                <li class="list-group-item">
                                    <strong><?php echo $lich['tenmon']; ?></strong> - 
                                    <?php echo $lich['tenlop']; ?> |
                                    Tiết <?php echo $lich['tietbatdau']; ?> (<?php echo $lich['sotiet']; ?> tiết) - 
                                    Phòng <?php echo $lich['phong']; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a class="btn btn-danger" href="../logout.php">Đăng xuất</a>
        </div>
    </div>
</body>
</html>
