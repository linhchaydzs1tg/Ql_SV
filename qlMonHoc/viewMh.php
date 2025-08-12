<?php
// viewMh.php (đã fix)
// Kết nối CSDL
require_once '../config/db.php';

// helper: chạy truy vấn, dừng và in lỗi nếu thất bại
function runQuery($conn, $sql) {
    $res = $conn->query($sql);
    if (!$res) {
        die("Lỗi truy vấn SQL: " . $conn->error . " — SQL: " . htmlspecialchars($sql));
    }
    return $res;
}

// Lấy danh sách cột của bảng monhoc để biết cột nào tồn tại
$colsRes = runQuery($conn, "SHOW COLUMNS FROM `monhoc`");
$columns = [];
while ($r = $colsRes->fetch_assoc()) {
    $columns[] = $r['Field'];
}

// tìm tên cột phù hợp (nhiều biến thể để tương thích)
function firstColMatch($columns, $candidates) {
    foreach ($candidates as $c) {
        if (in_array($c, $columns)) return $c;
    }
    return false;
}

$col_ma         = firstColMatch($columns, ['ma_mon','mamon','maMon']);
$col_ten        = firstColMatch($columns, ['ten_mon','tenmon','tenMon']);
$col_tc         = firstColMatch($columns, ['so_tc','sotinchi','so_tc','so_tin_chi']);
$col_loai       = firstColMatch($columns, ['loai_mon','loai_monhoc']);
$col_khoa       = firstColMatch($columns, ['khoa']);
$col_hoc_ky     = firstColMatch($columns, ['hoc_ky','hocky']);
$col_mon_tq     = firstColMatch($columns, ['mon_tien_quyet','mon_tienquyet','mon_tien_quyet']);
$col_mo_ta      = firstColMatch($columns, ['mo_ta','mota','moTa','description']);
$col_giaovienid = firstColMatch($columns, ['giaovien_id','giaovienid']);

// Thống kê an toàn (chỉ chạy truy vấn tham chiếu tới cột nếu cột tồn tại)
$tongMon    = runQuery($conn, "SELECT COUNT(*) FROM `monhoc`")->fetch_row()[0] ?? 0;

if ($col_loai) {
    $batBuoc = runQuery($conn, "SELECT COUNT(*) FROM `monhoc` WHERE `$col_loai` = 'Bắt buộc'")->fetch_row()[0] ?? 0;
    $tuChon  = runQuery($conn, "SELECT COUNT(*) FROM `monhoc` WHERE `$col_loai` = 'Tự chọn'")->fetch_row()[0] ?? 0;
} else {
    // nếu không có cột loai_mon, đặt mặc định 0 (hoặc bạn có thể để null/—)
    $batBuoc = 0;
    $tuChon  = 0;
}

if ($col_tc) {
    $tongTinChi = runQuery($conn, "SELECT SUM(`$col_tc`) FROM `monhoc`")->fetch_row()[0] ?? 0;
} else {
    $tongTinChi = 0;
}

// Lấy danh sách môn học — nếu có giaovien_id thì join để lấy tên GV
if ($col_giaovienid) {
    $sql = "SELECT m.*, g.hoten AS giaovien
            FROM `monhoc` AS m
            LEFT JOIN `giaovien` AS g ON m.`$col_giaovienid` = g.id";
} else {
    $sql = "SELECT * FROM `monhoc`";
}
$dsMonHoc = runQuery($conn, $sql);

