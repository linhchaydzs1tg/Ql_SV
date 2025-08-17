<?php
session_start();

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'student');
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thêm người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $email = $_POST['email'];
    $matkhau = $_POST['matkhau']; // Lưu plain-text (bạn có thể mã hóa bằng password_hash)
    $vaitro = $_POST['vaitro'];

    $stmt = $conn->prepare("INSERT INTO nguoidung (email, matkhau, vaitro) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $matkhau, $vaitro);
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Sửa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $matkhau = $_POST['matkhau'];
    $vaitro = $_POST['vaitro'];

    $stmt = $conn->prepare("UPDATE nguoidung SET email=?, matkhau=?, vaitro=? WHERE id=?");
    $stmt->bind_param("sssi", $email, $matkhau, $vaitro, $id);
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Xóa người dùng
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM nguoidung WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Lấy danh sách người dùng
$userQuery = "SELECT * FROM nguoidung WHERE vaitro IN ('giaovien', 'sinhvien')";
$userResult = $conn->query($userQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Quản Lý Người Dùng</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #eef2f7; margin: 0; padding: 20px; }
        .container { max-width: 950px; margin: auto; background: white; border-radius: 8px; 
                     box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 25px; }
        h1 { text-align: center; color: #2c3e50; }
        h2 { margin-top: 30px; color: #34495e; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #f7f7f7; color: #333; }
        tr:nth-child(even) { background: #fafafa; }
        .btn { padding: 6px 12px; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration:none; }
        .btn-primary { background-color: #007bff; }
        .btn-danger { background-color: #dc3545; }
        .btn-edit { background-color: #28a745; }
        .error { color: #d9534f; text-align:center; }
        .form-group { margin-bottom: 12px; }
        input[type="text"], input[type="password"], select {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
        }
        form { margin-top: 15px; }
        .actions { display: flex; gap: 5px; justify-content: center; }
    </style>
</head>
<body>
<div class="container">
    <h1>Quản Lý Người Dùng</h1>

    <h2>➕ Thêm Người Dùng</h2>
    <form method="POST" action="">
        <div class="form-group">
            <input type="text" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="text" name="matkhau" placeholder="Mật khẩu" required>
        </div>
        <div class="form-group">
            <select name="vaitro" required>
                <option value="">-- Chọn Vai Trò --</option>
                <option value="giaovien">Giáo Viên</option>
                <option value="sinhvien">Sinh Viên</option>
            </select>
        </div>
        <button type="submit" name="add_user" class="btn btn-primary">Thêm Người Dùng</button>
    </form>

    <h2>📋 Danh Sách Người Dùng</h2>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Mật khẩu</th>
            <th>Vai Trò</th>
            <th>Thao Tác</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($userResult && $userResult->num_rows > 0): ?>
            <?php while ($user = $userResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $user['id']; ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td><?= htmlspecialchars($user['matkhau']); ?></td>
                    <td><?= htmlspecialchars($user['vaitro']); ?></td>
                    <td class="actions">
                        <!-- Sửa -->
                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?= $user['id']; ?>">
                            <input type="hidden" name="email" value="<?= $user['email']; ?>">
                            <input type="hidden" name="matkhau" value="<?= $user['matkhau']; ?>">
                            <input type="hidden" name="vaitro" value="<?= $user['vaitro']; ?>">
                            <button type="submit" name="edit_user_show" class="btn btn-edit">Sửa</button>
                        </form>
                        <!-- Xóa -->
                        <a href="?delete=<?= $user['id']; ?>" class="btn btn-danger"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" class="error">Không có người dùng nào.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if (isset($_POST['edit_user_show'])): ?>
        <h2>✏️ Sửa Người Dùng</h2>
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?= $_POST['id']; ?>">
            <div class="form-group">
                <input type="text" name="email" value="<?= $_POST['email']; ?>" required>
            </div>
            <div class="form-group">
                <input type="text" name="matkhau" value="<?= $_POST['matkhau']; ?>" required>
            </div>
            <div class="form-group">
                <select name="vaitro" required>
                    <option value="giaovien" <?= ($_POST['vaitro']=='giaovien')?'selected':''; ?>>Giáo Viên</option>
                    <option value="sinhvien" <?= ($_POST['vaitro']=='sinhvien')?'selected':''; ?>>Sinh Viên</option>
                </select>
            </div>
            <button type="submit" name="edit_user" class="btn btn-edit">Cập Nhật</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
