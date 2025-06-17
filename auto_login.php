<?php
session_start();
require 'api/db.php';
date_default_timezone_set('Asia/Jakarta');

$device_id = $_POST['device_id'] ?? '';

if (!$device_id) {
    echo 'Device ID tidak ditemukan.';
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
        echo 'Login gagal! Perangkat tidak dikenali.';
        exit;
    }

    $role = $user['role'];
    $shift = $user['shift'];
    $currentDay = date('l');

    if ($role === 'warga') {
        echo 'Login gagal! Role warga tidak diizinkan login otomatis.';
        exit;
    }

    if (in_array($role, ['pengurus', 'user'])) {
        $shiftDays = explode(',', $shift);
        if (!in_array($currentDay, $shiftDays)) {
            echo 'Login gagal! Hari ini bukan jadwalmu jaga.';
            exit;
        }
    }

    // Login berhasil → simpan session
    $_SESSION['user'] = $user;
    $_SESSION['device_id'] = $device_id;

    echo 'login_ok';
    exit;

} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
    exit;
}
