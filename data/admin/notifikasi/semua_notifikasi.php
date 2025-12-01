<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";
checkSession("admin");

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Query untuk semua notifikasi
$sql = "SELECT * FROM tbl_notifikasi ORDER BY dibuat_pada DESC LIMIT $offset, $limit";
$queryNotif = is_object($con) ? $con->query($sql) : mysqli_query($con, $sql);
$totalNotif = is_object($con) ? $con->query("SELECT COUNT(*) as total FROM tbl_notifikasi")->fetch_assoc()['total'] : mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM tbl_notifikasi"))['total'];
$totalPages = ceil($totalNotif / $limit);

// Fungsi helper
function getNotifIcon($tipe) {
    $tipe = strtolower($tipe ?? '');
    if (strpos($tipe, 'member') !== false) return 'user-plus';
    if (strpos($tipe, 'transaksi') !== false) return 'money-bill-wave';
    return 'bell';
}

function getNotifIconClass($tipe) {
    $tipe = strtolower($tipe ?? '');
    if (strpos($tipe, 'member') !== false) return 'member';
    if (strpos($tipe, 'transaksi') !== false) return 'transaksi';
    return 'lainnya';
}

function timeAgo($datetime) {
    if (empty($datetime)) return 'Baru saja';
    $timestamp = strtotime($datetime);
    if (!$timestamp) return date('d M Y');
    $difference = time() - $timestamp;
    
    if ($difference < 60) return 'Baru saja';
    elseif ($difference < 3600) return floor($difference / 60) . ' menit lalu';
    elseif ($difference < 86400) return floor($difference / 3600) . ' jam lalu';
    elseif ($difference < 2592000) return floor($difference / 86400) . ' hari lalu';
    else return date('d M Y H:i', $timestamp);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Semua Notifikasi - Arena Fit Club</title>
    <link rel="icon" type="image/png" href="../../../assets/assets_admin/dist/img/logoadmin.png">
    
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css">
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/admin-styles.css">
    
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .notification-page {
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-actions {
            display: flex;
            gap: 1rem;
        }
        
        .notification-list {
            background: var(--primary-dark);
            border-radius: 12px;
            border: 1px solid var(--sidebar-hover);
            overflow: hidden;
        }
        
        .notification-item {
            display: flex;
            align-items: flex-start;
            padding: 1.25rem;
            border-bottom: 1px solid var(--sidebar-hover);
            transition: all 0.3s ease;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background: var(--sidebar-hover);
        }
        
        .notification-item.unread {
            background: rgba(25, 118, 210, 0.05);
            border-left: 4px solid var(--info-color);
        }
        
        .notification-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
            font-size: 1.2rem;
        }
        
        .notification-icon.member {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }
        
        .notification-icon.transaksi {
            background: rgba(33, 150, 243, 0.2);
            color: #2196f3;
        }
        
        .notification-icon.lainnya {
            background: rgba(158, 158, 158, 0.2);
            color: #9e9e9e;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: white;
            font-size: 1.1rem;
        }
        
        .notification-message {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }
        
        .notification-meta {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-top: 0.75rem;
        }
        
        .notification-time {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
        }
        
        .notification-status {
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }
        
        .status-unread {
            background: rgba(33, 150, 243, 0.2);
            color: #2196f3;
        }
        
        .status-read {
            background: rgba(158, 158, 158, 0.2);
            color: #9e9e9e;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.5);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <?php include "../layout/header.php"; ?>
        <?php include "../layout/sidebar.php"; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Semua Notifikasi</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                                <li class="breadcrumb-item active">Notifikasi</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="notification-page">
                        <div class="page-header">
                            <h2 class="mb-0">Riwayat Notifikasi</h2>
                            <div class="page-actions">
                                <?php if ($totalNotif > 0): ?>
                                    <button class="btn btn-info" onclick="markAllAsRead()">
                                        <i class="fas fa-check-double mr-2"></i>Tandai Semua Dibaca
                                    </button>
                                <?php endif; ?>
                                <a href="../dashboard/index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                                </a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Total <?= $totalNotif ?> Notifikasi
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <?php if ($queryNotif && (is_object($con) ? $queryNotif->num_rows : mysqli_num_rows($queryNotif)) > 0): ?>
                                    <div class="notification-list">
                                        <?php while ($notif = is_object($con) ? $queryNotif->fetch_assoc() : mysqli_fetch_assoc($queryNotif)): 
                                            $dibaca = $notif['dibaca'] ?? 0;
                                            $statusClass = $dibaca ? 'read' : 'unread';
                                            $statusText = $dibaca ? 'Sudah Dibaca' : 'Belum Dibaca';
                                        ?>
                                            <div class="notification-item <?= $statusClass ?>" 
                                                 onclick="markAsRead(<?= $notif['id'] ?>, this)">
                                                <div class="notification-icon <?= getNotifIconClass($notif['tipe']) ?>">
                                                    <i class="fas fa-<?= getNotifIcon($notif['tipe']) ?>"></i>
                                                </div>
                                                <div class="notification-content">
                                                    <div class="notification-title">
                                                        <?= ucfirst(str_replace('_', ' ', $notif['tipe'])) ?>
                                                    </div>
                                                    <div class="notification-message">
                                                        <?= htmlspecialchars($notif['pesan']) ?>
                                                    </div>
                                                    <div class="notification-meta">
                                                        <div class="notification-time">
                                                            <i class="far fa-clock mr-1"></i>
                                                            <?= timeAgo($notif['dibuat_pada']) ?>
                                                        </div>
                                                        <span class="notification-status <?= $dibaca ? 'status-read' : 'status-unread' ?>">
                                                            <?= $statusText ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-bell-slash"></i>
                                        <h3>Tidak Ada Notifikasi</h3>
                                        <p>Belum ada notifikasi yang tersedia</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($totalPages > 1): ?>
                            <div class="card-footer">
                                <div class="pagination-container">
                                    <nav>
                                        <ul class="pagination">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $page - 1 ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $page + 1 ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php include "../layout/footer.php"; ?>
    </div>

    <!-- jQuery -->
    <script src="../../../assets/assets_admin/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../../assets/assets_admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../../assets/assets_admin/dist/js/adminlte.min.js"></script>

    <script>
    function markAsRead(notifId, element) {
        fetch('notifikasi_api.php?action=mark_read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + notifId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update tampilan
                element.classList.remove('unread');
                const statusElement = element.querySelector('.notification-status');
                statusElement.textContent = 'Sudah Dibaca';
                statusElement.className = 'notification-status status-read';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Notifikasi telah ditandai sebagai dibaca',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function markAllAsRead() {
        Swal.fire({
            title: 'Tandai Semua Dibaca?',
            text: 'Semua notifikasi akan ditandai sebagai sudah dibaca',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Tandai Semua',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('notifikasi_api.php?action=mark_all_read', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update semua item notifikasi
                        document.querySelectorAll('.notification-item.unread').forEach(item => {
                            item.classList.remove('unread');
                            const statusElement = item.querySelector('.notification-status');
                            statusElement.textContent = 'Sudah Dibaca';
                            statusElement.className = 'notification-status status-read';
                        });
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    }
    </script>
</body>
</html>