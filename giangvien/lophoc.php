<?php
require_once __DIR__ . '/_auth.php';
$active = 'classes'; $title = 'Quản lý lớp học';

// Lấy lớp + môn theo lịch dạy của GV
$cards = [];
if ($st = $mysqli->prepare("
    SELECT l.id AS lop_id, l.tenlop, m.id AS mon_id, m.tenmon, g.hoten AS tengv,
           lh.thu, lh.tietbatdau, lh.sotiet, lh.phong,
           (SELECT COUNT(*) FROM sinhvien sv WHERE sv.lop_id = l.id) AS siso
    FROM lichhoc lh
    JOIN lop l ON l.id = lh.lop_id
    JOIN monhoc m ON m.id = lh.monhoc_id
    JOIN giaovien g ON g.id = lh.giaovien_id
    WHERE lh.giaovien_id = ?
    ORDER BY l.tenlop, m.tenmon
")) {
    $st->bind_param("i", $giaovien_id);
    $st->execute();
    $cards = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
}
function tiet2range($t, $s, $base="08:00", $len=45) {
    $b = strtotime($base); $st = $b + ($t-1)*$len*60; $en = $st + $s*$len*60;
    return date("H:i",$st) . "-" . date("H:i",$en);
}
include __DIR__ . '/_layout_top.php';
?>
<h1 class="text-2xl font-extrabold">Quản lý lớp học</h1>
<p class="text-sm text-slate-500 mt-1 mb-5">Danh sách các lớp học đang giảng dạy</p>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <?php if (!$cards): ?>
    <div class="col-span-full bg-white rounded-xl p-6 text-slate-600">
      Chưa có lịch dạy cho giáo viên này. Thêm bản ghi vào bảng <code>lichhoc</code> để hiển thị.
    </div>
  <?php else: foreach ($cards as $c): ?>
    <div class="bg-white rounded-xl shadow-sm p-5">
      <div class="text-lg font-bold mb-1"><?= htmlspecialchars($c['tenlop']) ?></div>
      <a class="text-blue-600 font-semibold" href="diemso.php?lop_id=<?= (int)$c['lop_id'] ?>&monhoc_id=<?= (int)$c['mon_id'] ?>">
        <?= htmlspecialchars($c['tenmon']) ?>
      </a>
      <ul class="mt-3 text-sm text-slate-700 space-y-1">
        <li>👤 GV: <?= htmlspecialchars($c['tengv']) ?></li>
        <li>👥 <?= (int)$c['siso'] ?> sinh viên</li>
        <li>🏫 Phòng <?= htmlspecialchars($c['phong']) ?></li>
        <li>📅 Thứ <?= htmlspecialchars(preg_replace('/[^0-9]/','',$c['thu']) ?: $c['thu']) ?>,
            <?= tiet2range((int)$c['tietbatdau'], (int)$c['sotiet']) ?>
        </li>
      </ul>
      <div class="flex gap-3 mt-4">
        <a class="px-4 py-2 rounded-lg bg-blue-600 text-white" href="sinhvien.php?lop_id=<?= (int)$c['lop_id'] ?>">Xem chi tiết</a>
        <a class="px-4 py-2 rounded-lg bg-slate-100" href="diemdanh.php?lop_id=<?= (int)$c['lop_id'] ?>">Điểm danh</a>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
