<?php
require_once __DIR__ . '/_auth.php';
$active = 'attendance'; $title = 'Điểm danh sinh viên';

// Lớp ưu tiên lấy theo lịch dạy GV, nếu chưa có thì lấy tất cả lớp
$lopList = [];
if ($st = $mysqli->prepare("
    SELECT DISTINCT l.id, l.tenlop
    FROM lichhoc lh JOIN lop l ON l.id = lh.lop_id
    WHERE lh.giaovien_id = ?
    ORDER BY l.tenlop
")) {
    $st->bind_param("i", $giaovien_id);
    $st->execute();
    $lopList = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
}
if (!$lopList) $lopList = $mysqli->query("SELECT id, tenlop FROM lop ORDER BY tenlop")->fetch_all(MYSQLI_ASSOC);

$lop_id = (int)($_GET['lop_id'] ?? ($_POST['lop_id'] ?? ($lopList[0]['id'] ?? 0)));
$ngay = $_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d'));

// Lấy danh sách SV
$svList = [];
if ($lop_id > 0) {
    if ($st = $mysqli->prepare("SELECT mssv, hoten FROM sinhvien WHERE lop_id=? ORDER BY hoten")) {
        $st->bind_param("i", $lop_id);
        $st->execute();
        $svList = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();
    }
}

// Xử lý submit (demo: không ghi DB)
$saved = false; $stats = ['present'=>0,'late'=>0,'absent'=>0];
if (isset($_POST['save'])) {
    foreach ($svList as $sv) {
        $val = $_POST['att'][$sv['mssv']] ?? 'present';
        if ($val === 'present') $stats['present']++;
        elseif ($val === 'late') $stats['late']++;
        else $stats['absent']++;
    }
    $saved = true;
}

include __DIR__ . '/_layout_top.php';
?>
<h1 class="text-2xl font-extrabold">Điểm danh sinh viên</h1>
<p class="text-sm text-slate-500 mt-1 mb-5">Ghi nhận sự có mặt và theo dõi tỷ lệ</p>

<?php if ($saved): ?>
  <div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-700">
    Đã “lưu” điểm danh (demo) cho ngày <?= htmlspecialchars($ngay) ?>.
    Có mặt: <?= $stats['present'] ?> · Muộn: <?= $stats['late'] ?> · Vắng: <?= $stats['absent'] ?>.
  </div>
<?php endif; ?>

<form class="bg-white rounded-xl shadow-sm p-5" method="get">
  <div class="flex flex-col sm:flex-row gap-3">
    <select name="lop_id" class="h-10 bg-white border rounded-lg px-3">
      <?php foreach ($lopList as $l): ?>
        <option value="<?= (int)$l['id'] ?>" <?= $lop_id===(int)$l['id']?'selected':'' ?>>
          Lớp <?= htmlspecialchars($l['tenlop']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input type="date" name="date" value="<?= htmlspecialchars($ngay) ?>" class="h-10 bg-white border rounded-lg px-3">
    <button class="h-10 px-4 rounded-lg bg-blue-600 text-white">Chọn</button>
  </div>
</form>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
  <div class="lg:col-span-2">
    <div class="bg-white rounded-xl shadow-sm">
      <div class="border-b px-5 py-3 font-semibold">Điểm danh hôm nay</div>
      <form method="post">
        <input type="hidden" name="lop_id" value="<?= (int)$lop_id ?>">
        <input type="hidden" name="date" value="<?= htmlspecialchars($ngay) ?>">
        <ul class="divide-y">
          <?php if (!$svList): ?>
            <li class="p-5 text-slate-600">Lớp chưa có sinh viên.</li>
          <?php else: foreach ($svList as $sv): ?>
            <li class="p-4 flex items-center justify-between">
              <div>
                <div class="font-medium"><?= htmlspecialchars($sv['hoten']) ?></div>
                <div class="text-xs text-slate-500"><?= htmlspecialchars($sv['mssv']) ?></div>
              </div>
              <div class="flex items-center gap-4 text-sm">
                <label class="flex items-center gap-1">
                  <input type="radio" name="att[<?= htmlspecialchars($sv['mssv']) ?>]" value="present" checked> Có mặt
                </label>
                <label class="flex items-center gap-1">
                  <input type="radio" name="att[<?= htmlspecialchars($sv['mssv']) ?>]" value="late"> Muộn
                </label>
                <label class="flex items-center gap-1">
                  <input type="radio" name="att[<?= htmlspecialchars($sv['mssv']) ?>]" value="absent"> Vắng
                </label>
              </div>
            </li>
          <?php endforeach; endif; ?>
        </ul>
        <div class="p-4 border-t flex justify-end">
          <button name="save" class="px-4 py-2 bg-emerald-600 text-white rounded-lg">Lưu điểm danh</button>
        </div>
      </form>
    </div>
  </div>

  <div>
    <div class="bg-white rounded-xl shadow-sm">
      <div class="border-b px-5 py-3 font-semibold">Tổng quan</div>
      <div class="p-5 grid grid-cols-2 gap-3 text-center">
        <div class="rounded-lg bg-emerald-50 py-4">
          <div class="text-3xl font-extrabold"><?= $saved ? $stats['present'] : 0 ?></div>
          <div class="text-sm text-emerald-700 mt-1">Có mặt</div>
        </div>
        <div class="rounded-lg bg-rose-50 py-4">
          <div class="text-3xl font-extrabold"><?= $saved ? $stats['absent'] : 0 ?></div>
          <div class="text-sm text-rose-700 mt-1">Vắng</div>
        </div>
        <div class="rounded-lg bg-amber-50 py-4 col-span-2">
          <div class="text-xl font-bold"><?= $saved ? $stats['late'] : 0 ?></div>
          <div class="text-sm text-amber-700 mt-1">Đi muộn</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>

<?php /* 
-- Muốn LƯU DB, tạo bảng:
CREATE TABLE diemdanh (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ngay DATE NOT NULL,
  lop_id INT NOT NULL,
  mssv VARCHAR(20) NOT NULL,
  trangthai ENUM('present','late','absent') NOT NULL,
  UNIQUE KEY uniq_ngay_lop_sv (ngay, lop_id, mssv)
);
-- Và trong xử lý POST phía trên, thay phần “demo” bằng INSERT ... ON DUPLICATE KEY UPDATE
*/ ?>
