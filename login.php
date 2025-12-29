<?php
include 'config.php';
if (isset($_SESSION['user_id'])) header('Location: index.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($_POST['password'], $user['password'])) {
            if ($user['banned_until'] > date('Y-m-d H:i:s')) {
                echo "You are banned until " . $user['banned_until'];
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nick'] = $user['nick'];
                $_SESSION['role'] = $user['role'];
                header('Location: index.php');
            }
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Goydazvon - Login</title>
    <style>
        body { background: #000; color: #fff; font-family: Arial; }
        form { width: 300px; margin: auto; padding: 20px; background: #222; border: 1px solid #444; }
        input { width: 100%; margin: 10px 0; padding: 10px; background: #333; color: #fff; border: 1px solid #555; }
        button { background: #444; color: #fff; padding: 10px; border: none; }
    </style>
</head>
<body>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <a href="register.php" style="color: #fff;">Register</a>
</body>
</html>