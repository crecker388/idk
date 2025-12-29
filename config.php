<?php
session_start();
define('DB_HOST', 'sql310.ezyro.com');
define('DB_USER', 'ezyro_40778945');
define('DB_PASS', '65f2abde4');
define('DB_NAME', 'ezyro_40778945_goydazvon');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if not exist
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nick VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    warns INT DEFAULT 0,
    banned_until TIMESTAMP NULL,
    muted_until TIMESTAMP NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS global_chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    edited TIMESTAMP NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS private_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_id INT NOT NULL,
    to_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    edited TIMESTAMP NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_type ENUM('global', 'private') NOT NULL,
    chat_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    type ENUM('image', 'video', 'file') NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS warns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT NOT NULL,
    reason TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>