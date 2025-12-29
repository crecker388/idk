<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) header('Location: login.php');

$user_id = getUserId();
$muted = $conn->query("SELECT muted_until FROM users WHERE id=$user_id")->fetch_assoc()['muted_until'] > date('Y-m-d H:i:s');

// List users for private chat
$users = $conn->query("SELECT id, nick FROM users WHERE id != $user_id");

// Handle send (similar to global, but with to_id)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['to_id']) && !$muted) {
    $to_id = (int)$_POST['to_id'];
    $message = $conn->real_escape_string($_POST['message']);
    $conn->query("INSERT INTO private_messages (from_id, to_id, message) VALUES ($user_id, $to_id, '$message')");
    // Handle media similar to global
}

// Fetch for specific user (AJAX ?to_id=X&ajax=1)
if (isset($_GET['ajax']) && isset($_GET['to_id'])) {
    $to_id = (int)$_GET['to_id'];
    $result = $conn->query("SELECT p.id, u.nick, p.message, p.timestamp, p.edited FROM private_messages p JOIN users u ON p.from_id = u.id WHERE (p.from_id = $user_id AND p.to_id = $to_id) OR (p.from_id = $to_id AND p.to_id = $user_id) ORDER BY p.timestamp DESC");
    // Output similar to global chat
    while ($row = $result->fetch_assoc()) {
        // ... (echo div with message, media, edit/delete if owner or admin)
    }
    exit;
}

// Edit/delete similar to global
// ...
?>
<!DOCTYPE html>
<html>
<head>
    <title>Goydazvon - Private Messages</title>
    <!-- Similar style and JS as index.php, but refresh based on selected user -->
</head>
<body>
    <h1>Private Messages</h1>
    <a href="index.php">Global Chat</a> | <a href="profile.php">Profile</a> | <?php if (isAdmin()) echo '<a href="admin.php">Admin Panel</a>'; ?> | <a href="logout.php">Logout</a>
    <h2>Select User</h2>
    <?php while ($user = $users->fetch_assoc()): ?>
        <a href="?to=<?php echo $user['id']; ?>" style="color: #fff; display: block;"><?php echo $user['nick']; ?></a>
    <?php endwhile; ?>
    <?php if (isset($_GET['to'])): $to_id = (int)$_GET['to']; ?>
    <div id="chat"></div>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="to_id" value="<?php echo $to_id; ?>">
        <input type="text" name="message" placeholder="Message">
        <input type="file" name="media">
        <button type="submit">Send</button>
    </form>
    <script>
        // Similar refresh, but with ?to_id=<?php echo $to_id; ?>&ajax=1
    </script>
    <?php endif; ?>
</body>
</html>