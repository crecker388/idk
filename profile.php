<?php
include 'config.php';
if (!isset($_SESSION['user_id'])) header('Location: login.php');

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : getUserId();
$is_own = $user_id == getUserId();
$can_edit = $is_own || isAdmin();

$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $can_edit) {
    $bio = $conn->real_escape_string($_POST['bio']);
    $conn->query("UPDATE users SET bio='$bio' WHERE id=$user_id");
    header('Location: profile.php?id=' . $user_id);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Goydazvon - Profile</title>
    <style> /* Similar black theme */ </style>
</head>
<body>
    <h1>Profile: <?php echo $user['nick']; ?></h1>
    <p>Email: <?php echo $user['email']; ?></p>
    <p>Bio: <?php echo $user['bio'] ?? 'No bio'; ?></p>
    <?php if ($can_edit): ?>
    <form method="POST">
        <textarea name="bio" placeholder="Bio"><?php echo $user['bio'] ?? ''; ?></textarea>
        <button type="submit">Update Bio</button>
    </form>
    <?php endif; ?>
    <a href="index.php">Back</a>
</body>
</html>