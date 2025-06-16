<?php
session_start(); // Memulai sesi
date_default_timezone_set('Asia/Jakarta');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include the connection file
require '../helper/connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php'); // Redirect to login page
    exit; // Hentikan eksekusi jika pengguna tidak terautentikasi
}

// Dapatkan input data
$data = json_decode(file_get_contents("php://input"));

// Pastikan semua data yang diperlukan ada
if (isset($data->report_id) && isset($data->jimpitan_date)) {
    $report_id = $data->report_id;
    $jimpitan_date = $data->jimpitan_date;

    // Dapatkan koneksi database
    $conn = getDatabaseConnection();

    // Siapkan pernyataan SQL untuk penghapusan
    $sql = "DELETE FROM report WHERE report_id = ? AND jimpitan_date = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Eksekusi pernyataan
        $stmt->execute([$report_id, $jimpitan_date]);
        
        // Cek apakah ada baris yang dihapus
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada data yang ditemukan untuk dihapus']);
        }
    } else {
        // Respons gagal untuk persiapan pernyataan
        echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan pernyataan']);
    }
} else {
    // Respons jika data tidak lengkap
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
}
?>