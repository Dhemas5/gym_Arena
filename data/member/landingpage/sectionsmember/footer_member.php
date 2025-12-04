<?php
require "../../../setting/session.php";
require "../../../setting/koneksi.php";

// Pastikan user sudah login sebagai member
if (!isset($_SESSION['login']) || $_SESSION['user_type'] !== 'member') {
    header("Location: ../login/login.php");
    exit;
}

$member_id = $_SESSION['id_member'];

// Variabel untuk menangani form submission
$form_submitted = false;
$success_message = "";
$error_message = "";
$show_thankyou = false;

// Proses form testimonial jika dikirim - TANPA REDIRECT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    $testimoni = $con->real_escape_string($_POST['testimoni']);
    $rating = intval($_POST['rating']);
    
    // Validasi input
    if (!empty($testimoni) && $rating >= 1 && $rating <= 5) {
        $query = "INSERT INTO tbl_testimoni (member_id, testimoni, rating, status) VALUES (?, ?, ?, 'pending')";
        $stmt = $con->prepare($query);
        $stmt->bind_param("isi", $member_id, $testimoni, $rating);
        
        if ($stmt->execute()) {
            $form_submitted = true;
            $show_thankyou = true;
            $success_message = "Testimoni berhasil dikirim! Menunggu persetujuan admin.";
        } else {
            $error_message = "Gagal mengirim testimoni. Silakan coba lagi.";
        }
        $stmt->close();
    } else {
        $error_message = "Harap isi testimoni dan berikan rating.";
    }
}

// Ambil testimoni member yang sudah dikirim
$member_testimonials = [];
$query = "
    SELECT testimoni, rating, status, created_at 
    FROM tbl_testimoni 
    WHERE member_id = ? 
    ORDER BY created_at DESC
