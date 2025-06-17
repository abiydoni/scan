<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Redirect to login page
    exit;
}
require_once 'api/db.php';

// Validasi device_id di database
$device_id = $_SESSION['device_id'] ?? null;
$user_id = $_SESSION['user']['id_code'] ?? null;

if ($device_id && $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE device_id = ? AND user_id = ?");
    $stmt->execute([$device_id, $user_id]);
    $deviceValid = $stmt->fetch();

    if (!$deviceValid) {
        // Device tidak valid lagi, hapus session dan redirect ke login
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

$stmt = $pdo->query("SELECT cp, hp FROM tb_profil LIMIT 1");
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

$cp = $profil['cp'] ?? 'Nama Kontak Tidak Ada';
$hp = $profil['hp'] ?? 'Nomor HP Tidak Ada';
// Ubah 0 jadi 62 jika perlu
$hp_link = preg_replace('/^0/', '62', $hp);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jimpitan</title>
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#ffffff">

  <script src="js/html5-qrcode.min.js" type="text/javascript"></script>
  <link rel="stylesheet" href="css/sweetalert2.min.css">
  <script src="js/sweetalert2.all.min.js"></script>
  <script src="js/jquery-3.6.0.min.js"></script>
  <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

  <style>
    body, html {
        margin: 10px;
        padding: 0;
        overflow: hidden;
        font-family: Arial, sans-serif;
    }
    #landscapeBlocker {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        z-index: 10000;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    #landscapeBlocker img {
        max-width: 30%;
        max-height: 30%;
    }
    .container {
        text-align: center;
        /* margin-top: 50px; */
    }
    .rounded {
        border-radius: 25px;
    }
    .roundedBtn {
        border-radius: 25px;
        background-color: #14505c;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    .stopBtn {
        border-radius: 25px;
        background-color: #F95454;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }
    .custom-timer-progress-bar {
        height: 4px; /* Height of the progress bar */
        background-color: #FF8A8A; /* Color of the progress bar */
        width: 80%; /* Adjust width as needed */
        margin: 0 auto; /* Center the progress bar horizontally */
    }

    .floating-button {
      position: fixed;
      bottom: 20px; /* Jarak dari bawah */
      right: 20px; /* Jarak dari kanan */
      background-color: #14505c; /* Warna latar belakang dengan transparansi */
      border-radius: 50%; /* Membuat tombol bulat */
      width: 60px; /* Lebar tombol */
      height: 60px; /* Tinggi tombol */
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); /* Bayangan */
      z-index: 1000; /* Pastikan di atas elemen lain */
  }

  .floating-button a {
      color: white; /* Warna teks */
      font-size: 24px; /* Ukuran teks */
      text-decoration: none; /* Menghilangkan garis bawah */
  }
  button {
    margin: 10px;
    padding: 10px 20px;
    border-radius: 25px;
    background-color: #14505c;
    color: white;
    border: none;
    cursor: pointer;
  }
  button:disabled {
      background-color: #ccc;
      cursor: not-allowed;
  }

.floating-button-logout {
    position: fixed;
    bottom: 20px; /* Jarak dari bawah */
    left: 20px; /* Jarak dari kiri */
    background-color: #F95454; /* Warna merah */
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    z-index: 1000;
}

.floating-button-logout a {
    color: white;
    font-size: 24px;
    text-decoration: none;
}

  </style>
</head>
<body>
  <div id="landscapeBlocker">
    <img src="assets/image/loading.gif" alt="Please rotate your device to portrait mode">
    <p>Please rotate your device to portrait mode.</p>
  </div>

  <div class="container">
  <h4>Selamat datang, <?= htmlspecialchars($_SESSION['user']['name']) ?></h4>
    <h3 style="color:grey;">Jimpitan RT.07 Randuares</h3>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const tanggalHariIni = today.toLocaleDateString('id-ID', options);
        document.getElementById('tanggalHariIni').innerText = `Hari: ${tanggalHariIni}`;
      });
    </script>
    <p style="color:grey; font-size: 14px; text-align: center;" id="tanggalHariIni"></p>

    <a href="api/detail_scan.php"><h4 id="totalScan">Menunggu data...</h4></a>

      <!-- Tombol Logout Floating
      <div class="floating-button-logout">
        <a href="api/logout.php" title="Keluar">
          <i class="bx bx-log-out"></i>
        </a>
      </div> -->

      <div class="floating-button">
        <label for="qr-input-file" id="fileInputLabel" style="color: white;">
          <i class="bx bxs-camera" style="font-size:24px; color: white;"></i>
        </label>
        <input type="file" id="qr-input-file" accept="image/*" capture hidden>
      </div>

    <div id="qr-reader"></div> <!-- QR camera dimulai -->
      
    <p style="color:grey; font-size: 10px; text-align: center;">Device ID Anda: <?= htmlspecialchars($_SESSION['device_id']) ?></p>
    <p style="color:grey; font-size: 10px; text-align: center;">Apabila ada kendala, hubungi: <?= htmlspecialchars($cp) ?></p>
    <p style="color:grey; font-size: 10px; text-align: center;">Ke no HP : <a href="https://wa.me/<?= htmlspecialchars($hp_link) ?>" target="_blank"><?= htmlspecialchars($hp) ?></a></p>
  </div>
<audio id="audio" src="assets/audio/interface.wav"></audio>

<script src="js/app.js"></script>

<script>
    // Fungsi untuk mengambil data secara realtime
    function updateData() {
        $.ajax({
            url: 'api/get_info.php',  // URL script PHP yang akan diambil
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Debugging: Lihat data yang diterima dari server
                console.log(data);

                if (data.error) {
                    console.log('Error: ' + data.error);
                } else {
                    // Update konten dengan data baru
                    $('#totalScan').html('Jumlah Scan: Rp. ' + parseInt(data.totalScan).toLocaleString('id-ID') + ' dan ' + data.totalData + ' KK');
                }
            },
            error: function(xhr, status, error) {
                console.log('Gagal mengambil data: ' + status + ' - ' + error);
            }
        });
    }

    // Update data setiap 1 detik (1000ms)
    setInterval(updateData, 3000);

    // Panggil updateData() sekali saat halaman dimuat
    $(document).ready(function() {
        updateData();
    });
</script>

<script>
// Service worker register
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('service-worker.js')
            .then(reg => console.log('✅ Service Worker registered:', reg.scope))
            .catch(err => console.error('❌ Service Worker registration failed:', err));
    });
}
</script>

</body>
</html>
