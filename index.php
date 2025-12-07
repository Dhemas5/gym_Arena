<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arena | Fit Club</title>
    <link rel="icon" type="image/png" href="assets/assets_admin/dist/img/logoadmin.png">

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="data/member/landingpage/assets/css/style.css">
  
</head>
<body>

  <?php include 'data/member/landingpage/header.php'; ?>

  <main>
    <?php
    // Include semua section
    include 'data/member/landingpage/sections/hero.php';
    include 'data/member/landingpage/sections/features.php';
    include 'data/member/landingpage/sections/about.php';
    include 'data/member/landingpage/sections/gallery.php';
    include 'data/member/landingpage/sections/testimonial.php';
    include 'data/member/landingpage/sections/classes.php';
    include 'data/member/landingpage/sections/membership.php';
    include 'data/member/landingpage/sections/trainers.php';
    include 'data/member/landingpage/sections/contact.php';
    ?>
  </main>

  <?php include 'data/member/landingpage/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
// Scroll Animations
document.addEventListener('DOMContentLoaded', function() {
  // Navbar Scroll Effect
  const navbar = document.getElementById('mainNavbar');
  const backToTop = document.getElementById('backToTop');
  
  // Scroll functions
  function handleScroll() {
    // Navbar background change on scroll
    if (window.scrollY > 100) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    
    // Back to top button visibility
    if (window.scrollY > 300) {
      backToTop.classList.add('show');
    } else {
      backToTop.classList.remove('show');
    }
  }
  
  // Scroll animation for elements
  function checkScroll() {
    const elements = document.querySelectorAll('.fade-in-up, .slide-in-left, .slide-in-right, .scale-in');
    
    elements.forEach(element => {
      const elementTop = element.getBoundingClientRect().top;
      const elementVisible = 150;
      
      if (elementTop < window.innerHeight - elementVisible) {
        element.classList.add('visible');
      }
    });
  }
  
  // Smooth scroll for navigation links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        // Update active nav link
        document.querySelectorAll('.nav-link').forEach(link => {
          link.classList.remove('active');
        });
        this.classList.add('active');
        
        // Smooth scroll
        window.scrollTo({
          top: targetElement.offsetTop - 80,
          behavior: 'smooth'
        });
      }
    });
  });
  
  // Back to top functionality
  backToTop.addEventListener('click', function() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
  
  // Update active nav link on scroll
  function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.clientHeight;
      
      if (scrollY >= sectionTop - 100) {
        current = section.getAttribute('id');
      }
    });
    
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === `#${current}`) {
        link.classList.add('active');
      }
    });
  }
  
  // Event listeners
  window.addEventListener('scroll', function() {
    handleScroll();
    checkScroll();
    updateActiveNavLink();
  });
  
  // Initial check
  handleScroll();
  checkScroll();
  updateActiveNavLink();
});
</script>
</body>
</html>