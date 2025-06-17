<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

// SQL statement untuk mengambil data
$stmt = $pdo->prepare("SELECT master_kk.kk_name, report.* FROM report JOIN master_kk ON report.report_id = master_kk.code_id WHERE report.jimpitan_date = CURDATE() ORDER BY report.scan_time DESC");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total nominal
$total_nominal = array_sum(array_column($data, 'nominal'));

// Kembalikan data dalam format JSON
echo json_encode([
    'data' => $data,
    'total_nominal' => $total_nominal,
]);
?>
