// Logout tanpa menghapus device_id (agar tetap bisa auto-login)
function logoutWithoutClearingDevice() {
  // Konfirmasi logout
  if (confirm("Apakah Anda yakin ingin logout?")) {
    // Hapus session di server
    fetch("api/logout.php")
      .then(() => {
        // Redirect ke login page
        window.location.href = "login.php";
      })
      .catch((error) => {
        console.error("Logout error:", error);
        // Redirect ke login page meskipun ada error
        window.location.href = "login.php";
      });
  }
}

// Logout yang menghapus device_id (untuk keamanan)
function logoutWithClearingDevice() {
  // Konfirmasi logout
  if (
    confirm(
      "Apakah Anda yakin ingin logout? Device ID akan dihapus dan Anda perlu login ulang."
    )
  ) {
    // Hapus device_id dari localStorage
    localStorage.removeItem("device_id");

    // Hapus session di server
    fetch("api/logout.php")
      .then(() => {
        // Redirect ke login page
        window.location.href = "login.php";
      })
      .catch((error) => {
        console.error("Logout error:", error);
        // Redirect ke login page meskipun ada error
        window.location.href = "login.php";
      });
  }
}
