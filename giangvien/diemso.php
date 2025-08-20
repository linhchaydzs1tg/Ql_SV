<?php
require_once __DIR__ . '/_auth.php';
$active = 'scores'; $title = 'Quản lý điểm';

// Lấy cặp (lớp, môn) từ lichhoc của GV
$cap = [];
if ($st = $mysqli->prepare("
    SELECT DISTINCT l.id AS lop_id, l.tenlop, m.id AS monhoc_id, m.tenmon
    FROM lichhoc lh
    JOIN lop l ON l.id = lh.lop_id
    JOIN monhoc m ON m.id = lh.monhoc_id
    WHERE lh.giaovien_id = ?
    ORDER BY l.tenlop, m.tenmon
")) {
    $st->bind_param("i", $giaovien_id);
    $st->execute();
    $cap = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
}

// Mặc định chọn cặp đầu tiên
$lop_id = (int)($_GET['lop_id'] ?? 0);
$monhoc_id = (int)($_GET['monhoc_id'] ?? 0);
if ((!$lop_id || !$monhoc_id) && $cap) {
    $lop_id = (int)$cap[0]['lop_id'];
    $monhoc_id = (int)$cap[0]['monhoc_id'];
}

// Handle save (UPSERT)
$msg = "";
if (isset($_POST['save_score'])) {
    $mssv = $_POST['mssv'] ?? '';
    $monhoc_id_post = (int)($_POST['monhoc_id'] ?? 0);
    $lop_post = (int)($_POST['lop_id'] ?? 0);
    $diem_cc  = is_numeric($_POST['diem_cc'] ?? '') ? (float)$_POST['diem_cc'] : null;
    $diem_kt  = is_numeric($_POST['diem_kt'] ?? '') ? (float)$_POST['diem_kt'] : null;
    $diem_thi = is_numeric($_POST['diem_thi'] ?? '') ? (float)$_POST['diem_thi'] : null;

    if ($mssv !== '' && $monhoc_id_post > 0) {
        if ($st = $mysqli->prepare("SELECT id FROM diem WHERE mssv=? AND monhoc_id=? LIMIT 1")) {
            $st->bind_param("si", $mssv, $monhoc_id_post);
            $st->execute();
            if ($row = $st->get_result()->fetch_assoc()) {
                $id = (int)$row['id']; $st->close();
                if ($up = $mysqli->prepare("UPDATE diem SET diem_cc=?, diem_kt=?, diem_thi=? WHERE id=?")) {
                    $up->bind_param("dddi", $diem_cc, $diem_kt, $diem_thi, $id);
                    $up->execute(); $up->close();
                    $msg = "Cập nhật điểm cho $mssv thành công!";
                }
            } else {
                $st->close();
                if ($ins = $mysqli->prepare("INSERT INTO diem (mssv, monhoc_id, diem_cc, diem_kt, diem_thi) VALUES (?, ?, ?, ?, ?)")) {
                    $ins->bind_param("siddd", $mssv, $monhoc_id_post, $diem_cc, $diem_kt, $diem_thi);
                    $ins->execute(); $ins->close();
                    $msg = "Nhập điểm cho $mssv thành công!";
                }
            }
        }
        // giữ filter
        $lop_id = $lop_post ?: $lop_id;
        $monhoc_id = $monhoc_id_post ?: $monhoc_id;
    }
}

// Danh sách SV của lớp đã chọn + điểm môn đã chọn
$svList = [];
if ($lop_id && $monhoc_id) {
    if ($st = $mysqli->prepare("
        SELECT sv.mssv, sv.hoten, d.diem_cc, d.diem_kt, d.diem_thi
        FROM sinhvien sv
        LEFT JOIN diem d ON d.mssv = sv.mssv AND d.monhoc_id = ?
        WHERE sv.lop_id = ?
        ORDER BY sv.hoten
    ")) {
        $st->bind_param("ii", $monhoc_id, $lop_id);
        $st->execute();
        $svList = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st->close();
    }
}

include __DIR__ . '/_layout_top.php';
?>
<h1 class="text-2xl font-extrabold">Quản lý điểm</h1>
<p class="text-sm text-slate-500 mt-1 mb-5">Nhập/sửa điểm cho lớp và môn phụ trách</p>

<?php if ($msg): ?>
  <div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-700"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm p-5">
  <form class="flex flex-col sm:flex-row gap-3 mb-4" method="get">
    <select name="lop_id" class="h-10 bg-white border rounded-lg px-3" onchange="this.form.submit()">
      <?php
      $lopDaIn = [];
      foreach ($cap as $c) $lopDaIn[$c['lop_id']] = $c['tenlop'];
      foreach ($lopDaIn as $id=>$ten): ?>
        <option value="<?= (int)$id ?>" <?= $lop_id===(int)$id?'selected':'' ?>>Lớp: <?= htmlspecialchars($ten) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="monhoc_id" class="h-10 bg-white border rounded-lg px-3" onchange="this.form.submit()">
      <?php foreach ($cap as $c): if ((int)$c['lop_id'] !== $lop_id) continue; ?>
        <option value="<?= (int)$c['monhoc_id'] ?>" <?= $monhoc_id===(int)$c['monhoc_id']?'selected':'' ?>>
          Môn: <?= htmlspecialchars($c['tenmon']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <noscript><button class="h-10 px-4 rounded-lg bg-blue-600 text-white">Lọc</button></noscript>
  </form>

  <?php if (!$cap): ?>
    <div class="text-slate-600">Chưa có lịch dạy cho giáo viên này nên chưa chọn được lớp/môn.</div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left py-3 px-4">MSSV</th>
            <th class="text-left py-3 px-4">Sinh viên</th>
            <th class="text-center py-3 px-4">Điểm CC</th>
            <th class="text-center py-3 px-4">Điểm KT</th>
            <th class="text-center py-3 px-4">Điểm Thi</th>
            <th class="text-center py-3 px-4">Lưu</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$svList): ?>
          <tr><td colspan="6" class="py-6 text-center text-slate-500">Lớp này chưa có sinh viên.</td></tr>
        <?php else: foreach ($svList as $sv): ?>
          <tr class="border-t">
            <td class="py-3 px-4"><span class="px-2 py-0.5 rounded bg-slate-100"><?= htmlspecialchars($sv['mssv']) ?></span></td>
            <td class="py-3 px-4"><?= htmlspecialchars($sv['hoten']) ?></td>
            <td class="py-3 px-4 text-center">
              <form method="post" class="inline-flex items-center gap-2">
                <input type="hidden" name="mssv" value="<?= htmlspecialchars($sv['mssv']) ?>">
                <input type="hidden" name="monhoc_id" value="<?= (int)$monhoc_id ?>">
                <input type="hidden" name="lop_id" value="<?= (int)$lop_id ?>">
                <input type="number" step="0.1" min="0" max="10" name="diem_cc" class="w-20 border rounded h-9 px-2"
                       value="<?= $sv['diem_cc']!==null ? $sv['diem_cc'] : '' ?>">
            </td>
            <td class="py-3 px-4 text-center">
                <input type="number" step="0.1" min="0" max="10" name="diem_kt" class="w-20 border rounded h-9 px-2"
                       value="<?= $sv['diem_kt']!==null ? $sv['diem_kt'] : '' ?>">
            </td>
            <td class="py-3 px-4 text-center">
                <input type="number" step="0.1" min="0" max="10" name="diem_thi" class="w-20 border rounded h-9 px-2"
                       value="<?= $sv['diem_thi']!==null ? $sv['diem_thi'] : '' ?>">
            </td>
            <td class="py-3 px-4 text-center">
                <button name="save_score" class="px-3 py-1.5 bg-emerald-600 text-white rounded">Lưu</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
