<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arena FIT - Gym and Class</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  
</head>
<body>

  <?php include 'header.php'; ?>

  <main>
    <?php
    // Include semua section
    include 'sections/hero.php';
    include 'sections/features.php';
    include 'sections/about.php';
    include 'sections/gallery.php';
    include 'sections/testimonial.php';
    include 'sections/classes.php';
    include 'sections/membership.php';
    include 'sections/trainers.php';
    include 'sections/blog.php';
    include 'sections/contact.php';
    ?>
  </main>

  <?php include 'footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>