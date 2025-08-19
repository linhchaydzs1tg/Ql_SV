<?php
require_once __DIR__ . '/_auth.php';
$active = 'students'; $title = 'Danh sách sinh viên';

// Lấy danh sách lớp cho filter
$lopList = $mysqli->query("SELECT id, tenlop FROM lop ORDER BY tenlop")->fetch_all(MYSQLI_ASSOC);

// Nhận filter
$q = trim($_GET['q'] ?? '');
$lop_id = (int)($_GET['lop_id'] ?? 0);

// Truy vấn sinh viên (4 nhánh gọn để tránh phức tạp bind động)
if ($q !== '' && $lop_id > 0) {
    $kw = "%$q%";
    $st = $mysqli->prepare("
        SELECT sv.mssv, sv.hoten, sv.email, sv.sodienthoai, l.tenlop
        FROM sinhvien sv LEFT JOIN lop l ON l.id = sv.lop_id
        WHERE sv.lop_id = ? AND (sv.hoten LIKE ? OR sv.mssv LIKE ?)
        ORDER BY sv.hoten
    ");
    $st->bind_param("iss", $lop_id, $kw, $kw);
} elseif ($q !== '') {
    $kw = "%$q%";
    $st = $mysqli->prepare("
        SELECT sv.mssv, sv.hoten, sv.email, sv.sodienthoai, l.tenlop
        FROM sinhvien sv LEFT JOIN lop l ON l.id = sv.lop_id
        WHERE sv.hoten LIKE ? OR sv.mssv LIKE ?
        ORDER BY sv.hoten
    ");
    $st->bind_param("ss", $kw, $kw);
} elseif ($lop_id > 0) {
    $st = $mysqli->prepare("
        SELECT sv.mssv, sv.hoten, sv.email, sv.sodienthoai, l.tenlop
        FROM sinhvien sv LEFT JOIN lop l ON l.id = sv.lop_id
        WHERE sv.lop_id = ?
        ORDER BY sv.hoten
    ");
    $st->bind_param("i", $lop_id);
} else {
    $st = $mysqli->prepare("
        SELECT sv.mssv, sv.hoten, sv.email, sv.sodienthoai, l.tenlop
        FROM sinhvien sv LEFT JOIN lop l ON l.id = sv.lop_id
        ORDER BY sv.hoten
    ");
}
$st->execute();
$svList = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

include __DIR__ . '/_layout_top.php';
?>
<h1 class="text-2xl font-extrabold">Danh sách sinh viên</h1>
<p class="text-sm text-slate-500 mt-1 mb-5">Quản lý thông tin sinh viên trong trường</p>

<form class="flex flex-col sm:flex-row gap-3 mb-4" method="get">
  <div class="flex-1 relative">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Tìm kiếm theo tên hoặc mã sinh viên..."
           class="w-full bg-white border rounded-lg h-10 pl-10 pr-3" />
    <span class="absolute left-3 top-1/2 -translate-y-1/2">🔎</span>
  </div>
  <select name="lop_id" class="h-10 bg-white border rounded-lg px-3">
    <option value="0">Tất cả lớp</option>
    <?php foreach ($lopList as $l): ?>
      <option value="<?= (int)$l['id'] ?>" <?= $lop_id===(int)$l['id']?'selected':'' ?>>
        <?= htmlspecialchars($l['tenlop']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button class="h-10 px-4 rounded-lg bg-blue-600 text-white">Lọc</button>
  <a href="#" class="ml-auto h-10 px-4 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-2">➕ Thêm sinh viên</a>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
  <table class="min-w-full text-sm">
    <thead class="bg-slate-50 text-slate-600">
      <tr>
        <th class="text-left py-3 px-4">Sinh viên</th>
        <th class="text-left py-3 px-4">Mã SV</th>
        <th class="text-left py-3 px-4">Lớp</th>
        <th class="text-left py-3 px-4">E-mail</th>
        <th class="text-left py-3 px-4">Số điện thoại</th>
        <th class="text-center py-3 px-4">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$svList): ?>
        <tr><td colspan="6" class="py-6 text-center text-slate-500">Không có sinh viên phù hợp.</td></tr>
      <?php else: foreach ($svList as $sv): ?>
        <tr class="border-t">
          <td class="py-3 px-4 font-medium"><?= htmlspecialchars($sv['hoten']) ?></td>
          <td class="py-3 px-4"><?= htmlspecialchars($sv['mssv']) ?></td>
          <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-full bg-slate-100"><?= htmlspecialchars($sv['tenlop'] ?? '—') ?></span></td>
          <td class="py-3 px-4"><?= htmlspecialchars($sv['email'] ?? '—') ?></td>
          <td class="py-3 px-4"><?= htmlspecialchars($sv['sodienthoai'] ?? '—') ?></td>
          <td class="py-3 px-4 text-center">
            <a class="mx-1 text-blue-600" href="#">👁️</a>
            <a class="mx-1 text-emerald-600" href="#">✏️</a>
            <a class="mx-1 text-rose-600" href="#">🗑️</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
