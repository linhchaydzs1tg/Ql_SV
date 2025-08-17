<?php
session_start();
require_once '../config/db.php';

$err = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $matkhau = trim($_POST['password']);

    if (empty($email) || empty($matkhau)) {
        $err = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM nguoidung WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 👉 Không dùng password_verify nữa – so sánh trực tiếp
            if ($matkhau === $user['matkhau']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['ho_ten'] = $user['ho_ten'] ?? 'Không rõ';
                $_SESSION['email'] = $user['email'];
                $_SESSION['vai_tro'] = $user['vaitro'];

                // Chuyển hướng theo vai trò
                switch ($user['vaitro']) {
                    case 'admin':
                        header("Location: ../admin/dashboard.php");
                        break;
                    case 'giaovien':
                        header("Location: ../giangvien/dashboard_gv.php");
                        break;
                    case 'sinhvien':
                        header("Location: ../sinhvien/view_sv.php");
                        break;
                    default:
                        $err = "Tài khoản không có vai trò hợp lệ.";
                        break;
                }
                exit();
            } else {
                $err = "Mật khẩu không đúng.";
            }
        } else {
            $err = "Tài khoản không tồn tại.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=school" />
    <style>
        body {
            background-color: #e0e0e0;
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            width: 400px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .login-container span {
            font-size: 48px; /* Kích thước biểu tượng */
            color: #2196F3; /* Màu sắc biểu tượng */
            margin-bottom: 20px;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .login-container input[type="submit"] {
            background-color: #2196F3;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-container input[type="submit"]:hover {
            background-color: #1976D2;
        }

        .error {
            color: red;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <span class="material-symbols-outlined">school</span> <!-- Biểu tượng thay thế cho logo -->
        
        <h2>Đăng nhập hệ thống</h2>

        <?php if (!empty($err)) echo "<div class='error'>$err</div>"; ?>

        <form method="POST" action="">
            <input type="text" name="email" placeholder="Email hoặc MSSV" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="submit" value="Đăng nhập">
        </form>
    </div>
</body>
</html>