<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta');

try {
    // Query untuk total Scan
    $sqlScan = "SELECT COALESCE(SUM(nominal), 0) AS total_scan FROM report WHERE jimpitan_date = CURDATE()";
    $stmtScan = $pdo->query($sqlScan);
    $totalScan = $stmtScan->fetch(PDO::FETCH_ASSOC)["total_scan"];
    
    // Query untuk total Data
    $sqlData = "SELECT COALESCE(count(nominal), 0) AS total_data FROM report WHERE jimpitan_date = CURDATE()";
    $stmtData = $pdo->query($sqlData);
    $totalData = $stmtData->fetch(PDO::FETCH_ASSOC)["total_data"];
    
    // Kirim data dalam format JSON
    echo json_encode([
        "totalScan" => $totalScan,
        "totalData" => $totalData
    ]);

} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>