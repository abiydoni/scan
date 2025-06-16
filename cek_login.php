<?php
session_start(); 
date_default_timezone_set('Asia/Jakarta');

require 'helper/connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $redirect_option = $_POST['redirect_option'] ?? 'scan_app';

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_name = ?');
        $stmt->execute([$user_name]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $currentDay = date('l');
            // ✅ warga bisa login kapan saja
            if (in_array($user['role'], ['pengurus', 'admin', 's_admin', 'warga']) || in_array($currentDay, explode(',', $user['shift']))) {
                $_SESSION['user'] = $user;

                $role = $user['role'];
                switch ($role) {
                    case 's_admin':
                        $stmtMenu = $pdo->query('SELECT * FROM tb_menu WHERE s_admin = 1 ORDER BY nama ASC');
                        break;
                    case 'admin':
                        $stmtMenu = $pdo->query('SELECT * FROM tb_menu WHERE admin = 1 ORDER BY nama ASC');
                        break;
                    case 'user':
                        $stmtMenu = $pdo->query('SELECT * FROM tb_menu WHERE status = 1 ORDER BY nama ASC');
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
                $menus = $stmtMenu->fetchAll();
                $_SESSION['menus'] = $menus;

                // warga dan user tidak boleh masuk dashboard
                if ($redirect_option === 'dashboard' && in_array($role, ['user', 'warga'])) {
                    $error = 'Maaf, kamu tidak memiliki akses ke Dashboard';
                } else {
                    if ($redirect_option === 'dashboard') {
                        header('Location: dashboard');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                }
            } else {
                $error = 'Login gagal! Hari ini bukan jadwalmu jaga';
            }
        } else {
            $error = 'Username atau password salah!';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>
