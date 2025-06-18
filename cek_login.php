<?php
// Perpanjang session hingga 1 tahun
// Biar session bertahan 1 tahun (365 hari)
ini_set('session.gc_maxlifetime', 31536000);      // 1 tahun di server (file session tidak dihapus cepat)
ini_set('session.cookie_lifetime', 31536000);     // 1 tahun di browser
ini_set('session.gc_probability', 1);             // Session akan dibersihkan...
ini_set('session.gc_divisor', 100);               // ...1 dari 100 request (default, bagus)

session_set_cookie_params(31536000);              // Cookie disimpan di browser selama 1 tahun
session_start();
date_default_timezone_set('Asia/Jakarta');
require 'helper/connection.php';

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
            $role = $user['role'];
            $currentDay = strtolower(date('l')); // hasil: 'monday', 'tuesday', dst
            $shiftDays = array_map('strtolower', array_map('trim', explode(',', $user['shift'])));

            if ($role === 'warga') {
                header("Location: login.php?error=" . urlencode("Login gagal! Role warga tidak diizinkan login."));
                exit;
            }

            if (
                in_array($role, ['admin', 's_admin']) ||
                (in_array($role, ['pengurus', 'user']) && in_array($currentDay, $shiftDays))
            ) {
                $_SESSION['user'] = $user;
                $_SESSION['device_id'] = $device_id;

                // Simpan device_id jika belum ada
                if (!empty($device_id)) {
                    $cek = $pdo->prepare("SELECT * FROM devices WHERE device_id = ?");
                    $cek->execute([$device_id]);

                    if (!$cek->fetch()) {
                        $save = $pdo->prepare("INSERT INTO devices (user_id, name, device_id) VALUES (?, ?, ?)");
                        $save->execute([$user['id_code'], $user['name'], $device_id]);
                    }
                }

                header("Location: index.php");
                exit;
            } else {
                header("Location: login.php?error=" . urlencode("Login gagal! Hari ini bukan jadwalmu jaga."));
                exit;
            }
        } else {
            header("Location: login.php?error=" . urlencode("User/Password salah atau tidak valid."));
            exit;
        }
    } catch (PDOException $e) {
        header("Location: login.php?error=" . urlencode("Database error: " . $e->getMessage()));
        exit;
    }
}
?>
