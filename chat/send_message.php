<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/db.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'NOT_AUTH']);
    exit;
}

$sender_id   = (int)$_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$message     = trim($_POST['message'] ?? '');

if ($receiver_id <= 0 || $message === '') {
    echo json_encode(['ok'=>false,'error'=>'INVALID_INPUT']);
    exit;
}

/* Tạo bảng nếu chưa có */
$conn->query("CREATE TABLE IF NOT EXISTS messages(
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param('iis', $sender_id, $receiver_id, $message);
$stmt->execute();
$stmt->close();

echo json_encode(['ok'=>true]);
