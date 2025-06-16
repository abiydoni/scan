<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$error = '';
include "cek_login.php"
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;600;800&display=swap'>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<form action="login.php" method="POST">
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
        </div>
    </div>

    <div class="password">
    <label><input type="checkbox" name="redirect_option" value="dashboard"> Go To Dashboard</label>
    </div>

    <button class="login">Login</button>
    <div class="footer">
        <?php if ($error): ?>
            <div class="error-message" style="color: red; font-size: 12px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
    <p style="color:grey; font-size: 8px; text-align: center;">@2024 copyright | by doniabiy</p>
</div>
</form>
</body>
</html>