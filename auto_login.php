<?php
session_start();
require 'api/db.php';

$device_id = $_POST['device_id'] ?? '';

if (!$device_id) {
    exit('invalid');
}

try {
    $pdo = getDatabaseConnection();

    // Ambil user dari device_id
    $stmt = $pdo->prepare("SELECT users.* FROM users
                           JOIN devices ON users.id_code = devices.user_id
                           WHERE devices.device_id = ?");
    $stmt->execute([$device_id]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;

        // Ambil menu berdasarkan role
        $role = $user['role'];
        switch ($role) {
            case 's_admin':
                $stmtMenu = $pdo->query('SELECT * FROM tb_menu WHERE s_admin = 1 ORDER BY nama ASC');
                break;
            case 'admin':
                $stmtMenu = $pdo->query('SELECT * FROM tb_menu WHERE admin = 1 ORDER BY nama ASC');
                break;
            case 'pengurus':
                $stmtMenu = $pdo->query('SELECT * FROM tb_menu WHERE pengurus = 1 ORDER BY nama ASC');
                break;
            case 'warga':
                $stmtMenu = $pdo->query('SELECT * FROM tb_menu WHERE warga = 1 ORDER BY nama ASC');
                break;
            default:
                $stmtMenu = $pdo->query('SELECT * FROM tb_menu WHERE 1 = 0');
                break;
        }
        $_SESSION['menus'] = $stmtMenu->fetchAll();

        echo "login_ok";
    } else {
        echo "not_found";
    }
} catch (PDOException $e) {
    echo 'db_error';
}
