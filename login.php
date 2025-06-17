<?php
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

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const hasErrorFromPHP = <?php echo $error ? 'true' : 'false'; ?>;
      if (hasErrorFromPHP) return; // Jangan jalankan auto-login jika ada error dari PHP

      function generateUUID() {
        return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
          (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
        );
      }

      let deviceID = localStorage.getItem("device_id");
      if (!deviceID) {
        deviceID = generateUUID();
        localStorage.setItem("device_id", deviceID);
      }

      document.getElementById("device_id").value = deviceID;

      fetch("auto_login.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "device_id=" + encodeURIComponent(deviceID)
      })
      .then(res => res.text())
      .then(res => {
        if (res === "login_ok") {
          window.location.href = "index.php";
        } else {
          const footer = document.querySelector(".footer");
          const existingError = document.querySelector(".error-message");

          if (existingError) {
            existingError.textContent = res;
          } else {
            const errorEl = document.createElement("div");
            errorEl.className = "error-message";
            errorEl.style.color = "red";
            errorEl.style.fontSize = "12px";
            errorEl.textContent = res;
            footer.prepend(errorEl);
          }

          localStorage.removeItem("device_id");
        }
      });
    });
  </script>
</body>
</html>
