<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
  <!-- Include Header -->
  <div id="header">
    <?php include 'templates/header.php'; ?>
  </div>

  <!-- Include Navigation -->
  <div id="nav">
    <?php include 'templates/nav.php'; ?>
  </div>

  <!-- Main Content -->
  <main class="container mt-4">
    <h1>Shop Owner Demo Page
	</h1>
  </main>

  <!-- Include Footer -->
  <div id="footer">
    <?php include 'templates/footer.php'; ?>
  </div>
</body>
</html>
