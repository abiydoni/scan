<?php
session_start(); 
date_default_timezone_set('Asia/Jakarta');

require 'helper/connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $device_id = $_POST['device_id'] ?? '';

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_name = ?');
        $stmt->execute([$user_name]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $currentDay = date('l');

            // Jika role warga atau jadwal shift cocok
            if (
                $user['role'] === 'warga' || 
                in_array($user['role'], ['pengurus', 'admin', 's_admin']) || 
                in_array($currentDay, explode(',', $user['shift']))
            ) {
                $_SESSION['user'] = $user;
                $_SESSION['device_id'] = $device_id;

                // Simpan device_id ke database jika belum ada
                if ($device_id) {
                    $cek = $pdo->prepare("SELECT * FROM devices WHERE device_id = ?");
                    $cek->execute([$device_id]);

                    if (!$cek->fetch()) {
                        $save = $pdo->prepare("INSERT INTO devices (user_id, device_id) VALUES (?, ?)");
                        $save->execute([$user['id_code'], $device_id]);
                    }
                }
                header('Location: index.php');
                exit;
            } else {
                $error = 'Login gagal! Hari ini bukan jadwalmu jaga.';
            }
        } else {
            $error = 'Username atau password salah!';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}
?>
