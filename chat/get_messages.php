<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/db.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'NOT_AUTH']);
    exit;
}

$me          = (int)$_SESSION['user_id'];
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

if ($receiver_id <= 0) {
    echo json_encode(['ok'=>false,'error'=>'INVALID_RECEIVER']);
    exit;
}

$stmt = $conn->prepare("
    SELECT sender_id, message, DATE_FORMAT(created_at,'%Y-%m-%d %H:%i:%s') AS ts
    FROM messages
    WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
    ORDER BY created_at ASC
");
$stmt->bind_param('iiii', $me, $receiver_id, $receiver_id, $me);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
    $out[] = [
        'from_id' => (int)$r['sender_id'],
        'message' => $r['message'],
        'time'    => $r['ts'],
        'is_me'   => ((int)$r['sender_id'] === $me)
    ];
}
$stmt->close();

echo json_encode(['ok'=>true,'messages'=>$out], JSON_UNESCAPED_UNICODE);
