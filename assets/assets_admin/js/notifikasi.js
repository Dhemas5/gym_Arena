// File: assets/assets_admin/js/notifikasi.js
function markAsRead(id) {
  fetch("../../../setting/mark_notification_read.php?id=" + id)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update badge count
        let badge = document.querySelector(".notif-badge");
        if (badge) {
          let count = parseInt(badge.textContent) - 1;
          if (count > 0) {
            badge.textContent = count;
          } else {
            badge.remove();
          }
        }
      }
    });
}

function refreshNotifications() {
  // Ambil notifikasi terbaru setiap 30 detik
  setInterval(() => {
    fetch("../../../setting/get_notifications.php?limit=5")
      .then((response) => response.json())
      .then((data) => {
        // Update notifikasi jika ada yang baru
        console.log("Notifikasi diperbarui:", data);
      });
  }, 30000);
}

// Panggil saat halaman dimuat
document.addEventListener("DOMContentLoaded", refreshNotifications);
