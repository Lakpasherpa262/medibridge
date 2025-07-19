<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - MediBridge</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <style>
    :root {
      --primary-color: #2a7fba;
      --secondary-color: #3bb77e;
      --accent-color: #ff7e33;
      --dark-color: #253d4e;
      --light-color: #f7f8fa;
      --text-color: #6c757d;
      --heading-color: #253d4e;
      --white: #ffffff;
      --border-radius: 8px;
      --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      --transition: all 0.3s ease;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text-color);
      background-color: var(--white);
      overflow-x: hidden;
      line-height: 1.6;
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
      color: var(--heading-color);
      font-weight: 700;
    }
    
    a {
      text-decoration: none;
      transition: var(--transition);
    }
    
    /* Navigation */
    .navbar {
      background-color: var(--white);
      box-shadow: 0 2px 30px rgba(0, 0, 0, 0.08);
      padding: 15px 0;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .logo-container {
      display: flex;
      align-items: center;
    }

    .logo-img {
      height: 42px;
      width: auto;
      margin-right: 12px;
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 26px;
      color: var(--primary-color) !important;
    }

    .navbar-brand span {
      color: var(--secondary-color);
    }

    .nav-link {
      font-weight: 500;
      color: var(--dark-color) !important;
      padding: 8px 16px !important;
      transition: var(--transition);
      position: relative;
    }

    .nav-link:hover, .nav-link.active {
      color: var(--primary-color) !important;
    }
    
    .nav-link.active:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 16px;
      width: calc(100% - 32px);
      height: 2px;
      background: var(--primary-color);
    }
    
    /* Hero Section */
    .about-hero {
      background: linear-gradient(rgba(42, 127, 186, 0.9), rgba(59, 183, 126, 0.9)), 
                  url('https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
      background-size: cover;
      background-position: center;
      padding: 120px 0;
      text-align: center;
      color: var(--white);
      position: relative;
    }
    
    .about-hero-title {
      font-size: 42px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.3;
    }
    
    .about-hero-subtitle {
      font-size: 18px;
      max-width: 700px;
      margin: 0 auto 30px;
      opacity: 0.9;
    }
    
    /* Section Styling */
    .section-padding {
      padding: 80px 0;
    }
    
    .section-title {
      position: relative;
      margin-bottom: 50px;
      text-align: center;
    }
    
    .section-title h2 {
      font-size: 32px;
      margin-bottom: 15px;
    }
    
    .section-title p {
      color: var(--text-color);
      max-width: 700px;
      margin: 0 auto;
    }
    
    .section-title:after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background: var(--secondary-color);
      border-radius: 2px;
    }
    
    /* Mission Section */
    .mission-box {
      background-color: var(--white);
      border-radius: var(--border-radius);
      padding: 40px;
      margin-bottom: 30px;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      border-left: 4px solid var(--primary-color);
      height: 100%;
    }
    
    .mission-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .mission-box h3 {
      font-size: 22px;
      margin-bottom: 15px;
      color: var(--primary-color);
    }
    
    /* Team Section */
    .team-card {
      background: var(--white);
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      margin-bottom: 30px;
      border: none;
    }
    
    .team-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .team-img {
      width: 100%;
      height: 280px;
      object-fit: cover;
      transition: var(--transition);
    }
    
    .team-card:hover .team-img {
      transform: scale(1.05);
    }
    
    .team-content {
      padding: 25px;
      text-align: center;
    }
    
    .team-name {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 5px;
      color: var(--dark-color);
    }
    
    .team-position {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 15px;
      display: block;
      font-size: 15px;
    }
    
    .team-social {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-top: 20px;
    }
    
    .team-social a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      background: rgba(59, 183, 126, 0.1);
      border-radius: 50%;
      color: var(--secondary-color);
      transition: var(--transition);
    }
    
    .team-social a:hover {
      background: var(--secondary-color);
      color: var(--white);
      transform: translateY(-3px);
    }
    
    /* Partners Section */
    .partner-card {
      background: var(--white);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      margin-bottom: 30px;
      padding: 20px;
      text-align: center;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .partner-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .partner-img {
      height: 80px;
      width: auto;
      object-fit: contain;
      margin-bottom: 20px;
      filter: grayscale(100%);
      opacity: 0.8;
      transition: var(--transition);
    }
    
    .partner-card:hover .partner-img {
      filter: grayscale(0);
      opacity: 1;
    }
    
    .partner-name {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 5px;
      color: var(--dark-color);
    }
    
    .partner-location {
      color: var(--text-color);
      font-size: 14px;
    }
    
    /* Delivery Section */
    .delivery-card {
      background: var(--white);
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      margin-bottom: 30px;
    }
    
    .delivery-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .delivery-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    
    .delivery-content {
      padding: 20px;
      text-align: center;
    }
    
    .delivery-name {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 5px;
      color: var(--dark-color);
    }
    
    .delivery-area {
      color: var(--text-color);
      font-size: 14px;
    }
    
    /* Footer */
    .footer {
      background: var(--dark-color);
      color: var(--white);
      padding: 70px 0 30px;
    }
    
    .footer-title {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 25px;
      color: var(--white);
      position: relative;
      padding-bottom: 10px;
    }
    
    .footer-title:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 2px;
      background: var(--secondary-color);
    }
    
    .footer-links {
      list-style: none;
      padding: 0;
    }
    
    .footer-links li {
      margin-bottom: 12px;
    }
    
    .footer-links a {
      color: rgba(255, 255, 255, 0.7);
      transition: var(--transition);
    }
    
    .footer-links a:hover {
      color: var(--white);
      padding-left: 5px;
    }
    
    .social-links {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }
    
    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      color: var(--white);
      transition: var(--transition);
    }
    
    .social-links a:hover {
      background: var(--primary-color);
      transform: translateY(-3px);
    }
    
    .copyright {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 30px;
      margin-top: 50px;
      text-align: center;
      color: rgba(255, 255, 255, 0.7);
      font-size: 14px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1199.98px) {
      .about-hero-title {
        font-size: 38px;
      }
    }
    
    @media (max-width: 991.98px) {
      .about-hero {
        padding: 100px 0;
      }
      
      .section-padding {
        padding: 70px 0;
      }
      
      .section-title h2 {
        font-size: 28px;
      }
    }
    
    @media (max-width: 767.98px) {
      .about-hero-title {
        font-size: 32px;
      }
      
      .about-hero-subtitle {
        font-size: 16px;
      }
      
      .navbar-brand {
        font-size: 22px;
      }
      
      .logo-img {
        height: 36px;
      }
    }
    
    @media (max-width: 575.98px) {
      .about-hero-title {
        font-size: 28px;
      }
      
      .mission-box {
        padding: 30px;
      }
      
      .team-img {
        height: 240px;
      }
    }
  </style>
