// Inisialisasi pemindai QR
let isScanning = false;
const html5QrCode = new Html5Qrcode("qr-reader");

// Fungsi untuk menampilkan atau menyembunyikan blokir orientasi
function updateLandscapeBlocker() {
  const landscapeBlocker = document.getElementById("landscapeBlocker");
  if (landscapeBlocker) {
    if (window.orientation === 90 || window.orientation === -90) {
      landscapeBlocker.style.display = "flex";
      stopScanning();
    } else {
      landscapeBlocker.style.display = "none";
    }
  }
}

// Memulai pemindaian pada dokumen yang dimuat
document.addEventListener("DOMContentLoaded", updateLandscapeBlocker);
window.addEventListener("orientationchange", updateLandscapeBlocker);

// Fungsi untuk memutar audio
function playAudio() {
  const audio = document.getElementById("audio");
  if (audio) {
    audio.play().catch((error) => console.error("Error playing audio:", error));
  }
}

let nominal = 0; // Default nominal jika API gagal memberikan data

// Fungsi untuk mendapatkan tarif dari server
function fetchTarif() {
  fetch("../api/get_tarif.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        nominal = data.tarif;
        console.log("Tarif berhasil diperoleh:", nominal);
      } else {
        console.warn("Gagal mengambil tarif:", data.message);
      }
    })
    .catch((error) => console.error("Kesalahan mengambil tarif:", error));
}

// Panggil fetchTarif saat halaman dimuat
document.addEventListener("DOMContentLoaded", fetchTarif);

// Fungsi untuk menangani hasil pemindaian
function onScanSuccess(decodedText) {
  console.log("Teks yang dipindai:", decodedText);
  const id = decodedText;
  const today = new Date();
  const jimpitanDate = today.toLocaleDateString("id-ID", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });

  const [day, month, year] = jimpitanDate.split("/");
  const formattedDate = `${year}-${month}-${day}`;

  playAudio();

  console.log("Data yang akan dikirim:", {
    report_id: id,
    jimpitan_date: formattedDate,
    nominal: nominal,
  });

  fetch("../api/input_jimpitan.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      report_id: id,
      jimpitan_date: formattedDate,
      nominal: nominal,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        Swal.fire({
          icon: "success",
          title: "Sukses",
          text: data.message,
          willClose: startScanning,
        });
      } else {
        Swal.fire({
          icon: "warning",
          title: "Ooops!",
          text: data.message,
          showCancelButton: true,
          confirmButtonText: "Hapus Data",
          cancelButtonText: "Batal",
        }).then((result) => {
          if (result.isConfirmed) {
            fetch("../api/delete_jimpitan.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({
                report_id: id,
                jimpitan_date: formattedDate,
              }),
            })
              .then((response) => response.json())
              .then((deleteData) => {
                if (deleteData.success) {
                  Swal.fire({
                    icon: "success",
                    title: "Data Dihapus",
                    text: "Data yang sudah ada telah dihapus",
                    willClose: startScanning,
                  });
                } else {
                  Swal.fire({
                    icon: "error",
                    title: "Kesalahan",
                    text: deleteData.message,
                    willClose: startScanning,
                  });
                }
              });
          } else {
            startScanning();
          }
        });
      }
    })
    .catch((error) => console.error("Kesalahan Fetch:", error));

  stopScanning();
}

function onScanError(errorMessage) {
  console.warn(`Kesalahan pemindaian: ${errorMessage}`);
}

function startScanning() {
  if (!isScanning) {
    isScanning = true;
    html5QrCode
      .start(
        { facingMode: "environment" },
        { fps: 20, qrbox: 200 },
        onScanSuccess,
        onScanError
      )
      .catch((err) =>
        console.error("Kesalahan memulai pemindaian QR code:", err)
      );
  }
}

function stopScanning() {
  if (isScanning) {
    isScanning = false;
    html5QrCode
      .stop()
      .catch((err) =>
        console.error("Kesalahan menghentikan pemindaian QR code:", err)
      );
  }
}

const fileinput = document.getElementById("qr-input-file");
const fileInputLabel = document.getElementById("fileInputLabel");

fileInputLabel.addEventListener("click", (e) => {
  e.preventDefault();
  stopScanning();
  fileinput.click();
});

fileinput.addEventListener("change", (e) => {
  if (e.target.files.length === 0) {
    startScanning();
    return;
  }

  const imageFile = e.target.files[0];
  if (imageFile.type.startsWith("image/")) {
    html5QrCode
      .scanFile(imageFile, false)
      .then((qrCodeMessage) => {
        onScanSuccess(qrCodeMessage);
      })
      .catch((err) => {
        console.error(`Kesalahan pemindaian file. Alasan: ${err}`);
        alert("Gagal memindai kode QR. Silakan coba lagi.");
      });
  } else {
    alert("Silakan unggah file gambar yang valid.");
  }

  fileinput.value = "";
});

startScanning();
