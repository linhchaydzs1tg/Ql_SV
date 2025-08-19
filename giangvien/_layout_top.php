<?php
// giangvien/_layout_top.php
/* Yêu cầu: include _auth.php trước, đặt $active = 'home'|'students'|'classes'|'scores'|'attendance' */

function active_class($name, $active) {
    return $name === $active ? 'text-blue-600 font-semibold' : 'text-slate-600 hover:text-slate-900';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title><?= isset($title) ? htmlspecialchars($title) : 'QuanLySV' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f7f9fc] text-slate-900">
<header class="bg-white border-b">
  <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="dashboard_gv.php" class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold">Q</a>
      <span class="font-semibold">QuanLySV</span>
      <nav class="ml-6 hidden md:flex items-center gap-6 text-sm">
        <a class="<?= active_class('home', $active ?? '') ?>" href="dashboard_gv.php">Trang chủ</a>
        <a class="<?= active_class('students', $active ?? '') ?>" href="sinhvien.php">Danh sách sinh viên</a>
        <a class="<?= active_class('classes', $active ?? '') ?>" href="lophoc.php">Lớp học</a>
        <a class="<?= active_class('scores', $active ?? '') ?>" href="diemso.php">Điểm số</a>
        <a class="<?= active_class('attendance', $active ?? '') ?>" href="diemdanh.php">Điểm danh</a>
      </nav>
    </div>
    <div class="flex items-center gap-3">
      <div class="hidden sm:block text-sm text-slate-500">
        <div class="font-medium text-slate-700"><?= htmlspecialchars($gv['hoten']) ?></div>
        <div><?= htmlspecialchars($gv['email']) ?></div>
      </div>
      <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center">👩‍🏫</div>
      <a href="/QL_SV/auth/logout.php" class="hidden sm:inline-block text-sm px-3 py-1.5 rounded-md border text-slate-700 hover:bg-slate-50">Đăng xuất</a>
    </div>
  </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-6">
