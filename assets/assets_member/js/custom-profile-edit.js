// Enhanced JavaScript untuk Edit Profile Page
document.addEventListener('DOMContentLoaded', function() {
    // Preview image sebelum upload
    const fileInput = document.querySelector('input[type="file"]');
    const profileImage = document.querySelector('.img-fluid.rounded-circle');
    
    if (fileInput && profileImage) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Tambahkan efek transisi saat mengganti gambar
                    profileImage.style.opacity = '0';
                    profileImage.style.transform = 'scale(0.8)';
                    
                    setTimeout(() => {
                        profileImage.src = e.target.result;
                        profileImage.style.opacity = '1';
                        profileImage.style.transform = 'scale(1)';
                        
                        // Tambahkan efek sukses
                        profileImage.style.animation = 'pulse 1s ease';
                    }, 300);
                };
                
                reader.readAsDataURL(file);
            }
        });
    }

    // Form validation enhancement
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                    
                    // Animasi shake untuk field yang error
                    input.style.animation = 'shake 0.5s ease';
                    setTimeout(() => {
                        input.style.animation = '';
                    }, 500);
                } else {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Harap lengkapi semua field yang wajib diisi.';
                errorDiv.style.animation = 'fadeInUp 0.5s ease';
                
                const existingAlert = form.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                form.insertBefore(errorDiv, form.firstChild);
                
                // Auto remove error message
                setTimeout(() => {
                    errorDiv.style.animation = 'fadeOut 0.5s ease';
                    setTimeout(() => errorDiv.remove(), 500);
                }, 5000);
            }
        });
    }

    // Real-time validation
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
        });
        
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.classList.add('is-valid');
            }
        });
    });

    // Add shake animation for errors
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .fade-out {
            animation: fadeOut 0.5s ease;
        }
    `;
    document.head.appendChild(style);
});

// Character counter untuk textarea
const textarea = document.querySelector('textarea[name="alamat"]');
if (textarea) {
    const counter = document.createElement('div');
    counter.className = 'text-muted text-right small mt-1';
    counter.textContent = `0/500 karakter`;
    
    textarea.parentNode.appendChild(counter);
    
    textarea.addEventListener('input', function() {
        const count = this.value.length;
        counter.textContent = `${count}/500 karakter`;
        
        if (count > 450) {
            counter.style.color = '#ff9800';
        } else if (count > 500) {
            counter.style.color = '#f44336';
        } else {
            counter.style.color = '#546e7a';
        }
    });
}