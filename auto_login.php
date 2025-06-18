<?php
// Biar session bertahan 1 tahun (365 hari)
ini_set('session.gc_maxlifetime', 31536000);      // 1 tahun di server (file session tidak dihapus cepat)
ini_set('session.cookie_lifetime', 31536000);     // 1 tahun di browser
ini_set('session.gc_probability', 1);             // Session akan dibersihkan...
ini_set('session.gc_divisor', 100);               // ...1 dari 100 request (default, bagus)

session_set_cookie_params(31536000);              // Cookie disimpan di browser selama 1 tahun
session_start();
require 'api/db.php';
date_default_timezone_set('Asia/Jakarta');

$device_id = $_POST['device_id'] ?? '';

if (!$device_id) {
    header("Location: login.php?error=" . urlencode("Device ID tidak ditemukan."));
    exit;
}

try {
    $pdo = getDatabaseConnection();

    // Cek apakah device_id ada di tabel devices
    $stmt = $pdo->prepare("
        SELECT users.* FROM users
        JOIN devices ON users.id_code = devices.user_id
        WHERE devices.device_id = ?
    ");
    $stmt->execute([$device_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: login.php?error=" . urlencode("Login gagal! Perangkat tidak dikenali."));
        exit;
    }

    $role = $user['role'];
    $shift = $user['shift'];
    $currentDay = date('l');

    if ($role === 'warga') {
        header("Location: login.php?error=" . urlencode("Login gagal! Role warga tidak diizinkan login otomatis."));
        exit;
    }

    if (in_array($role, ['pengurus', 'user'])) {
        $shiftDays = explode(',', $shift);
        if (!in_array($currentDay, $shiftDays)) {
            header("Location: login.php?error=" . urlencode("Login gagal! Hari ini bukan jadwalmu jaga."));
            exit;
        }
    }

    // Login berhasil → simpan session
    $_SESSION['user'] = $user;
    $_SESSION['device_id'] = $device_id;

    echo 'login_ok';
    exit;

} catch (PDOException $e) {
    header("Location: login.php?error=" . urlencode("Database error: " . $e->getMessage()));
    exit;
}
