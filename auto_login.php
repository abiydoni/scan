<?php
session_start();
require 'api/db.php';

$device_id = $_POST['device_id'] ?? '';

if (!$device_id) {
    exit('invalid');
}

try {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT users.* FROM users
                           JOIN devices ON users.id_code = devices.user_id
                           WHERE devices.device_id = ?");
    $stmt->execute([$device_id]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
        echo "login_ok";
    } else {
        echo "not_found";
    }
} catch (PDOException $e) {
    echo 'db_error';
}