";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $member_testimonials[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonial - Gym Arena</title>
    
    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/stylemember.css">
    
    <style>
        /* VARIABLES */
        :root {
            --primary: #1976d2;
            --primary-dark: #1565c0;
            --primary-light: #42a5f5;
            --primary-bg: rgba(25, 118, 210, 0.1);
            --secondary: #03a9f4;
            --accent: #00bcd4;
            --dark: #0d1b2a;
            --darker: #0a1929;
            --light: #e3f2fd;
            --text: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --card-bg: rgba(13, 27, 42, 0.8);
            --card-border: rgba(66, 165, 245, 0.3);
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #ffc107;
        }

        /* FOOTER STYLES */
        .footer {
            background: linear-gradient(135deg, var(--dark) 0%, var(--darker) 100%);
            border-top: 1px solid var(--card-border);
            padding: 60px 0 20px;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(66, 165, 245, 0.05) 0%, transparent 50%);
            z-index: 0;
        }

        .footer h5 {
            color: var(--primary-light) !important;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .footer h5 i {
            color: var(--primary-light);
        }

        /* TESTIMONIAL FORM STYLES */
        .testimonial-form {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 15px;
            padding: 25px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .testimonial-form:hover {
            border-color: var(--primary-light);
            box-shadow: 0 10px 30px rgba(66, 165, 245, 0.1);
        }

        .form-label {
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-light);
            color: var(--text);
            box-shadow: 0 0 0 0.25rem rgba(66, 165, 245, 0.25);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        /* RATING STYLES */
        .rating-input {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .form-check-inline {
            margin-right: 0;
            margin-bottom: 5px;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid var(--card-border);
            width: 20px;
            height: 20px;
            margin-right: 8px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
            transform: scale(1.1);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(66, 165, 245, 0.25);
        }

        .form-check-label {
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .form-check-label:hover {
            color: var(--text);
        }

        .form-check-input:checked ~ .form-check-label {
            color: var(--primary-light);
        }

        .fa-star {
            color: var(--warning);
            font-size: 0.9rem;
        }

        /* TESTIMONIAL BUTTON */
        .btn-testimonial {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border: none;
            color: var(--text);
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-testimonial::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-testimonial:hover::before {
            left: 100%;
        }

        .btn-testimonial:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(66, 165, 245, 0.4);
            color: var(--text);
        }

        /* ALERT STYLES */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border-left: 4px solid var(--success);
            color: var(--text);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border-left: 4px solid var(--danger);
            color: var(--text);
        }

        .alert-dismissible .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }

        .alert-dismissible .btn-close:hover {
            opacity: 1;
        }

        /* CONTACT INFO STYLES */
        .footer ul.list-unstyled li {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .footer ul.list-unstyled li:hover {
            color: var(--text);
        }

        .footer ul.list-unstyled li i {
            width: 20px;
            margin-right: 10px;
            color: var(--primary-light);
        }

        /* SOCIAL MEDIA STYLES */
        .social-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-social {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: var(--text-secondary);
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-social:hover {
            background: rgba(66, 165, 245, 0.1);
            border-color: var(--primary-light);
            color: var(--primary-light);
            transform: translateY(-2px);
        }

        /* COPYRIGHT STYLES */
        .footer .border-top {
            border-color: var(--card-border) !important;
        }

        .footer .text-muted {
            color: var(--text-secondary) !important;
        }

        .footer .text-warning {
            color: var(--primary-light) !important;
        }

        .footer a.text-muted {
            color: var(--text-secondary) !important;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a.text-muted:hover {
            color: var(--primary-light) !important;
        }

        /* TESTIMONIAL HISTORY STYLES */
        .testimonial-history {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .testimonial-history::-webkit-scrollbar {
            width: 6px;
        }

        .testimonial-history::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        .testimonial-history::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 3px;
        }

        .testimonial-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .testimonial-item:hover {
            border-color: var(--primary-light);
            transform: translateX(5px);
        }

        .testimonial-text {
            color: var(--text);
            line-height: 1.5;
            font-style: italic;
            margin-bottom: 10px;
        }

        .testimonial-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        .testimonial-rating {
            color: var(--warning);
        }

        .testimonial-status {
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: var(--warning);
        }

        .status-approved {
            background: rgba(34, 197, 94, 0.2);
            color: var(--success);
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        /* SUCCESS NOTIFICATION STYLES */
        .notification-container {
            position: fixed;
            top: 100px;
            right: 30px;
            z-index: 9999;
            max-width: 400px;
        }

        .notification {
            background: var(--card-bg);
            border: 1px solid var(--success);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: slideInRight 0.5s ease, slideOutRight 0.5s ease 4.5s forwards;
            position: relative;
            overflow: hidden;
        }

        .notification::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--success);
        }

        .notification-success {
            border-color: var(--success);
        }

        .notification-success::before {
            background: var(--success);
        }

        .notification-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .notification-icon {
            width: 30px;
            height: 30px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            font-size: 0.9rem;
        }

        .notification-title {
            color: var(--text);
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0;
        }

        .notification-message {
            color: var(--text-secondary);
            margin: 0;
            line-height: 1.5;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .footer {
                padding: 40px 0 20px;
            }
            
            .testimonial-form {
                padding: 20px;
            }
            
            .rating-input {
                gap: 10px;
            }
            
            .social-links {
                justify-content: center;
            }
            
            .btn-social {
                flex: 1;
                min-width: 120px;
                justify-content: center;
            }
            
            .notification-container {
                right: 15px;
                left: 15px;
                max-width: none;
            }
        }

        @media (max-width: 576px) {
            .footer .row > div {
                margin-bottom: 30px;
            }
            
            .testimonial-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .rating-input {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

<!-- Notifikasi Terima Kasih -->
<?php if ($show_thankyou): ?>
<div class="notification-container">
    <div class="notification notification-success">
        <div class="notification-header">
            <div class="notification-icon">
                <i class="fas fa-check"></i>
            </div>
            <h6 class="notification-title">Terima Kasih!</h6>
        </div>
        <p class="notification-message">
            Testimoni Anda telah berhasil dikirim. Kami sangat menghargai waktu dan masukan yang Anda berikan!
        </p>
    </div>
</div>
<?php endif; ?>

<!-- Footer dengan Form Testimonial -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <!-- Form Testimonial untuk Member -->
            <div class="col-lg-6 mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-edit me-2"></i>Beri Testimonial
                </h5>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $success_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $error_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="testimonial-form" id="testimonialForm">
                    <div class="mb-3">
                        <label for="testimoni" class="form-label">Testimoni Anda</label>
                        <textarea class="form-control" id="testimoni" name="testimoni" rows="4" 
                                  placeholder="Bagikan pengalaman Anda di Gym Arena..." 
                                  required><?= $form_submitted ? '' : (isset($_POST['testimoni']) ? htmlspecialchars($_POST['testimoni']) : '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Berikan Rating</label>
                        <div class="rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rating" 
                                           id="rating<?= $i ?>" value="<?= $i ?>" 
                                           <?= $form_submitted ? '' : ((isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : '') ?> required>
                                    <label class="form-check-label" for="rating<?= $i ?>">
                                        <?= $i ?> <i class="fas fa-star"></i>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_testimonial" class="btn btn-testimonial">
                        <i class="fas fa-paper-plane me-2"></i> Kirim Testimonial
                    </button>
                </form>
            </div>
            
            <!-- Informasi Gym -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-map-marker-alt me-2"></i>Informasi Kami
                </h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                       Jl. KH Shiddiq No.19-21 TalangSari, Jember
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2"></i>
                       +62 821-4308-0510
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        arenafitclubjbr22@gmail.com
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock me-2"></i>
                        Senin - Sabtu: 07:00 - 22:00 WIB
                        Minggu : 07.00-18.00 WIB
                    </li>
                </ul>
            </div>
            
            <!-- Social Media & Support -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-share-alt me-2"></i>Kontak Kami
                </h5>
                <div class="social-links mb-4">
                    <a href="https://www.instagram.com/arenafitclubjember?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" class="btn-social">
                        <i class="fab fa-instagram me-1"></i> @arenafitclubjember
                    </a>
                    
                    <a href="#" class="btn-social">
                        <i class="fas fa-envelope contact-icon email-icon"></i> arenafitclubjbr22@gmail.com
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="row border-top pt-3 mt-3">
            <div class="col-md-6">
                <p class="mb-0 text-muted">
                    &copy; 2025 <strong class="text-warning">Arena Gym Fit Club</strong>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-muted">
                    Welcome, <strong class="text-warning"><?= $_SESSION['user_name'] ?? 'Member' ?></strong>! |
                    <a href="#" class="text-muted">Privacy Policy</a> |
                    <a href="#" class="text-muted">Terms of Service</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript untuk handle form submission -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('testimonialForm');
    
    // Reset form fields setelah submit berhasil
    <?php if ($form_submitted): ?>
    setTimeout(function() {
        if (form) {
            form.reset();
        }
    }, 100);
    <?php endif; ?>
    
    // Auto-hide alerts setelah 5 detik
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
    
    // Auto-hide notification setelah 5 detik
    const notification = document.querySelector('.notification');
    if (notification) {
        setTimeout(function() {
            notification.style.display = 'none';
        }, 5000);
    }
    
    // Form submission handler untuk reset
    if (form) {
        form.addEventListener('submit', function() {
            // Form akan direset oleh PHP condition di atas
        });
    }
});
</script>

</body>
</html>