</head>
<body>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
      <div class="logo-container">
        <img src="../images/logo.png" alt="MediBridge Logo" class="logo-img">  
        <a class="navbar-brand" href="index.php">Medi<span>Bridge</span></a>
      </div>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="../user.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="blog.php">Blog</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="about.php">About Us</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Our Story & Mission -->
  <section class="section-padding">
    <div class="container">
      <div class="section-title">
        
            <div class="row mt-4">
              <div class="col-md-6">
                <h3 class="mb-3">Our Mission</h3>
                <p>To bridge the gap between healthcare providers and communities by delivering accessible, affordable, and reliable medication services combined with expert health information.</p>
              </div>
              <div class="col-md-6">
                <h3 class="mb-3">Our Vision</h3>
                <p>A world where quality healthcare is within reach for everyone, regardless of location or circumstance.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Our Team -->
<section class="team-section py-5">
  <div class="container">
    <div class="row">
      <!-- Team Member 1 -->
      <div class="col-md-6 mb-4">
        <div class="team-card text-center">
          <img src="../images/lakpa.jpg" alt="Lakpa Dendi Sherpa" class="team-img mb-3">
          <div class="team-content">
            <h3 class="team-name">Lakpa Dendi Sherpa</h3>
            <span class="team-position">Frontend Developer</span>
          </div>
        </div>
      </div>

      <!-- Team Member 2 -->
      <div class="col-md-6 mb-4">
        <div class="team-card text-center">
          <img src="../images/sang.jpg" alt="SangDoma Lama" class="team-img mb-3">
          <div class="team-content">
            <h3 class="team-name">SangDoma Lama</h3>
            <span class="team-position">Backend Developer</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

  <!-- Partner Pharmacies -->
  <section class="section-padding">
    <div class="container">
      <div class="section-title">
        <h2>Listed Pharmacies</h2>

      </div>
      <div class="row">
        <!-- Pharmacy 1 -->
        <div class="col-lg-3 col-md-6">
          <div class="partner-card">
            <img src="https://cdn-icons-png.flaticon.com/512/206/206853.png" 
                 alt="City Health Pharmacy" class="partner-img">
            <div class="partner-content">
              <h3 class="partner-name">City Health Pharmacy</h3>
              <p class="partner-location">Downtown, Kathmandu</p>
            </div>
          </div>
        </div>
        
        <!-- Pharmacy 2 -->
        <div class="col-lg-3 col-md-6">
          <div class="partner-card">
            <img src="../images/ganesh.jpg"> 
                 alt="Green Valley Meds" class="partner-img">
            <div class="partner-content">
              <h3 class="partner-name">Green Valley Meds</h3>
              <p class="partner-location">Pokhara</p>
            </div>
          </div>
        </div>
        
    </div>
  </section>

  
  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">MediBridge</h3>
          <p>Bridging the gap between pharmacies and communities with accessible, affordable healthcare solutions.</p>
          <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Quick Links</h3>
          <ul class="footer-links">
            <li><a href="../user.php">Home</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="about.php">About Us</a></li>
          </ul>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Contact Us</h3>
          <ul class="footer-links">
            <li><i class="fas fa-map-marker-alt me-2"></i> 123 Health Street, Kathmandu, Nepal</li>
            <li><i class="fas fa-phone-alt me-2"></i> +977 9876543210</li>
            <li><i class="fas fa-envelope me-2"></i> info@medibridge.com</li>
          </ul>
        </div>
      </div>
      <div class="copyright">
        <p class="mb-0">&copy; 2025 MediBridge. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>