<?php
// Biar session bertahan 1 tahun (365 hari)
ini_set('session.gc_maxlifetime', 31536000);      // 1 tahun di server (file session tidak dihapus cepat)
ini_set('session.cookie_lifetime', 31536000);     // 1 tahun di browser
ini_set('session.gc_probability', 1);             // Session akan dibersihkan...
ini_set('session.gc_divisor', 100);               // ...1 dari 100 request (default, bagus)

session_set_cookie_params(31536000);              // Cookie disimpan di browser selama 1 tahun
session_start();
date_default_timezone_set('Asia/Jakarta');

// Tangkap error dari URL jika ada (misalnya setelah redirect dari cek_login.php)
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;600;800&display=swap">
  <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
  <form action="cek_login.php" method="POST">
    <div class="screen-1">
      <div class="email">
        <label for="user_name">User</label>
        <div class="sec-2">
          <ion-icon name="person-outline"></ion-icon>
          <input type="text" name="user_name" placeholder="********" required/>
        </div>
      </div>
      <div class="password">
        <label for="password">Password</label>
        <div class="sec-2">
          <ion-icon name="lock-closed-outline"></ion-icon>
          <input class="pas" type="password" name="password" placeholder="********" required/>
          <input type="hidden" name="device_id" id="device_id">
        </div>
      </div>

      <button type="submit" class="login">Login</button>

      <div class="footer">
        <?php if ($error): ?>
          <div class="error-message" style="color: red; font-size: 12px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
      </div>
      <p style="color:grey; font-size: 8px; text-align: center;">@2024 copyright | by doniabiy</p>
    </div>
  </form>

  <script src="js/auto-login.js"></script>
</body>
</html>
