<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) header('Location: login.php');

$user_id = getUserId();
$muted = $conn->query("SELECT muted_until FROM users WHERE id=$user_id")->fetch_assoc()['muted_until'] > date('Y-m-d H:i:s');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$muted) {
    if (isset($_POST['message'])) {
        $message = $conn->real_escape_string($_POST['message']);
        $conn->query("INSERT INTO global_chats (user_id, message) VALUES ($user_id, '$message')");
    }
    if (isset($_FILES['media'])) {
        $file = $_FILES['media'];
        $type = strpos($file['type'], 'image') === 0 ? 'image' : (strpos($file['type'], 'video') === 0 ? 'video' : 'file');
        $path = 'uploads/' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $path);
        $chat_id = $conn->insert_id; // From last message or new one
        $conn->query("INSERT INTO media (chat_type, chat_id, file_path, type) VALUES ('global', $chat_id, '$path', '$type')");
    }
}

// Fetch chats (AJAX endpoint if ?ajax=1)
if (isset($_GET['ajax'])) {
    $result = $conn->query("SELECT g.id, u.nick, g.message, g.timestamp, g.edited FROM global_chats g JOIN users u ON g.user_id = u.id ORDER BY g.timestamp DESC");
    while ($row = $result->fetch_assoc()) {
        echo "<div style='background: #333; padding: 10px; margin: 5px; border: 1px solid #555;'>";
        echo "<strong>{$row['nick']}</strong> ({$row['timestamp']}): {$row['message']}";
        if ($row['edited']) echo " (edited)";
        $media_result = $conn->query("SELECT * FROM media WHERE chat_type='global' AND chat_id={$row['id']}");
        while ($media = $media_result->fetch_assoc()) {
            if ($media['type'] == 'image') echo "<img src='{$media['file_path']}' style='max-width:200px;'>";
            elseif ($media['type'] == 'video') echo "<video src='{$media['file_path']}' controls style='max-width:200px;'></video>";
            else echo "<a href='{$media['file_path']}' download>Download File</a>";
            if (isAdmin()) echo " <a href='?delete_media={$media['id']}' style='color:red;'>Delete</a>";
        }
        if ($row['user_id'] == $user_id || isAdmin()) {
            echo " <a href='?edit={$row['id']}' style='color:yellow;'>Edit</a> <a href='?delete={$row['id']}' style='color:red;'>Delete</a>";
        }
        echo "</div>";
    }
    exit;
}

// Handle edit/delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM global_chats WHERE id=$id AND (user_id=$user_id OR " . (isAdmin() ? '1' : '0') . ")");
    header('Location: index.php');
}
if (isset($_GET['edit']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_GET['edit'];
    $message = $conn->real_escape_string($_POST['new_message']);
    $conn->query("UPDATE global_chats SET message='$message', edited=NOW() WHERE id=$id AND (user_id=$user_id OR " . (isAdmin() ? '1' : '0') . ")");
    header('Location: index.php');
}
if (isset($_GET['delete_media'])) {
    $id = (int)$_GET['delete_media'];
    $conn->query("DELETE FROM media WHERE id=$id");
    unlink($conn->query("SELECT file_path FROM media WHERE id=$id")->fetch_assoc()['file_path']);
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Goydazvon - Global Chat</title>
    <style>
        body { background: #000; color: #fff; font-family: Arial; }
        #chat { background: #222; padding: 20px; border: 1px solid #444; height: 400px; overflow-y: scroll; }
        form { margin-top: 20px; }
        input[type="text"], input[type="file"] { background: #333; color: #fff; border: 1px solid #555; padding: 10px; }
        button { background: #444; color: #fff; padding: 10px; border: none; }
        a { color: #fff; }
    </style>
    <script>
        function refreshChat() {
            fetch('index.php?ajax=1').then(response => response.text()).then(html => {
                document.getElementById('chat').innerHTML = html;
            });
        }
        setInterval(refreshChat, 5000);
        window.onload = refreshChat;
    </script>
</head>
<body>
    <h1>Goydazvon Global Chat</h1>
    <a href="profile.php">Profile</a> | <a href="private.php">Private Messages</a> | <?php if (isAdmin()) echo '<a href="admin.php">Admin Panel</a>'; ?> | <a href="logout.php">Logout</a>
    <div id="chat"></div>
    <?php if (!$muted): ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="message" placeholder="Message">
        <input type="file" name="media">
        <button type="submit">Send</button>
    </form>
    <?php else: echo "<p>You are muted.</p>"; endif; ?>
    <?php if (isset($_GET['edit'])): ?>
    <form method="POST">
        <input type="text" name="new_message" placeholder="New message">
        <button type="submit">Update</button>
    </form>
    <?php endif; ?>
</body>
</html>