<?php
include 'config.php';
if (isset($_SESSION['user_id'])) header('Location: index.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nick = $conn->real_escape_string($_POST['nick']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (nick, email, password) VALUES ('$nick', '$email', '$password')";
    if ($conn->query($sql)) {
        echo "Registered successfully. <a href='login.php'>Login</a>";
    } else {
        echo "Error: Nick or email taken.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Goydazvon - Register</title>
    <style>
        body { background: #000; color: #fff; font-family: Arial; }
        form { width: 300px; margin: auto; padding: 20px; background: #222; border: 1px solid #444; }
        input { width: 100%; margin: 10px 0; padding: 10px; background: #333; color: #fff; border: 1px solid #555; }
        button { background: #444; color: #fff; padding: 10px; border: none; }
    </style>
</head>
<body>
    <form method="POST">
        <input type="text" name="nick" placeholder="Nick" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
</body>
</html>