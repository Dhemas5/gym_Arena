<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arena | Fit Club</title>
    <link rel="icon" type="image/png" href="../../../assets/assets_admin/dist/img/logoadmin.png">

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/fontawesome-free/css/all.min.css">

    <!-- Overlay Scrollbars -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/adminlte.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="../../../assets/assets_admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../../assets/assets_admin/dist/css/admin-styles.css">
    
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="hold-transition light-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed <?= $body_class ?? '' ?>">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle navigation">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" aria-label="Notifications" id="notificationDropdown">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge" id="notificationCount">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notificationMenu">
                        <span class="dropdown-item dropdown-header" id="notificationHeader">0 Notifikasi</span>
                        <div class="dropdown-divider"></div>
                        <div id="notificationList">
                            <!-- Notifikasi akan dimuat via AJAX -->
                            <div class="text-center py-3">
                                <i class="fas fa-spinner fa-spin"></i> Memuat notifikasi...
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <!-- Hanya tombol "Tandai Semua Sudah Dibaca" saja -->
                        <a href="javascript:void(0)" class="dropdown-item dropdown-footer" id="markAllRead">
                            <i class="fas fa-check-circle mr-1"></i> Tandai Semua Sudah Dibaca
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" aria-label="Fullscreen">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" aria-label="User menu">
                        <i class="far fa-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">User Menu</span>
                        <div class="dropdown-divider"></div>
                        <a href="../login/logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <script>
        $(document).ready(function() {
            console.log('Notifikasi system initialized');
            
            // Load notifikasi pertama kali
            loadNotifications();
            
            // Auto refresh notifikasi setiap 30 detik
            setInterval(loadNotifications, 30000);
            
            // Tandai semua sudah dibaca
            $('#markAllRead').click(function(e) {
                e.preventDefault();
                markAllAsRead();
            });
            
            // Toggle dropdown notifikasi
            $('#notificationDropdown').click(function() {
                console.log('Notification dropdown clicked');
                loadNotifications();
            });
        });

        function loadNotifications() {
            console.log('Loading notifications...');
            
            $.ajax({
                url: '../../api/notifikasi_api.php?action=get_notifications&limit=5',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Notifications response:', response);
                    if (response.success) {
                        updateNotificationUI(response.notifications, response.unread_count);
                    } else {
                        console.error('Notifications API error:', response.message);
                        showNotificationError('Gagal memuat notifikasi: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load notifications:', error);
                    showNotificationError('Koneksi gagal: ' + error);
                }
            });
        }

        function updateNotificationUI(notifications, unreadCount) {
            console.log('Updating UI with', notifications.length, 'notifications,', unreadCount, 'unread');
            
            // Update badge count
            $('#notificationCount').text(unreadCount);
            $('#notificationHeader').text(unreadCount > 0 ? unreadCount + ' Notifikasi Baru' : 'Tidak ada notifikasi baru');
            
            // Update notification list
            let notificationHTML = '';
            
            if (notifications.length === 0) {
                notificationHTML = `
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                        <p>Tidak ada notifikasi</p>
                        <small class="text-muted">Registrasi member baru akan muncul di sini</small>
                    </div>
                `;
            } else {
                notifications.forEach(notif => {
                    const isUnread = !notif.dibaca;
                    const borderColor = getColorByType(notif.tipe);
                    notificationHTML += `
                        <div class="dropdown-item notification-item ${isUnread ? 'unread' : ''}" 
                             data-id="${notif.id}" style="cursor: pointer; border-left: 3px solid ${borderColor};">
                            <div class="d-flex align-items-start">
                                <div class="mr-2">
                                    <i class="${notif.icon} text-${getColorClassByType(notif.tipe)}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1" style="font-size: 0.9rem; font-weight: 600;">${notif.judul}</h6>
                                        ${isUnread ? '<span class="badge badge-primary badge-sm ml-2">Baru</span>' : ''}
                                    </div>
                                    <p class="mb-1 small text-muted" style="line-height: 1.3;">${notif.pesan}</p>
                                    <small class="text-muted"><i class="far fa-clock mr-1"></i>${notif.waktu}</small>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                    `;
                });
            }
            
            $('#notificationList').html(notificationHTML);
            
            // Add click event untuk mark as read
            $('.notification-item').click(function() {
                const notifId = $(this).data('id');
                console.log('Marking notification as read:', notifId);
                markAsRead(notifId);
                
                // Update UI langsung
                $(this).removeClass('unread').find('.badge').remove();
                
                // Update counter
                const currentCount = parseInt($('#notificationCount').text());
                if (currentCount > 0) {
                    $('#notificationCount').text(currentCount - 1);
                    $('#notificationHeader').text((currentCount - 1) > 0 ? (currentCount - 1) + ' Notifikasi Baru' : 'Tidak ada notifikasi baru');
                }
            });
        }

        function showNotificationError(message) {
            $('#notificationList').html(`
                <div class="text-center py-3 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>${message}</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="loadNotifications()">
                        <i class="fas fa-redo mr-1"></i> Coba Lagi
                    </button>
                </div>
            `);
        }

        function getColorByType(tipe) {
            switch (tipe) {
                case 'member_baru': return '#28a745';
                case 'transaksi': return '#17a2b8';
                default: return '#6c757d';
            }
        }

        function getColorClassByType(tipe) {
            switch (tipe) {
                case 'member_baru': return 'success';
                case 'transaksi': return 'info';
                default: return 'secondary';
            }
        }

        function markAsRead(notifId) {
            $.ajax({
                url: '../../api/notifikasi_api.php?action=mark_read',
                type: 'POST',
                data: { id: notifId },
                dataType: 'json',
                error: function() {
                    console.error('Gagal menandai notifikasi sebagai dibaca');
                }
            });
        }

        function markAllAsRead() {
            $.ajax({
                url: '../../api/notifikasi_api.php?action=mark_all_read',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadNotifications();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menandai notifikasi',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
        </script>