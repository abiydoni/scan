// Auto-login untuk login sekali selamanya
class AutoLogin {
  constructor() {
    this.deviceID = this.getDeviceID();
    this.init();
  }

  generateUUID() {
    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (c) =>
      (
        c ^
        (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))
      ).toString(16)
    );
  }

  getDeviceID() {
    let deviceID = localStorage.getItem("device_id");
    if (!deviceID) {
      deviceID = this.generateUUID();
      localStorage.setItem("device_id", deviceID);
    }
    return deviceID;
  }

  async attemptAutoLogin() {
    try {
      const response = await fetch("auto_login.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "device_id=" + encodeURIComponent(this.deviceID),
      });

      const result = await response.text();

      if (result === "login_ok") {
        // Auto-login berhasil, redirect ke index
        window.location.href = "index.php";
        return true;
      } else {
        // Auto-login gagal, biarkan user login manual
        console.log("Auto-login gagal:", result);
        return false;
      }
    } catch (error) {
      console.log("Auto-login error:", error);
      return false;
    }
  }

  init() {
    // Set device_id ke form
    const deviceIdInput = document.getElementById("device_id");
    if (deviceIdInput) {
      deviceIdInput.value = this.deviceID;
    }

    // Coba auto-login jika tidak ada error dari PHP
    const hasErrorFromPHP = document.querySelector(".error-message") !== null;
    if (!hasErrorFromPHP) {
      this.attemptAutoLogin();
    }
  }
}

// Inisialisasi auto-login saat DOM ready
document.addEventListener("DOMContentLoaded", function () {
  new AutoLogin();
});
