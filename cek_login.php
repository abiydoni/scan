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

        // Cari user berdasarkan user_name
        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_name = ?');
        $stmt->execute([$user_name]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $currentDay = date('l');
            $role = $user['role'];
            $shiftDays = explode(',', $user['shift']);

            if ($role === 'warga') {
                $error = 'Login gagal! Role warga tidak diizinkan login.';
            } elseif (
                in_array($role, ['admin', 's_admin']) ||
                ($role === 'pengurus' && in_array($currentDay, $shiftDays))
            ) {
                $_SESSION['user'] = $user;
                $_SESSION['device_id'] = $device_id;

                // Cek apakah device_id sudah ada
                if ($device_id) {
                    $cek = $pdo->prepare("SELECT * FROM devices WHERE device_id = ?");
                    $cek->execute([$device_id]);

                    if (!$cek->fetch()) {
                        $save = $pdo->prepare("INSERT INTO devices (user_id, device_id) VALUES (?, ?)");
                        $save->execute([$user['id_code'], $device_id]);
                    }
                }

                // Login sukses, arahkan ke dashboard
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

// Jika gagal login, tampilkan kembali form login
// Pastikan $error ikut terbaca oleh halaman login
include 'login.php';
exit;
?>
