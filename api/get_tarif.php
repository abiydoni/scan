<?php
// Koneksi database
include 'db.php';

// Inisialisasi respon
$response = [
    'success' => false,
    'tarif' => 0,
    'message' => 'Gagal mengambil tarif.'
];

try {
    // Query untuk mengambil tarif dengan kode_tarif = 'JIMPIT'
    $stmt = $pdo->prepare("SELECT tarif FROM tb_tarif WHERE kode_tarif = 'TR001' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Jika tarif ditemukan
        $response['success'] = true;
        $response['tarif'] = (int)$result['tarif'];
        $response['message'] = 'Tarif berhasil diambil.';
    } else {
        $response['message'] = 'Tarif tidak ditemukan.';
    }
} catch (Exception $e) {
    $response['message'] = 'Kesalahan: ' . $e->getMessage();
}

// Kirim respon sebagai JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
