<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arena FIT - Gym and Class</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Logo Styles */
        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-img {
            height: 50px;
            width: auto;
            object-fit: contain;
            transition: all 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .brand-text-container {
            display: flex;
            flex-direction: column;
            margin-left: 12px;
        }

        .brand-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
            line-height: 1.2;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .brand-subtitle {
            font-size: 0.8rem;
            color: #f0f0f0;
            line-height: 1;
            letter-spacing: 0.5px;
        }

        /* Navbar styling */
        #mainNavbar {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.2);
            padding: 12px 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover .logo-img {
            transform: scale(1.05);
        }

        .navbar-brand:hover .brand-text {
            color: #1565c0;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin: 0 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #1565c0 !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #1565c0;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Button dengan warna #1565c0 */
        .btn-danger {
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            border: none;
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(21, 101, 192, 0.3);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #0d47a1 0%, #08306b 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(21, 101, 192, 0.4);
        }

        /* Back to top button dengan warna #1565c0 */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(21, 101, 192, 0.4);
            z-index: 1000;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: linear-gradient(135deg, #0d47a1 0%, #08306b 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(21, 101, 192, 0.6);
        }

        /* Hero section untuk demo */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding-top: 80px;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* Button di hero section */
        .hero .btn {
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            border: none;
            border-radius: 30px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(21, 101, 192, 0.4);
        }

        .hero .btn:hover {
            background: linear-gradient(135deg, #0d47a1 0%, #08306b 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(21, 101, 192, 0.6);
        }

        /* Responsive design untuk logo */
        @media (max-width: 768px) {
            .logo-img {
                height: 40px;
            }
            
            .brand-text {
                font-size: 1.3rem;
            }
            
            .brand-subtitle {
                font-size: 0.75rem;
            }
            
            .navbar-brand {
                margin-right: 0;
            }
        }

        @media (max-width: 576px) {
            .logo-img {
                height: 35px;
            }
            
            .brand-text {
                font-size: 1.2rem;
            }
            
            .brand-subtitle {
                font-size: 0.7rem;
            }
            
            .btn-danger {
                padding: 6px 16px;
                font-size: 0.9rem;
            }
        }

        /* Optional: Jika ingin logo lebih compact di mobile */
        @media (max-width: 400px) {
            .brand-subtitle {
                display: none;
            }
            
            .brand-text-container {
                margin-left: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <div class="logo-container">
                    <img src="assets/assets_admin/dist/img/logoadmin.png" alt="Arena FIT Logo" class="logo-img">
                </div>
                <div class="brand-text-container">
                    <span class="brand-text">Arena FIT</span>
                    <span class="brand-subtitle">Gym and Class</span>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">Tentang Kami</a></li>
                    <li class="nav-item"><a class="nav-link" href="#classes">Kelas & Jadwal</a></li>
                    <li class="nav-item"><a class="nav-link" href="#membership">Membership</a></li>
                    <li class="nav-item"><a class="nav-link" href="#trainers">Instruktur</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Kontak</a></li>
                </ul>
                <a href="data/member/login/login.php" class="btn btn-danger ms-3">Daftar Member</a>
            </div>
        </div>
    </nav>
    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m18 15-6-6-6 6"/>
        </svg>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Back to top button functionality
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('mainNavbar');
            if (window.scrollY > 50) {
                navbar.style.padding = '8px 0';
                navbar.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            } else {
                navbar.style.padding = '12px 0';
                navbar.style.boxShadow = '0 2px 15px rgba(0,0,0,0.2)';
            }
        });
    </script>