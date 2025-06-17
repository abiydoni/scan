<?php
session_start();
require 'api/db.php';
date_default_timezone_set('Asia/Jakarta');

$error = '';

$device_id = $_POST['device_id'] ?? '';

if (!$device_id) {
    $error = 'Device ID tidak ditemukan.';
} else {
    try {
        $pdo = getDatabaseConnection();

        // Cek apakah device ID terdaftar
        $stmt = $pdo->prepare("
            SELECT users.* FROM users
            JOIN devices ON users.id_code = devices.user_id
            WHERE devices.device_id = ?
        ");
        $stmt->execute([$device_id]);
        $user = $stmt->fetch();

        if ($user) {
            $role = $user['role'];
            $shift = $user['shift'];
            $currentDay = date('l');

            if ($role === 'warga') {
                $error = 'Login gagal! Role warga tidak diizinkan login otomatis.';
            } elseif (
                in_array($role, ['admin', 's_admin']) ||
                ($role === 'pengurus' && in_array($currentDay, explode(',', $shift)))
            ) {
                $_SESSION['user'] = $user;
                $_SESSION['device_id'] = $device_id;

                // Simpan device_id jika belum terdaftar
                $cek = $pdo->prepare("SELECT * FROM devices WHERE device_id = ?");
                $cek->execute([$device_id]);

                if (!$cek->fetch()) {
                    $save = $pdo->prepare("INSERT INTO devices (user_id, device_id) VALUES (?, ?)");
                    $save->execute([$user['id_code'], $device_id]);
                }

                echo 'login_ok';
                exit;
            } else {
                $error = 'Login gagal! Hari ini bukan jadwalmu jaga.';
            }
        } else {
            $error = 'Login gagal! Perangkat tidak dikenali.';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

echo $error;
