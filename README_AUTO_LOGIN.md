# Sistem Login Sekali Selamanya

## 🎯 **Konsep**

Sistem ini memungkinkan user login sekali dan kemudian akan otomatis login selamanya tanpa perlu memasukkan username dan password lagi.

## 🔧 **Cara Kerja**

### 1. **Device ID Generation**

- Setiap device mendapat ID unik yang disimpan di `localStorage`
- Device ID dibuat menggunakan UUID v4
- Device ID dikirim ke server saat login pertama kali

### 2. **Database Storage**

- Device ID disimpan di tabel `devices` bersama dengan `user_id`
- Relasi: `devices.device_id` ↔ `users.id_code`

### 3. **Auto-Login Process**

- Saat membuka `login.php`, sistem otomatis mencoba login menggunakan device ID
- Jika device ID valid dan user masih aktif, langsung redirect ke `index.php`
- Jika gagal, user bisa login manual

## 📁 **File yang Terlibat**

### Core Files:

- `login.php` - Halaman login utama
- `auto_login.php` - API untuk auto-login
- `cek_login.php` - API untuk login manual
- `js/auto-login.js` - JavaScript untuk auto-login
- `js/logout.js` - JavaScript untuk logout

### Database:

- `helper/connection.php` - Koneksi database untuk semua file

## 🔐 **Keamanan**

### Session Management:

- Session bertahan 1 tahun (31536000 detik)
- Session disimpan di server dan browser
- Device ID divalidasi setiap kali akses

### Role-based Access:

- **Admin/S_Admin**: Bisa login kapan saja
- **Pengurus/User**: Hanya bisa login sesuai jadwal shift
- **Warga**: Tidak diizinkan login otomatis

### Device Validation:

- Device ID divalidasi di database setiap akses
- Jika device tidak valid, session dihapus dan redirect ke login

## 🚀 **Cara Penggunaan**

### Untuk User:

1. Login pertama kali dengan username dan password
2. Device ID otomatis tersimpan di browser
3. Login selanjutnya otomatis tanpa input manual

### Untuk Admin:

- Bisa logout tanpa menghapus device ID (tetap bisa auto-login)
- Bisa logout dengan menghapus device ID (perlu login ulang)

## ⚙️ **Konfigurasi**

### Session Settings:

```php
ini_set('session.gc_maxlifetime', 31536000);      // 1 tahun
ini_set('session.cookie_lifetime', 31536000);     // 1 tahun
session_set_cookie_params(31536000);              // Cookie 1 tahun
```

### Database Tables:

```sql
-- Tabel devices untuk menyimpan device ID
CREATE TABLE devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50),
    name VARCHAR(100),
    device_id VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🔧 **Troubleshooting**

### Auto-login tidak berfungsi:

1. Cek apakah device ID tersimpan di localStorage
2. Cek apakah device ID ada di database
3. Cek apakah user masih aktif
4. Cek apakah hari ini adalah jadwal shift (untuk pengurus/user)

### Session hilang:

1. Cek session timeout di server
2. Cek cookie settings di browser
3. Cek apakah device ID masih valid di database

## 📝 **Notes**

- Device ID tidak dihapus saat logout biasa
- Device ID hanya dihapus saat logout dengan opsi "clear device"
- Auto-login gagal tidak menampilkan error ke user
- Admin dan s_admin bisa login kapan saja tanpa pengecekan shift
