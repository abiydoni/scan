<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require '../helper/connection.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'User tidak login']);
    exit;
}

$pdo = getDatabaseConnection();
$user = $_SESSION['user'];
$role = $user['role'];

// Admin dan s_admin tidak perlu pengecekan shift
if (in_array($role, ['admin', 's_admin'])) {
    echo json_encode([
        'is_shift_day' => true,
        'message' => 'Admin dapat akses kapan saja',
        'user_shift' => 'Admin'
    ]);
    exit;
}

// Untuk pengurus dan user, cek shift
if (in_array($role, ['pengurus', 'user'])) {
    $currentDay = strtolower(date('l')); // 'monday', 'tuesday', dst
    $userShift = strtolower(trim($user['shift'])); // 'monday', 'tuesday', dst
    
    $isShiftDay = ($currentDay === $userShift);
    
    // Konversi ke Bahasa Indonesia untuk pesan
    $hariIndonesiaMap = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa', 
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu'
    ];
    
    $hariIndonesia = $hariIndonesiaMap[$userShift] ?? 'Tidak diketahui';
    $hariSekarang = $hariIndonesiaMap[$currentDay] ?? 'Tidak diketahui';
    
    if ($isShiftDay) {
        echo json_encode([
            'is_shift_day' => true,
            'message' => "Hari ini adalah jadwal jaga Anda",
            'user_shift' => $hariIndonesia,
            'current_day' => $hariSekarang
        ]);
    } else {
        echo json_encode([
            'is_shift_day' => false,
            'message' => "Hari ini bukan jadwal jaga Anda. Jadwal jaga: $hariIndonesia",
            'user_shift' => $hariIndonesia,
            'current_day' => $hariSekarang
        ]);
    }
    exit;
}

// Untuk warga
echo json_encode([
    'is_shift_day' => false,
    'message' => 'Role warga tidak diizinkan akses',
    'user_shift' => 'Tidak ada'
]);
?> 