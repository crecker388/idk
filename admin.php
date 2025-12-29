<?php
include 'config.php';
if (!isAdmin()) header('Location: index.php');

$users = $conn->query("SELECT * FROM users WHERE role != 'admin'");

// Handle moderation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_id = (int)$_POST['user_id'];
    if (isset($_POST['warn'])) {
        $conn->query("INSERT INTO warns (user_id, admin_id, reason) VALUES ($target_id, " . getUserId() . ", '" . $conn->real_escape_string($_POST['reason']) . "')");
        $warns = $conn->query("SELECT COUNT(*) FROM warns WHERE user_id=$target_id")->fetch_row()[0];
        $conn->query("UPDATE users SET warns=$warns WHERE id=$target_id");
        if ($warns >= 3) {
            $ban_until = date('Y-m-d H:i:s', strtotime('+1 day'));
            $conn->query("UPDATE users SET banned_until='$ban_until' WHERE id=$target_id");
        }
    }
    if (isset($_POST['mute'])) {
        $duration = (int)$_POST['duration']; // in minutes
        $mute_until = date('Y-m-d H:i:s', strtotime("+$duration minutes"));
        $conn->query("UPDATE users SET muted_until='$mute_until' WHERE id=$target_id");
    }
    if (isset($_POST['ban'])) {
        if ($_POST['duration'] == 'forever') {
            $ban_until = '9999-12-31 23:59:59';
        } else {
            $duration = (int)$_POST['duration']; // days
            $ban_until = date('Y-m-d H:i:s', strtotime("+$duration days"));
        }
        $conn->query("UPDATE users SET banned_until='$ban_until' WHERE id=$target_id");
    }
    // Edit profile: similar to profile.php update
    // Delete message: $conn->query("DELETE FROM global_chats/private_messages WHERE id=...");
    // Manage media: add new or delete existing
}

// List users with forms for warn/mute/ban/edit
?>
<!DOCTYPE html>
<html>
<head>
    <title>Goydazvon - Admin Panel</title>
    <style> /* Black theme */ </style>
</head>
<body>
    <h1>Admin Panel</h1>
    <a href="index.php">Back</a>
    <?php while ($user = $users->fetch_assoc()): ?>
        <div style="background: #333; padding: 10px; margin: 5px;">
            <strong><?php echo $user['nick']; ?></strong> (Warns: <?php echo $user['warns']; ?>, Banned until: <?php echo $user['banned_until'] ?? 'None'; ?>, Muted until: <?php echo $user['muted_until'] ?? 'None'; ?>)
            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <input type="text" name="reason" placeholder="Warn reason">
                <button name="warn">Warn</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <input type="number" name="duration" placeholder="Mute minutes">
                <button name="mute">Mute</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <input type="text" name="duration" placeholder="Ban days or forever">
                <button name="ban">Ban</button>
            </form>
            <a href="profile.php?id=<?php echo $user['id']; ?>">Edit Profile</a>
        </div>
    <?php endwhile; ?>
    <!-- Sections for deleting messages/media across chats -->
</body>
</html>