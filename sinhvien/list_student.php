<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 'sinhvien') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

// Lấy email sinh viên từ session
$email = $_SESSION['email'];

$sql = "SELECT * FROM sinhvien WHERE email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi prepare: " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$sv = $stmt->get_result()->fetch_assoc();

if (!$sv) {
    die("Không tìm thấy sinh viên với Email: " . htmlspecialchars($email));
}

$tin_chi = 120;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin sinh viên</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            margin: 0;
            padding: 40px;
            color: #333;
        }
        /* --- Navbar --- */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #fff;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            cursor: pointer;
        }
        /* --- Dropdown --- */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 55px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            width: 260px;
            display: none;
            overflow: hidden;
            animation: fadeIn 0.25s ease;
        }
        .dropdown-menu.active { display: block; }
        .user-header {
            text-align: center;
            padding: 20px;
            background: #f7f7f7;
        }
        .user-header img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .user-header h4 { margin: 5px 0; font-size: 16px; font-weight: 600; }
        .user-header p { font-size: 13px; color: #666; margin: 0; }
        .user-links { display: flex; flex-direction: column; }
        .user-links a {
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s;
        }
        .user-links a:hover { background: #f0f0f0; }
        .logout { color: #e63946; font-weight: 600; }
        @keyframes fadeIn { from{opacity:0; transform: translateY(-10px);} to{opacity:1; transform:translateY(0);} }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="dropdown">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Avatar" class="avatar" onclick="toggleMenu()">
            <div class="dropdown-menu" id="userMenu">
                <div class="user-header">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Avatar">
                    <h4><?= htmlspecialchars($sv['hoten']) ?></h4>
                    <p><?= htmlspecialchars($sv['email']) ?></p>
                    <small>Sinh viên</small>
                </div>
                <div class="user-links">
                    <a href="profile.php"><i class="fa-solid fa-user"></i> Thông tin cá nhân</a>
                    <a href="../auth/logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Thông tin sinh viên -->
    <div class="container" style="margin-top:100px;">
        <div class="header" style="text-align:center;color:#fff;">
            <h2>📘 Thông tin sinh viên</h2>
        </div>
        <div class="info" style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;padding:30px;background:#fff;border-radius:16px;">
            <div><b>MSSV:</b> <?= htmlspecialchars($sv['mssv']) ?></div>
            <div><b>Họ tên:</b> <?= htmlspecialchars($sv['hoten']) ?></div>
            <div><b>Email:</b> <?= htmlspecialchars($sv['email']) ?></div>
            <div><b>Khoa:</b> <?= htmlspecialchars($sv['khoa']) ?></div>
            <div><b>Ngày sinh:</b> <?= htmlspecialchars($sv['ngaysinh']) ?></div>
            <div><b>Giới tính:</b> <?= htmlspecialchars($sv['gioitinh']) ?></div>
            <div><b>Địa chỉ:</b> <?= htmlspecialchars($sv['diachi']) ?></div>
            <div><b>Số điện thoại:</b> <?= htmlspecialchars($sv['sodienthoai']) ?></div>
            <div><b>Điểm TB:</b> <?= htmlspecialchars($sv['diem_tb']) ?></div>
            <div><b>Trạng thái:</b> <?= htmlspecialchars($sv['trang_thai']) ?></div>
            <div><b>Số tín chỉ:</b> <?= $tin_chi ?></div>
        </div>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById('userMenu').classList.toggle('active');
        }
        // Click ngoài thì đóng menu
        window.addEventListener('click', function(e){
            const menu = document.getElementById('userMenu');
            if (!e.target.closest('.dropdown')) {
                menu.classList.remove('active');
            }
        });
     </script>
        <?php include '../chat/chat.php'; ?>
</body>

</body>
</html>
