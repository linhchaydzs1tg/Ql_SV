<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$currentUser = (int)$_SESSION['user_id'];

// Lấy danh sách người dùng (trừ chính mình)
$stmt = $conn->prepare("SELECT id, email, vaitro FROM nguoidung WHERE id != ? ORDER BY vaitro, email");
if (!$stmt) { die("Lỗi prepare: " . $conn->error); }
$stmt->bind_param("i", $currentUser);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chọn người để chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    :root {
        --primary: #2563eb; 
        --primary-dark: #1e40af; 
        --bg: #0ea5e9; 
        --card: #ffffff;
        --muted: #64748b; 
        --shadow: 0 10px 25px rgba(2, 6, 23, .15);
    }
    * { box-sizing: border-box; }
    body {
        margin: 0; 
        font-family: Inter, system-ui, Segoe UI, Roboto, Arial, sans-serif;
        min-height: 100vh; 
        background: radial-gradient(80% 120% at 50% 0%, #e0f2fe 0%, #bfdbfe 30%, #93c5fd 60%, #60a5fa 100%);
    }
    .wrapper {
        width: 300px; 
        background: linear-gradient(180deg, #ffffffcc, #fffffff2);
        border-radius: 16px; 
        box-shadow: var(--shadow); 
        overflow: hidden;
        border: 1px solid #e5e7eb;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    .head {
        padding: 10px 14px; 
        background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);
        color: #fff; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
    }
    .head .logo {
        width: 32px; 
        height: 32px; 
        display: grid; 
        place-items: center;
        background: #ffffff22; 
        border: 1px solid #ffffff44; 
        border-radius: 10px; 
        font-weight: 800;
    }
    .head h1 {
        font-size: 16px; 
        margin: 0; 
        letter-spacing: .2px;
    }
    .head .meta {
        margin-left: auto; 
        font-size: 12px; 
        opacity: .9;
    }
    .toggle-btn {
        margin-left: 8px;
        background: transparent;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        transition: transform .2s;
    }
    .toggle-btn:hover {
        transform: scale(1.2);
    }
    .content {
        max-height: 600px;
        overflow: hidden;
        transition: max-height .3s ease, opacity .3s ease;
    }
    .content.hidden {
        max-height: 0;
        opacity: 0;
        padding: 0;
    }
    .toolbar {
        display: flex; 
        gap: 8px; 
        padding: 10px; 
        background: #f8fafc; 
        border-bottom: 1px solid #e5e7eb;
    }
    .search {
        flex: 1; 
        position: relative;
    }
    .search input {
        width: 100%; 
        padding: 10px 30px 10px 36px; 
        border: 1px solid #e2e8f0; 
        border-radius: 10px; 
        outline: none; 
        transition: .2s; 
        background: #fff;
    }
    .search input:focus {
        border-color: #93c5fd; 
        box-shadow: 0 0 0 4px #dbeafe;
    }
    .chips {
        padding: 10px; 
        display: flex; 
        gap: 6px; 
        flex-wrap: wrap;
    }
    .chip {
        background: #eff6ff; 
        color: #1d4ed8; 
        padding: 6px 10px; 
        border-radius: 999px;
        font-size: 12px; 
        border: 1px solid #dbeafe; 
        cursor: pointer; 
        user-select: none;
    }
    .grid {
        padding: 10px; 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
        gap: 10px; 
        background: #ffffffaa;
    }
    .bubble {
        display: flex; 
        align-items: center; 
        gap: 10px; 
        padding: 10px; 
        background: var(--card);
        border-radius: 14px; 
        border: 1px solid #e5e7eb; 
        text-decoration: none; 
        color: #0f172a;
        transition: .2s; 
        box-shadow: 0 1px 0 rgba(2, 6, 23, .02);
    }
    .bubble:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 10px 20px rgba(2, 6, 23, .08); 
        border-color: #dbeafe; 
    }
    .avatar {
        width: 32px; 
        height: 32px; 
        border-radius: 50%; 
        display: grid; 
        place-items: center;
        background: #dbeafe; 
        color: #1d4ed8; 
        font-weight: 700; 
        border: 1px solid #bfdbfe;
    }
    .info {
        display: flex; 
        flex-direction: column; 
        gap: 2px; 
    }
    .email {
        font-size: 13px; 
        font-weight: 600;
    }
    .role {
        font-size: 10px; 
        color: #475569; 
        background: #f1f5f9; 
        padding: 4px 6px; 
        border-radius: 999px; 
        width: max-content;
        border: 1px solid #e2e8f0;
    }
    .empty { 
        padding: 20px; 
        text-align: center; 
        color: #475569; 
    }
    @media (max-width: 540px) { 
        .head h1 { display: none; } 
    }
</style>
</head>
<body>
<div class="wrapper" id="chat-widget">
    <div class="head">
        <div class="logo">💬</div>
        <h1>Chọn người để chat</h1>
        <div class="meta">Đăng nhập: 
            <strong><?php echo htmlspecialchars($_SESSION['email'] ?? ("User #".$currentUser)); ?></strong>
        </div>
        <button class="toggle-btn" id="toggleBtn">−</button>
    </div>

    <div class="content" id="chatContent">
        <div class="toolbar">
            <div class="search">
                <span class="ico">🔎</span>
                <input id="search" type="text" placeholder="Tìm theo email hoặc vai trò (admin, giaovien, sinhvien)...">
            </div>
        </div>

        <div class="chips">
            <button class="chip" data-role="all">Tất cả</button>
            <button class="chip" data-role="admin">Admin</button>
            <button class="chip" data-role="giaovien">Giáo viên</button>
            <button class="chip" data-role="sinhvien">Sinh viên</button>
        </div>

        <div id="list" class="grid">
            <?php if (empty($users)): ?>
                <div class="empty">Không có người dùng nào khác để chat.</div>
            <?php else: ?>
                <?php foreach ($users as $u): 
                    $initial = strtoupper(substr($u['email'], 0, 1));
                ?>
                    <a class="bubble" data-email="<?php echo htmlspecialchars($u['email']); ?>" 
                       data-role="<?php echo htmlspecialchars($u['vaitro']); ?>"
                       href="../chat/chat_box.php?to=<?php echo (int)$u['id']; ?>">
                        <div class="avatar"><?php echo $initial; ?></div>
                        <div class="info">
                            <div class="email"><?php echo htmlspecialchars($u['email']); ?></div>
                            <div class="role"><?php echo htmlspecialchars($u['vaitro']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const search = document.getElementById('search');
const chips  = document.querySelectorAll('.chip');
const items  = [...document.querySelectorAll('.bubble')];
let currentRole = 'all';

function applyFilter() {
    const q = (search.value || '').toLowerCase().trim();
    items.forEach(el => {
        const email = el.dataset.email.toLowerCase();
        const role  = el.dataset.role.toLowerCase();
        const matchQ = !q || email.includes(q) || role.includes(q);
        const matchR = (currentRole === 'all') || (role === currentRole);
        el.style.display = (matchQ && matchR) ? '' : 'none';
    });
}
search.addEventListener('input', applyFilter);
chips.forEach(c => c.addEventListener('click', () => {
    currentRole = c.dataset.role;
    applyFilter();
}));

// Toggle thu gọn / mở rộng
const toggleBtn   = document.getElementById('toggleBtn');
const chatContent = document.getElementById('chatContent');

toggleBtn.addEventListener('click', () => {
    chatContent.classList.toggle('hidden');
    toggleBtn.textContent = chatContent.classList.contains('hidden') ? '+' : '−';
});
</script>
</body>
</html>
