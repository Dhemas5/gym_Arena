<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Member - Arena FIT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../landingpage/assets/css/registration.css">
</head>
<body>
    <div class="registration-container">
        <!-- Header -->
        <div class="header-section">
            <img src="../../../assets/assets_admin/dist/img/logo.jpg" alt="Arena FIT Logo">
            <h1>Pendaftaran <span class="text-primary">Member</span></h1>
            <p>Daftar sekarang dan mulai perjalanan fitness Anda</p>
        </div>

        <!-- Step Indicator - Hanya 2 Step -->
        <div class="step-indicator">
            <div class="step active">
                <div class="step-circle">1</div>
                <span>Data Diri</span>
            </div>
            <span class="step-arrow">→</span>
            <div class="step">
                <div class="step-circle">2</div>
                <span>Verifikasi Email</span>
            </div>
        </div>

        <!-- Form Data Diri -->
        <div class="section-card">
            <h2 class="section-title">Data Diri</h2>

            <div class="alert alert-info">
                💡 Setelah mengisi data diri, kami akan mengirimkan kode verifikasi ke email Anda. Setelah verifikasi berhasil, Anda langsung dapat mengakses dashboard member.
            </div>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php endif; ?>

            <form method="POST" id="registrationForm">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan username" required
                            value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">✉️</span>
                        <input type="email" name="email" class="form-control" placeholder="contoh@email.com" required
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">No. Telepon *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📱</span>
                        <input type="tel" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" required
                            value="<?= isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password *</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Ulangi password" required>
                        <button type="button" class="password-toggle" id="toggleConfirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordMatchMessage" style="margin-top: 8px; font-size: 0.9rem;"></div>
                </div>

                <button type="submit" name="registerbtn" class="btn-primary">
                    Daftar & Verifikasi Email
                </button>
            </form>

            <div class="links">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordMatchMessage = document.getElementById('passwordMatchMessage');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password strength indicator
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check password length
            if (password.length >= 6) strength += 25;
            if (password.length >= 8) strength += 25;
            
            // Check for uppercase letters
            if (/[A-Z]/.test(password)) strength += 25;
            
            // Check for numbers and special characters
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 10;
            
            // Update strength bar
            passwordStrengthBar.style.width = strength + '%';
            
            // Update color based on strength
            if (strength < 50) {
                passwordStrengthBar.className = 'password-strength-bar strength-weak';
            } else if (strength < 80) {
                passwordStrengthBar.className = 'password-strength-bar strength-medium';
            } else {
                passwordStrengthBar.className = 'password-strength-bar strength-strong';
            }
        });

        // Password confirmation check
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword === '') {
                passwordMatchMessage.textContent = '';
                passwordMatchMessage.style.color = '';
            } else if (password === confirmPassword) {
                passwordMatchMessage.textContent = '✓ Password cocok';
                passwordMatchMessage.style.color = '#4caf50';
            } else {
                passwordMatchMessage.textContent = '✗ Password tidak cocok';
                passwordMatchMessage.style.color = '#f44336';
            }
        });

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                confirmPasswordInput.focus();
            }
        });
    </script>
</body>
</html>
