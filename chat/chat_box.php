<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$from_id = (int)($_SESSION['user_id']);
$to_id   = isset($_GET['to']) ? (int)$_GET['to'] : 0;
if ($to_id <= 0) { die("Người nhận không hợp lệ."); }

// Tạo bảng messages nếu chưa có
$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

// Xử lý AJAX
if (isset($_GET['ajax'])) {
    header("Content-Type: application/json; charset=UTF-8");

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
        $msg = trim($_POST['message']);
        if ($msg !== "") {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $from_id, $to_id, $msg);
            $stmt->execute();
            $stmt->close();
        }
    }

    $messages = [];
    $stmt = $conn->prepare("
        SELECT sender_id, message, DATE_FORMAT(created_at, '%H:%i') AS ts
        FROM messages
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at ASC
    ");
    $stmt->bind_param("iiii", $from_id, $to_id, $to_id, $from_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()){
        $messages[] = [
            "from_id" => (int)$row['sender_id'],
            "message" => $row['message'],
            "time"    => $row['ts'],
            "is_me"   => ((int)$row['sender_id'] === $from_id)
        ];
    }
    $stmt->close();

    echo json_encode(["ok"=>true, "messages"=>$messages]);
    exit();
}

// Lấy thông tin người nhận
$to_email = '';
$stmt = $conn->prepare("SELECT email FROM nguoidung WHERE id = ?");
$stmt->bind_param("i", $to_id);
$stmt->execute();
$stmt->bind_result($to_email);
$stmt->fetch();
$stmt->close();
if (!$to_email) { die("Người nhận không tồn tại."); }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chat với <?php echo htmlspecialchars($to_email); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f3f4f6;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.chat {
    width: min(450px, 96vw);
    height: min(92vh, 720px);
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.top {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: #2563eb;
    color: white;
    font-weight: 600;
}
.back {
    margin-right: 12px;
    text-decoration: none;
    color: white;
    font-size: 20px;
}
.who {
    flex-grow: 1;
    display: flex;
    align-items: center;
    gap: 10px;
}
.avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: #1e40af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #fff;
}
.meta {
    font-size: 12px;
    margin-left: auto;
    opacity: 0.85;
}

.msgs {
    flex-grow: 1;
    padding: 16px;
    overflow-y: auto;
    background: #f9fafb;
    display: flex;
    flex-direction: column;
}
.bubble {
    max-width: 75%;
    padding: 10px 14px;
    border-radius: 16px;
    margin: 6px 0;
    font-size: 14px;
    line-height: 1.4;
    position: relative;
}
.me {
    background: #2563eb;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}
.other {
    background: #e5e7eb;
    color: #111827;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}
.time {
    display: block;
    font-size: 11px;
    opacity: 0.7;
    margin-top: 2px;
    text-align: right;
}
.empty {
    text-align: center;
    color: #999;
    padding: 20px;
}

.input {
    display: flex;
    padding: 12px;
    background: #fff;
    border-top: 1px solid #e5e7eb;
}
.input input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 20px;
    margin-right: 10px;
    outline: none;
    font-size: 14px;
}
.input input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px #bfdbfe;
}
.input button {
    padding: 10px 18px;
    border: none;
    border-radius: 20px;
    background: #2563eb;
    color: white;
    font-size: 14px;
    cursor: pointer;
}
.input button:hover { background: #1d4ed8; }
</style>
</head>
<body>
<div class="chat">
    <div class="top">
        <a class="back" href="chat.php">⟵</a>
        <div class="who">
            <div class="avatar"><?php echo strtoupper(substr($to_email, 0, 1)); ?></div>
            <div>
                <div><?php echo htmlspecialchars($to_email); ?></div>
                <small>Trò chuyện riêng</small>
            </div>
        </div>
        <div class="meta">
            Bạn: <strong><?php echo htmlspecialchars($_SESSION['email'] ?? ("User #".$from_id)); ?></strong>
        </div>
    </div>

    <div id="msgs" class="msgs"><div class="empty">Đang tải...</div></div>

    <form id="sendForm" class="input" autocomplete="off">
        <input id="message" name="message" type="text" placeholder="Nhập tin nhắn..." required>
        <button type="submit">Gửi</button>
    </form>
</div>

<script>
const toId = <?php echo json_encode($to_id); ?>;
const msgsEl = document.getElementById('msgs');
const form = document.getElementById('sendForm');
const input = document.getElementById('message');

function scrollBottom(){ msgsEl.scrollTop = msgsEl.scrollHeight; }
function escapeHtml(text){
    return text.replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
}

async function loadMessages(){
    try {
        const res = await fetch(`chat_box.php?to=${toId}&ajax=1`, { cache: 'no-store' });
        const data = await res.json();
        msgsEl.innerHTML = '';
        if (!data.ok) return;
        if (data.messages.length === 0){
            msgsEl.innerHTML = `<div class="empty">💬 Chưa có tin nhắn nào</div>`;
        } else {
            data.messages.forEach(m=>{
                const div = document.createElement('div');
                div.className = `bubble ${m.is_me ? 'me':'other'}`;
                div.innerHTML = `${escapeHtml(m.message).replaceAll('\n','<br>')}<span class="time">${m.time}</span>`;
                msgsEl.appendChild(div);
            });
        }
        scrollBottom();
    } catch(e){ console.error(e); }
}
setInterval(loadMessages, 2000);
loadMessages();

form.addEventListener('submit', async e=>{
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    await fetch(`chat_box.php?to=${toId}&ajax=1`, {
        method: 'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({message:text})
    });
    input.value = '';
    await loadMessages();
});
</script>
</body>
</html>
