<?php
// db.php
$host = 'localhost';
$db = 'appsbeem_jimpitan';
$user = 'appsbeem_admin';
$pass = 'A7by777__';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>