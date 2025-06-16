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

// Dapatkan informasi pengguna dari sesi
$collector = $_SESSION['user']['user_name'];
$kode_u = $_SESSION['user']['id_code'];
$nama_u = $_SESSION['user']['name'];

// Dapatkan input data
$data = json_decode(file_get_contents("php://input"));

// Pastikan semua data yang diperlukan ada
if (isset($data->report_id) && isset($data->jimpitan_date) && isset($data->nominal)) {
    $report_id = $data->report_id;
    $jimpitan_date = $data->jimpitan_date;
    $nominal = $data->nominal;

    // Dapatkan koneksi database
    $conn = getDatabaseConnection();

    // Periksa apakah data sudah ada
    $checkSql = "
        SELECT COUNT(*) AS count, m.kk_name 
        FROM report r
        JOIN master_kk m ON r.report_id = m.code_id 
        WHERE r.report_id = ? AND r.jimpitan_date = ?
    ";

    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$report_id, $jimpitan_date]);
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    $exists = $result['count'];
    $kk_name = $result['kk_name'] ?? null; // Jika kk_name tidak ada, set ke null

    if ($exists > 0) {
        echo json_encode(['success' => false, 'message' => 'Jimpitan tanggal: ' . $jimpitan_date . ', Dengan Nama: ' . $kk_name . ' sudah ada, mau di hapus?']);

        exit; // Hentikan eksekusi jika data sudah ada
    }

    // Siapkan pernyataan SQL untuk penyisipan
    $sql = "INSERT INTO report (report_id, jimpitan_date, nominal, collector, kode_u, nama_u) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Eksekusi pernyataan
        $stmt->execute([$report_id, $jimpitan_date, $nominal, $collector, $kode_u, $nama_u]);
        
            // Ambil kk_name setelah insert
        $kkQuery = "SELECT kk_name FROM master_kk WHERE code_id = ?";
        $kkStmt = $conn->prepare($kkQuery);
        $kkStmt->execute([$report_id]);
        $kkResult = $kkStmt->fetch(PDO::FETCH_ASSOC);
        $kk_name = $kkResult['kk_name'] ?? 'Tidak Diketahui'; // Default jika NULL

        echo json_encode([
            'success' => true,
            'message' => 'Jimpitan tanggal: ' . $jimpitan_date . ', Dengan Nama: ' . $kk_name . ', tercatat dengan nominal: Rp ' . number_format($nominal, 0, ',', '.')
        ]);
        
    } else {
        // Respons gagal untuk persiapan pernyataan
        echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan pernyataan']);
    }
} else {
    // Respons jika data tidak lengkap
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
}
?>