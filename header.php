<?php include 'config.php'; 
if (!isset($_SESSION['user_id'])) header('Location: login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goydazvon</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>GOYDAZVON</h1>
    <nav>
        <a href="index.php">Global Chat</a>
        <a href="private.php">Private Messages</a>
        <a href="profile.php">Profile</a>
        <?php if (isAdmin()): ?>
            <a href="admin.php">Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php" class="logout">Logout (<?php echo $_SESSION['nick']; ?>)</a>
    </nav>
</header>
<div class="container">