// hàm lấy giá trị an toàn từ row theo tên cột
function val($row, $col, $fallback = '—') {
    return isset($row[$col]) && $row[$col] !== null && $row[$col] !== '' ? htmlspecialchars($row[$col]) : $fallback;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý môn học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-batbuoc { background-color: #f8d7da; color: #721c24; }
        .badge-tuchon  { background-color: #d1ecf1; color: #0c5460; }
        .badge-daicuong { background-color: #fff3cd; color: #856404; }
        .stat-card { border-radius: 10px; padding: 14px; }
    </style>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-1 fw-bold">Quản lý môn học</h2>
    <p class="text-muted mb-4">Quản lý thông tin môn học và chương trình đào tạo</p>

    <!-- Thống kê -->
    <div class="row text-center mb-4">
        <div class="col">
            <div class="stat-card bg-primary text-white rounded">Tổng môn học<br><strong><?= $tongMon ?></strong></div>
        </div>
        <div class="col">
            <div class="stat-card bg-danger text-white rounded">Môn bắt buộc<br><strong><?= $batBuoc ?></strong></div>
        </div>
        <div class="col">
            <div class="stat-card bg-success text-white rounded">Môn tự chọn<br><strong><?= $tuChon ?></strong></div>
        </div>
        <div class="col">
            <div class="stat-card bg-warning text-dark rounded">Tổng tín chỉ<br><strong><?= $tongTinChi ?></strong></div>
        </div>
    </div>

    <!-- Tìm kiếm -->
    <div class="input-group mb-4">
        <input id="search" type="text" class="form-control" placeholder="Tìm kiếm theo tên môn học, mã môn...">
        <button class="btn btn-outline-secondary" type="button">Tìm</button>
    </div>

    <!-- Danh sách môn học -->
    <div class="row" id="course-list">
        <?php while ($mh = $dsMonHoc->fetch_assoc()): ?>
            <div class="col-md-4 mb-4 course-item">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <!-- Tên môn: thử nhiều cột khả dụng -->
                            <h5 class="card-title">
                                <?= val($mh, $col_ten ?: 'ten') ?>
                            </h5>

                            <!-- Badge: nếu có cột loai_mon dùng nó, nếu không hiển thị 'Không rõ' -->
                            <?php
                                $loaiVal = $col_loai ? ($mh[$col_loai] ?? '') : '';
                                if ($col_loai && $loaiVal === 'Bắt buộc') {
                                    echo '<span class="badge badge-batbuoc">Bắt buộc</span>';
                                } elseif ($col_loai && $loaiVal === 'Tự chọn') {
                                    echo '<span class="badge badge-tuchon">Tự chọn</span>';
                                } elseif ($col_loai && $loaiVal) {
                                    echo '<span class="badge badge-daicuong">' . htmlspecialchars($loaiVal) . '</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">Không rõ</span>';
                                }
                            ?>
                        </div>

                        <p class="mb-1">Mã môn: <strong><?= val($mh, $col_ma ?: 'mamon') ?></strong></p>
                        <p class="mb-1">Số tín chỉ: <?= val($mh, $col_tc ?: 'sotinchi', '0') ?></p>
                        <p class="mb-1">Khoa: <?= val($mh, $col_khoa ?: '—') ?></p>
                        <p class="mb-1">Học kỳ: <?= val($mh, $col_hoc_ky ?: '—') ?></p>
                        <p class="mb-1">Môn tiên quyết: <span class="badge bg-warning text-dark"><?= val($mh, $col_mon_tq ?: '—') ?></span></p>

                        <!-- Mô tả -->
                        <p class="text-muted"><em><?= val($mh, $col_mo_ta ?: 'mo_ta', '') ?></em></p>

                        <!-- Giáo viên (nếu join) -->
                        <?php if (isset($mh['giaovien'])): ?>
                            <p class="mb-1">Giáo viên: <?= htmlspecialchars($mh['giaovien']) ?></p>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mt-3">
                            <a href="xemChiTiet.php?id=<?= htmlspecialchars($mh['id']) ?>" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                            <div>
                                <a href="suaMon.php?id=<?= htmlspecialchars($mh['id']) ?>" class="btn btn-sm btn-warning">✏️</a>
                                <a href="xoaMon.php?id=<?= htmlspecialchars($mh['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa môn học này?')">🗑️</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <a href="themMon.php" class="btn btn-primary mt-4">+ Thêm môn học</a>
</div>

<script>
    // Tìm kiếm client-side (lọc bằng nội dung text của thẻ)
    document.getElementById('search').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.course-item').forEach(item => {
            item.style.display = item.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
</body>
</html>
