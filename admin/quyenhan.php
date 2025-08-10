<?php
session_start(); // Khởi tạo phiên

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'student'); // Thay đổi thông tin đăng nhập nếu cần

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thêm người dùng mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $email = $_POST['email'];
    $matkhau = password_hash($_POST['matkhau'], PASSWORD_DEFAULT); // Băm mật khẩu
    $vaitro = $_POST['vaitro'];

    $stmt = $conn->prepare("INSERT INTO nguoidung (email, matkhau, vaitro) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $matkhau, $vaitro);
    $stmt->execute();
}

// Xóa người dùng
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM nguoidung WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Lấy danh sách người dùng
$userQuery = "SELECT * FROM nguoidung WHERE vaitro IN ('giaovien', 'sinhvien')";
$userResult = $conn->query($userQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Quản Lý Người Dùng</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 5px 10px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .error {
            color: #d9534f;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quản Lý Người Dùng</h1>

        <h2>Thêm Người Dùng</h2>
        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="matkhau" placeholder="Mật khẩu" required>
            </div>
            <div class="form-group">
                <select name="vaitro" required>
                    <option value="giaovien">Giáo Viên</option>
                    <option value="sinhvien">Sinh Viên</option>
                </select>
            </div>
            <button type="submit" name="add_user" class="btn">Thêm Người Dùng</button>
        </form>

        <h2>Danh Sách Người Dùng</h2>
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Vai Trò</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($userResult && $userResult->num_rows > 0): ?>
                    <?php while ($user = $userResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['vaitro']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="error">Không có người dùng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>