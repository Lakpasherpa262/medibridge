<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge - Health & Wellness Blog</title>
  <meta name="description" content="Expert health advice, medical insights, and wellness tips from MediBridge professionals">
  
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
      --text-color: #7e7e7e;
      --heading-color: #253d4e;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text-color);
      background-color: #ffffff;
      overflow-x: hidden;
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
      color: var(--heading-color);
      font-weight: 600;
    }
    
    .navbar {
      background-color: white;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
      padding: 15px 0;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 28px;
      color: var(--primary-color) !important;
      margin-left: 10px;
    }
    
    .navbar-brand span {
      color: var(--secondary-color);
    }
    
    .nav-link {
      font-weight: 500;
      color: var(--dark-color) !important;
      padding: 8px 15px !important;
      transition: all 0.3s ease;
    }
    
    .nav-link:hover, .nav-link.active {
      color: var(--primary-color) !important;
    }
    
    /* Hero Section */
    .blog-hero {
      background: linear-gradient(135deg, rgba(42, 127, 186, 0.1) 0%, rgba(59, 183, 126, 0.1) 100%);
      padding: 100px 0 60px;
      position: relative;
      margin-bottom: 60px;
    }
    
    .blog-hero-title {
      font-size: 48px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.2;
      color: var(--dark-color);
    }
    
    .blog-hero-subtitle {
      font-size: 18px;
      margin-bottom: 30px;
      color: var(--text-color);
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
    }
    
    /* Blog Content */
    .blog-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    .section-title {
      position: relative;
      margin-bottom: 50px;
      text-align: center;
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
    }
    
    /* Blog Grid */
    .blog-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 30px;
      margin-bottom: 60px;
    }
    
    .blog-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      border: 1px solid rgba(0, 0, 0, 0.03);
    }
    
    .blog-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .blog-image {
      height: 220px;
      width: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
    
    .blog-card:hover .blog-image {
      transform: scale(1.05);
    }
    
    .blog-content {
      padding: 25px;
    }
    
    .blog-category {
      display: inline-block;
      background: rgba(59, 183, 126, 0.1);
      color: var(--secondary-color);
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .blog-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 15px;
      line-height: 1.4;
      color: var(--dark-color);
    }
    
    .blog-excerpt {
      color: var(--text-color);
      margin-bottom: 20px;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .blog-meta {
      display: flex;
      align-items: center;
      font-size: 14px;
      color: #999;
      margin-bottom: 20px;
    }
    
    .blog-meta i {
      margin-right: 5px;
      color: var(--primary-color);
    }
    
    .blog-meta span {
      margin-right: 15px;
    }
    
    .read-more {
      display: inline-flex;
      align-items: center;
      color: var(--primary-color);
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .read-more i {
      margin-left: 8px;
      transition: transform 0.3s ease;
    }
    
    .read-more:hover {
      color: var(--secondary-color);
    }
    
    .read-more:hover i {
      transform: translateX(5px);
    }
    
    /* Featured Post */
    .featured-post {
      margin-bottom: 60px;
    }
    
    .featured-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      display: flex;
      flex-direction: column;
    }
    
    @media (min-width: 992px) {
      .featured-card {
        flex-direction: row;
        height: 400px;
      }
    }
    
    .featured-image {
      height: 300px;
      width: 100%;
      object-fit: cover;
    }
    
    @media (min-width: 992px) {
      .featured-image {
        height: 100%;
        width: 50%;
      }
    }
    
    .featured-content {
      padding: 30px;
      flex: 1;
    }
    
    .featured-category {
      display: inline-block;
      background: rgba(255, 126, 51, 0.1);
      color: var(--accent-color);
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .featured-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.3;
      color: var(--dark-color);
    }
    
    .featured-excerpt {
      color: var(--text-color);
      margin-bottom: 25px;
      font-size: 16px;
      line-height: 1.7;
    }
    
    /* Article Modal */
    .article-modal .modal-content {
      border-radius: 15px;
      overflow: hidden;
      border: none;
    }
    
    .article-modal .modal-header {
      border-bottom: none;
      padding-bottom: 0;
      position: relative;
    }
    
    .article-modal .modal-header .btn-close {
      position: absolute;
      right: 20px;
      top: 20px;
      background: rgba(0, 0, 0, 0.8);
      border-radius: 50%;
      padding: 10px;
      z-index: 1;
      opacity: 1;
    }
    
    .article-modal .modal-header .btn-close::before {
      color: white;
    }
    
    .article-modal .modal-body {
      padding: 0;
    }
    
    .article-modal .article-image {
      width: 100%;
      height: 300px;
      object-fit: cover;
    }
    
    .article-modal .article-content {
      padding: 30px;
    }
    
    .article-modal .article-category {
      display: inline-block;
      background: rgba(59, 183, 126, 0.1);
      color: var(--secondary-color);
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .article-modal .article-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.3;
      color: var(--dark-color);
    }
    
    .article-modal .article-meta {
      display: flex;
      align-items: center;
      font-size: 14px;
      color: #999;
      margin-bottom: 25px;
    }
    
    .article-modal .article-meta i {
      margin-right: 5px;
      color: var(--primary-color);
    }
    
    .article-modal .article-meta span {
      margin-right: 15px;
    }
    
    .article-modal .article-text {
      color: var(--text-color);
      line-height: 1.8;
      margin-bottom: 25px;
    }
    
    .article-modal .article-text p {
      margin-bottom: 20px;
    }
    
    /* Footer */
    .footer {
      background: var(--dark-color);
      color: white;
      padding: 60px 0 30px;
    }
    
    .footer-title {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 25px;
      color: white;
    }
    
    .footer-links {
      list-style: none;
      padding: 0;
    }
    
    .footer-links li {
      margin-bottom: 10px;
    }
    
    .footer-links a {
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .footer-links a:hover {
      color: white;
      padding-left: 5px;
    }
    
    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      color: white;
      margin-right: 10px;
      transition: all 0.3s ease;
    }
    
    .social-links a:hover {
      background: var(--primary-color);
      transform: translateY(-3px);
    }
    
    .copyright {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 30px;
      margin-top: 30px;
      text-align: center;
      color: rgba(255, 255, 255, 0.7);
    }
    
    /* Logo container styles */
    .logo-container {
      display: flex;
      align-items: center;
    }
    
    .logo-img {
      height: 40px;
      width: auto;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1199.98px) {
      .blog-hero-title {
        font-size: 42px;
      }
      
      .featured-title, .article-modal .article-title {
        font-size: 24px;
      }
    }
    
    @media (max-width: 991.98px) {
      .blog-hero {
        padding: 80px 0 50px;
      }
      
      .blog-hero-title {
        font-size: 36px;
      }
      
      .blog-hero-subtitle {
        font-size: 16px;
      }
      
      .featured-content {
        padding: 25px;
      }
    }
    
    @media (max-width: 767.98px) {
      .blog-hero {
        padding: 70px 0 40px;
        margin-bottom: 40px;
      }
      
      .blog-hero-title {
        font-size: 32px;
      }
      
      .blog-grid {
        grid-template-columns: 1fr;
        gap: 25px;
      }
      
      .featured-title, .article-modal .article-title {
        font-size: 22px;
      }
      
      .article-modal .article-image {
        height: 200px;
      }
    }
    
    @media (max-width: 575.98px) {
      .blog-hero-title {
        font-size: 28px;
      }
      
      .featured-card {
        height: auto;
      }
      
      .featured-image {
        height: 250px;
        width: 100%;
      }
      
      .article-modal .article-content {
        padding: 20px;
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
            <a class="nav-link active" href="blog.php">Blog</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="blog-hero">
    <div class="container text-center">
      <h1 class="blog-hero-title">MediBridge Health & Wellness Blog</h1>
      <p class="blog-hero-subtitle">Expert insights, medical advice, and wellness tips from our team of healthcare professionals to help you live your healthiest life</p>
    </div>
  </section>

  <!-- Blog Content -->
  <div class="blog-container">
    <!-- Featured Post -->
    <section class="featured-post">
      <div class="featured-card">
        <img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
             alt="Featured Post" class="featured-image">
        <div class="featured-content">
          <span class="featured-category">WELLNESS</span>
          <h2 class="featured-title">10 Science-Backed Strategies for Better Sleep and Improved Health</h2>
          <p class="featured-excerpt">Discover the latest research on sleep hygiene and learn practical techniques to improve your sleep quality, boost energy levels, and enhance overall wellbeing. Our sleep specialist shares evidence-based recommendations that can transform your nightly routine.</p>
          <a class="read-more" data-bs-toggle="modal" data-bs-target="#articleModal1">Read Full Article <i class="fas fa-arrow-right"></i></a>
          <div class="blog-meta mt-4">
            <span><i class="far fa-calendar-alt"></i> June 15, 2023</span>
            <span><i class="far fa-user"></i> Dr. Sarah Johnson</span>
            <span><i class="far fa-clock"></i> 8 min read</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Blog Grid -->
    <section>
      <h2 class="section-title">Latest Articles</h2>
      <div class="blog-grid">
        <!-- Blog Post 1 -->
        <article class="blog-card">
          <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               alt="Nutrition Tips" class="blog-image">
          <div class="blog-content">
            <span class="blog-category">NUTRITION</span>
            <h3 class="blog-title">The Mediterranean Diet: Benefits and Simple Ways to Get Started</h3>
            <p class="blog-excerpt">Learn why the Mediterranean diet is consistently ranked as one of the healthiest eating patterns and discover easy ways to incorporate its principles into your daily meals. This heart-healthy approach emphasizes fruits, vegetables, whole grains, and healthy fats.</p>
            <a class="read-more" data-bs-toggle="modal" data-bs-target="#articleModal2">Read More <i class="fas fa-arrow-right"></i></a>
            <div class="blog-meta">
              <span><i class="far fa-calendar-alt"></i> June 12, 2023</span>
              <span><i class="far fa-user"></i> Dr. Michael Chen</span>
            </div>
          </div>
        </article>
        
        <!-- Blog Post 2 -->
        <article class="blog-card">
          <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               alt="Mental Health" class="blog-image">
          <div class="blog-content">
            <span class="blog-category">MENTAL HEALTH</span>
            <h3 class="blog-title">Managing Stress in Uncertain Times: Practical Coping Strategies</h3>
            <p class="blog-excerpt">Explore effective techniques to reduce stress and anxiety, from mindfulness exercises to cognitive behavioral approaches that can help restore emotional balance. Learn how to recognize stress triggers and build resilience in challenging situations.</p>
            <a class="read-more" data-bs-toggle="modal" data-bs-target="#articleModal3">Read More <i class="fas fa-arrow-right"></i></a>
            <div class="blog-meta">
              <span><i class="far fa-calendar-alt"></i> June 8, 2023</span>
              <span><i class="far fa-user"></i> Dr. Emily Rodriguez</span>
            </div>
          </div>
        </article>
        
        <!-- Blog Post 3 -->
        <article class="blog-card">
          <img src="https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               alt="Exercise Routine" class="blog-image">
          <div class="blog-content">
            <span class="blog-category">FITNESS</span>
            <h3 class="blog-title">The 20-Minute Workout: Maximizing Results With Minimal Time</h3>
            <p class="blog-excerpt">Short on time? Our fitness expert reveals how to structure efficient workouts that deliver maximum benefits in just 20 minutes, backed by exercise science research. Includes HIIT routines, strength training circuits, and recovery techniques.</p>
            <a class="read-more" data-bs-toggle="modal" data-bs-target="#articleModal4">Read More <i class="fas fa-arrow-right"></i></a>
            <div class="blog-meta">
              <span><i class="far fa-calendar-alt"></i> June 5, 2023</span>
              <span><i class="far fa-user"></i> Dr. James Wilson</span>
            </div>
          </div>
        </article>
        
        <!-- Blog Post 4 -->
        <article class="blog-card">
          <img src="https://images.unsplash.com/photo-1505576399279-565b52d4ac71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               alt="Sleep Health" class="blog-image">
          <div class="blog-content">
            <span class="blog-category">SLEEP</span>
            <h3 class="blog-title">The Science of Sleep: How Quality Rest Impacts Your Health</h3>
            <p class="blog-excerpt">Sleep is foundational to health. Explore the latest neuroscience research on sleep cycles and learn how poor sleep affects your body and mind. Discover the connection between sleep and immunity, metabolism, and cognitive function.</p>
            <a class="read-more" data-bs-toggle="modal" data-bs-target="#articleModal5">Read More <i class="fas fa-arrow-right"></i></a>
            <div class="blog-meta">
              <span><i class="far fa-calendar-alt"></i> June 3, 2023</span>
              <span><i class="far fa-user"></i> Dr. Lisa Park</span>
            </div>
          </div>
        </article>
        
        <!-- Blog Post 5 -->
        <article class="blog-card">
          <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               alt="Holistic Healing" class="blog-image">
          <div class="blog-content">
            <span class="blog-category">WELLNESS</span>
            <h3 class="blog-title">Holistic Healing: Combining Traditional and Modern Medicine</h3>
            <p class="blog-excerpt">Explore how integrating traditional healing practices with modern medicine can provide comprehensive care. Learn about acupuncture, Ayurveda, herbal remedies, and their evidence-based benefits when combined with conventional treatments.</p>
            <a class="read-more" data-bs-toggle="modal" data-bs-target="#articleModal6">Read More <i class="fas fa-arrow-right"></i></a>
            <div class="blog-meta">
              <span><i class="far fa-calendar-alt"></i> May 28, 2023</span>
              <span><i class="far fa-user"></i> Dr. Priya Sharma</span>
            </div>
          </div>
        </article>
        
        <!-- Blog Post 6 -->
        <article class="blog-card">
          <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               alt="Preventive Care" class="blog-image">
          <div class="blog-content">
            <span class="blog-category">PREVENTIVE CARE</span>
            <h3 class="blog-title">Essential Health Screenings You Shouldn't Ignore at Any Age</h3>
            <p class="blog-excerpt">Early detection saves lives. Our comprehensive guide to age-appropriate health screenings helps you stay on top of preventive care with recommendations from experts. Includes timelines for blood tests, cancer screenings, and cardiovascular assessments.</p>
            <a class="read-more" data-bs-toggle="modal" data-bs-target="#articleModal7">Read More <i class="fas fa-arrow-right"></i></a>
            <div class="blog-meta">
              <span><i class="far fa-calendar-alt"></i> May 25, 2023</span>
              <span><i class="far fa-user"></i> Dr. Amanda Lee</span>
            </div>
          </div>
        </article>
      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">MediBridge</h3>
          <p>Bridging the gap between pharmacies and communities with accessible, affordable healthcare solutions.</p>
          <div class="social-links mt-4">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Quick Links</h3>
          <ul class="footer-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Blog Categories</h3>
          <ul class="footer-links">
            <li><a href="#">Nutrition</a></li>
            <li><a href="#">Fitness</a></li>
            <li><a href="#">Mental Health</a></li>
            <li><a href="#">Preventive Care</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Contact Us</h3>
          <ul class="footer-links">
            <li><i class="fas fa-map-marker-alt me-2"></i> 123 Health Street, Mumbai, India</li>
            <li><i class="fas fa-phone-alt me-2"></i> +91 98765 43210</li>
            <li><i class="fas fa-envelope me-2"></i> info@medibridge.com</li>
            <li><i class="fas fa-clock me-2"></i> Mon-Sun: 8AM - 10PM</li>
          </ul>
        </div>
      </div>
      <div class="copyright">
        <p class="mb-0">&copy; 2025 MediBridge. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Article Modals -->
  <!-- Featured Article Modal -->
  <div class="modal fade article-modal" id="articleModal1" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               class="article-image" alt="Sleep Strategies">
          <div class="article-content">
            <span class="article-category">WELLNESS</span>
            <h2 class="article-title">10 Science-Backed Strategies for Better Sleep and Improved Health</h2>
            <div class="article-meta">
              <span><i class="far fa-calendar-alt"></i> June 15, 2023</span>
              <span><i class="far fa-user"></i> Dr. Sarah Johnson</span>
              <span><i class="far fa-clock"></i> 8 min read</span>
            </div>
            <div class="article-text">
              <p>Quality sleep is fundamental to our physical health, mental wellbeing, and overall quality of life. Yet, in our fast-paced world, many people struggle with sleep disorders or simply don't get enough restorative sleep. This article presents 10 evidence-based strategies to help you improve your sleep quality and duration.</p>
              
              <h4>1. Establish a Consistent Sleep Schedule</h4>
              <p>Our bodies thrive on routine. Going to bed and waking up at the same time every day (even on weekends) helps regulate your body's internal clock. Research shows that maintaining a regular sleep schedule can improve sleep quality by up to 40%.</p>
              
              <h4>2. Create a Relaxing Bedtime Routine</h4>
              <p>Develop a 30-60 minute wind-down period before bed. This might include reading, gentle stretching, meditation, or a warm bath. Avoid stimulating activities like work or intense exercise during this time.</p>
              
              <h4>3. Optimize Your Sleep Environment</h4>
              <p>Keep your bedroom cool (around 65°F or 18°C), dark, and quiet. Consider blackout curtains, earplugs, or a white noise machine if needed. Invest in a comfortable mattress and pillows that support your preferred sleep position.</p>
              
              <h4>4. Limit Exposure to Blue Light Before Bed</h4>
              <p>The blue light emitted by electronic devices can suppress melatonin production, making it harder to fall asleep. Try to avoid screens for at least 1 hour before bedtime, or use blue light filters if you must use devices.</p>
              
              <h4>5. Be Mindful of Food and Drink</h4>
              <p>Avoid large meals, caffeine, and alcohol close to bedtime. While alcohol may help you fall asleep initially, it disrupts sleep later in the night. Opt for sleep-promoting snacks like almonds, bananas, or chamomile tea if needed.</p>
              
              <h4>6. Get Regular Exercise</h4>
              <p>Regular physical activity can help you fall asleep faster and enjoy deeper sleep. However, try to finish vigorous workouts at least 3 hours before bedtime as they can be stimulating.</p>
              
              <h4>7. Manage Stress and Anxiety</h4>
              <p>Practice relaxation techniques like deep breathing, progressive muscle relaxation, or mindfulness meditation to calm your mind before bed. Journaling can also help process thoughts that might otherwise keep you awake.</p>
              
              <h4>8. Limit Daytime Naps</h4>
              <p>While short power naps (20-30 minutes) can be beneficial, longer or late afternoon naps can interfere with nighttime sleep. If you must nap, try to do so before 3pm.</p>
              
              <h4>9. Get Sunlight Exposure During the Day</h4>
              <p>Natural light exposure, especially in the morning, helps regulate your circadian rhythm. Aim for at least 30 minutes of sunlight exposure daily.</p>
              
              <h4>10. Know When to Seek Professional Help</h4>
              <p>If you consistently struggle with sleep despite trying these strategies, consult a healthcare provider. You may have an underlying sleep disorder like insomnia or sleep apnea that requires treatment.</p>
              
              <p>Implementing even a few of these strategies can significantly improve your sleep quality. Remember that changes take time - be patient and consistent with your new sleep habits.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Article Modal 2 -->
  <div class="modal fade article-modal" id="articleModal2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="https://images.unsplash.com/photo-1498837167922-ddd27525d352?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               class="article-image" alt="Mediterranean Diet">
          <div class="article-content">
            <span class="article-category">NUTRITION</span>
            <h2 class="article-title">The Mediterranean Diet: Benefits and Simple Ways to Get Started</h2>
            <div class="article-meta">
              <span><i class="far fa-calendar-alt"></i> June 12, 2023</span>
              <span><i class="far fa-user"></i> Dr. Michael Chen</span>
              <span><i class="far fa-clock"></i> 6 min read</span>
            </div>
            <div class="article-text">
              <p>The Mediterranean diet is consistently ranked as one of the healthiest eating patterns in the world. Based on the traditional foods of countries bordering the Mediterranean Sea, this diet emphasizes whole, minimally processed foods and has been linked to numerous health benefits.</p>
              
              <h4>Key Components of the Mediterranean Diet</h4>
              <ul>
                <li><strong>Abundant plant foods:</strong> Fruits, vegetables, whole grains, legumes, nuts and seeds</li>
                <li><strong>Healthy fats:</strong> Olive oil as the primary fat source</li>
                <li><strong>Moderate fish and poultry:</strong> At least twice weekly for fish</li>
                <li><strong>Limited red meat:</strong> A few times per month at most</li>
                <li><strong>Dairy in moderation:</strong> Mainly cheese and yogurt</li>
                <li><strong>Herbs and spices:</strong> Used instead of salt to flavor foods</li>
                <li><strong>Red wine in moderation:</strong> Optional with meals (1 glass per day for women, 2 for men)</li>
              </ul>
              
              <h4>Health Benefits</h4>
              <p>Research has shown the Mediterranean diet can:</p>
              <ul>
                <li>Reduce risk of heart disease and stroke</li>
                <li>Lower blood pressure</li>
                <li>Improve cholesterol levels</li>
                <li>Support healthy weight management</li>
                <li>Reduce risk of type 2 diabetes</li>
                <li>Lower risk of certain cancers</li>
                <li>Support brain health and reduce Alzheimer's risk</li>
                <li>Increase longevity</li>
              </ul>
              
              <h4>Simple Ways to Adopt the Mediterranean Diet</h4>
              <ol>
                <li><strong>Make vegetables the star:</strong> Aim for 4-6 servings daily. Try roasted vegetables, big salads, or vegetable-based soups.</li>
                <li><strong>Switch to whole grains:</strong> Choose whole grain bread, pasta, and rice instead of refined versions.</li>
                <li><strong>Use olive oil:</strong> Replace butter and other fats with extra virgin olive oil for cooking and dressings.</li>
                <li><strong>Eat more seafood:</strong> Aim for at least two servings of fatty fish like salmon or sardines per week.</li>
                <li><strong>Snack on nuts:</strong> A handful of almonds, walnuts or pistachios makes a satisfying, healthy snack.</li>
                <li><strong>Enjoy fruit for dessert:</strong> Fresh fruit with a little yogurt or nuts is a perfect Mediterranean-style dessert.</li>
                <li><strong>Flavor with herbs and spices:</strong> Reduce salt and experiment with basil, oregano, garlic, and other Mediterranean flavors.</li>
                <li><strong>Share meals with others:</strong> The social aspect of eating is an important part of the Mediterranean lifestyle.</li>
              </ol>
              
              <p>Remember that the Mediterranean diet is more than just food - it's a lifestyle that includes regular physical activity, sharing meals with others, and enjoying food in a relaxed, mindful way.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Article Modal 3 -->
  <div class="modal fade article-modal" id="articleModal3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               class="article-image" alt="Stress Management">
          <div class="article-content">
            <span class="article-category">MENTAL HEALTH</span>
            <h2 class="article-title">Managing Stress in Uncertain Times: Practical Coping Strategies</h2>
            <div class="article-meta">
              <span><i class="far fa-calendar-alt"></i> June 8, 2023</span>
              <span><i class="far fa-user"></i> Dr. Emily Rodriguez</span>
              <span><i class="far fa-clock"></i> 7 min read</span>
            </div>
            <div class="article-text">
              <p>Stress is an inevitable part of life, especially during times of uncertainty and change. While we can't always control external circumstances, we can develop healthy ways to respond to stress. This article explores evidence-based strategies to help you build resilience and maintain emotional balance during challenging times.</p>
              
              <h4>Understanding Stress</h4>
              <p>Stress is the body's natural response to perceived threats or demands. In small doses, stress can be beneficial, helping us stay alert and motivated. However, chronic stress can take a toll on both physical and mental health, contributing to issues like anxiety, depression, digestive problems, headaches, heart disease, sleep problems, and weight gain.</p>
              
              <h4>Effective Stress Management Techniques</h4>
              
              <h5>1. Mindfulness and Meditation</h5>
              <p>Mindfulness practices help bring your attention to the present moment without judgment. Research shows that regular meditation can reduce stress, anxiety, and improve emotional wellbeing.</p>
              <p><strong>Try this:</strong> Start with just 5 minutes per day of focused breathing. Sit comfortably, close your eyes, and pay attention to your breath. When your mind wanders, gently bring it back to your breathing.</p>
              
              <h5>2. Cognitive Behavioral Techniques</h5>
              <p>Our thoughts influence our emotions and behaviors. Cognitive restructuring helps identify and challenge negative thought patterns that contribute to stress.</p>
              <p><strong>Try this:</strong> When you notice a stressful thought, ask yourself: Is this thought factual? Is it helpful? What's a more balanced way to view this situation?</p>
              
              <h5>3. Physical Activity</h5>
              <p>Exercise is a powerful stress reliever. It boosts endorphins (natural mood lifters), improves sleep, and increases self-confidence.</p>
              <p><strong>Try this:</strong> Aim for at least 30 minutes of moderate exercise most days. Even a brisk walk can help clear your mind and reduce tension.</p>
              
              <h5>4. Social Connection</h5>
              <p>Strong social support can buffer against stress. Sharing your concerns with trusted friends or family members can provide perspective and emotional relief.</p>
              <p><strong>Try this:</strong> Schedule regular check-ins with loved ones, even if just a quick phone call or video chat.</p>
              
              <h5>5. Time Management</h5>
              <p>Feeling overwhelmed often stems from poor time management. Prioritizing tasks and setting realistic goals can reduce stress.</p>
              <p><strong>Try this:</strong> Use the "ABCDE" method - label tasks as A (must do), B (should do), C (nice to do), D (delegate), and E (eliminate).</p>
              
              <h5>6. Relaxation Techniques</h5>
              <p>Progressive muscle relaxation, deep breathing exercises, and guided imagery can activate the body's relaxation response.</p>
              <p><strong>Try this:</strong> The 4-7-8 breathing technique: Inhale for 4 counts, hold for 7 counts, exhale for 8 counts. Repeat 4 times.</p>
              
              <h5>7. Healthy Lifestyle Choices</h5>
              <p>A balanced diet, adequate sleep, and limiting alcohol and caffeine can help your body better cope with stress.</p>
              
              <h4>When to Seek Professional Help</h4>
              <p>If stress becomes overwhelming or interferes with daily functioning, consider consulting a mental health professional. Therapy can provide additional tools and support for managing stress effectively.</p>
              
              <p>Remember that stress management is an ongoing practice, not a one-time fix. Experiment with different techniques to discover what works best for you, and be patient with yourself as you develop these skills.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Article Modal 4 -->
  <div class="modal fade article-modal" id="articleModal4" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               class="article-image" alt="20-Minute Workout">
          <div class="article-content">
            <span class="article-category">FITNESS</span>
            <h2 class="article-title">The 20-Minute Workout: Maximizing Results With Minimal Time</h2>
            <div class="article-meta">
              <span><i class="far fa-calendar-alt"></i> June 5, 2023</span>
              <span><i class="far fa-user"></i> Dr. James Wilson</span>
              <span><i class="far fa-clock"></i> 5 min read</span>
            </div>
            <div class="article-text">
              <p>In our busy lives, finding time for exercise can be challenging. However, research shows that short, intense workouts can be just as effective as longer sessions for improving cardiovascular health, building strength, and burning calories. This article presents science-backed strategies for getting maximum results from just 20 minutes of exercise.</p>
              
              <h4>The Science Behind Short Workouts</h4>
              <p>High-Intensity Interval Training (HIIT) has gained popularity because it delivers significant benefits in minimal time. Studies show that 20 minutes of HIIT can burn more calories than 50 minutes of steady-state cardio and continue burning calories for hours after the workout (the "afterburn effect").</p>
              
              <h4>Sample 20-Minute Workout Routines</h4>
              
              <h5>1. Full-Body HIIT Circuit</h5>
              <p>Perform each exercise for 40 seconds, rest 20 seconds between exercises. Repeat the circuit twice.</p>
              <ul>
                <li><strong>Jump squats:</strong> Build lower body strength and power</li>
                <li><strong>Push-ups:</strong> Strengthen chest, shoulders, and triceps</li>
                <li><strong>Mountain climbers:</strong> Boost heart rate and core engagement</li>
                <li><strong>Plank:</strong> Strengthen core muscles</li>
                <li><strong>Burpees:</strong> Full-body conditioning</li>
              </ul>
              
              <h5>2. Strength Training Supersets</h5>
              <p>Pair two exercises (one upper body, one lower body) and alternate between them with minimal rest. Complete 3 sets of 10-12 reps per exercise.</p>
              <ul>
                <li><strong>Dumbbell squats + Shoulder presses</strong></li>
                <li><strong>Lunges + Bent-over rows</strong></li>
                <li><strong>Glute bridges + Chest presses</strong></li>
              </ul>
              
              <h5>3. Tabata Protocol</h5>
              <p>20 seconds of maximum effort followed by 10 seconds of rest, repeated 8 times (4 minutes total). Choose one exercise like sprints, cycling, or jump rope and complete 5 Tabata rounds with 1 minute rest between.</p>
              
              <h4>Tips for Maximizing Short Workouts</h4>
              <ul>
                <li><strong>Focus on compound movements:</strong> Exercises that work multiple muscle groups simultaneously (like squats, push-ups, rows) give you more bang for your buck.</li>
                <li><strong>Increase intensity:</strong> Since time is limited, push yourself during the active periods.</li>
                <li><strong>Minimize rest periods:</strong> Keep transitions quick to maintain elevated heart rate.</li>
                <li><strong>Use proper form:</strong> With shorter workouts, every rep counts - make them quality movements.</li>
                <li><strong>Stay consistent:</strong> Aim for at least 4-5 short workouts per week for best results.</li>
              </ul>
              
              <h4>Recovery and Nutrition</h4>
              <p>Even with short workouts, recovery is important:</p>
              <ul>
                <li>Stay hydrated before, during, and after exercise</li>
                <li>Consume protein within 30 minutes post-workout to aid muscle recovery</li>
                <li>Include active recovery days with stretching or light activity</li>
                <li>Get adequate sleep for optimal recovery and performance</li>
              </ul>
              
              <p>Remember that while short workouts are effective, they shouldn't replace all longer sessions. Consider mixing in some longer workouts when possible, but know that when time is limited, a focused 20-minute workout can still deliver significant health and fitness benefits.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Article Modal 5 -->
  <div class="modal fade article-modal" id="articleModal5" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="https://images.unsplash.com/photo-1505576399279-565b52d4ac71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               class="article-image" alt="Sleep Science">
          <div class="article-content">
            <span class="article-category">SLEEP</span>
            <h2 class="article-title">The Science of Sleep: How Quality Rest Impacts Your Health</h2>
            <div class="article-meta">
              <span><i class="far fa-calendar-alt"></i> June 3, 2023</span>
              <span><i class="far fa-user"></i> Dr. Lisa Park</span>
              <span><i class="far fa-clock"></i> 8 min read</span>
            </div>
            <div class="article-text">
              <p>Sleep is not merely a passive state of rest, but an active and complex physiological process essential for our physical health, mental wellbeing, and cognitive function. Recent advances in neuroscience have revealed just how profoundly sleep affects nearly every system in our body. This article explores the fascinating science behind sleep and its critical role in maintaining optimal health.</p>
              
              <h4>The Sleep Cycle: Understanding the Stages</h4>
              <p>Sleep occurs in cycles of approximately 90 minutes, each consisting of four stages:</p>
              <ol>
                <li><strong>NREM Stage 1:</strong> The transition from wakefulness to sleep (1-5 minutes)</li>
                <li><strong>NREM Stage 2:</strong> Light sleep where body temperature drops and heart rate slows (10-25 minutes)</li>
                <li><strong>NREM Stage 3:</strong> Deep sleep crucial for physical restoration and growth (20-40 minutes)</li>
                <li><strong>REM Sleep:</strong> When most dreaming occurs, important for memory and learning (10-60 minutes)</li>
              </ol>
              
              <h4>The Vital Functions of Sleep</h4>
              
              <h5>1. Cognitive Function and Memory</h5>
              <p>During sleep, especially REM sleep, the brain consolidates memories and processes information from the day. Studies show that sleep enhances learning, problem-solving skills, and creativity. Sleep deprivation, on the other hand, impairs attention, alertness, concentration, reasoning, and problem-solving.</p>
              
              <h5>2. Physical Health and Repair</h5>
              <p>Deep sleep triggers the release of growth hormone, which helps repair cells and tissues. This is when muscle repair, bone building, and immune system strengthening occur. Chronic sleep deprivation is linked to increased risk of obesity, diabetes, cardiovascular disease, and infections.</p>
              
              <h5>3. Emotional Regulation</h5>
              <p>Sleep helps regulate emotions by modulating activity in the amygdala (the brain's emotional center) and prefrontal cortex (responsible for rational thinking). Poor sleep is strongly associated with increased emotional reactivity, anxiety, and depression.</p>
              
              <h5>4. Metabolic Health</h5>
              <p>Sleep affects hormones that regulate hunger (ghrelin) and fullness (leptin). Insufficient sleep increases cravings for high-calorie foods and reduces insulin sensitivity, contributing to weight gain and metabolic disorders.</p>
              
              <h5>5. Detoxification</h5>
              <p>The glymphatic system, the brain's waste clearance system, is most active during sleep. This process removes toxic byproducts that accumulate during waking hours, including beta-amyloid proteins associated with Alzheimer's disease.</p>
              
              <h4>Consequences of Sleep Deprivation</h4>
              <p>Chronic sleep loss has wide-ranging effects:</p>
              <ul>
                <li>Impaired immune function (200% more likely to catch a cold with <6 hours sleep)</li>
                <li>Increased risk of heart disease and stroke</li>
                <li>Higher likelihood of weight gain and obesity</li>
                <li>Reduced testosterone and growth hormone production</li>
                <li>Increased inflammation in the body</li>
                <li>Higher risk of depression and anxiety</li>
                <li>Impaired glucose metabolism and increased diabetes risk</li>
              </ul>
              
              <h4>Optimizing Sleep Quality</h4>
              <p>To maximize the health benefits of sleep:</p>
              <ul>
                <li>Aim for 7-9 hours per night (varies by individual)</li>
                <li>Maintain consistent sleep and wake times</li>
                <li>Create a cool, dark, quiet sleep environment</li>
                <li>Limit exposure to blue light before bedtime</li>
                <li>Avoid caffeine, alcohol, and large meals close to bedtime</li>
                <li>Establish a relaxing pre-sleep routine</li>
                <li>Exercise regularly (but not too close to bedtime)</li>
              </ul>
              
              <p>Understanding the science of sleep underscores its importance as a pillar of health alongside nutrition and exercise. By prioritizing quality sleep, we invest in our physical health, mental wellbeing, and cognitive performance.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Article Modal 6 -->
  <div class="modal fade article-modal" id="articleModal6" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               class="article-image" alt="Holistic Healing">
          <div class="article-content">
            <span class="article-category">WELLNESS</span>
            <h2 class="article-title">Holistic Healing: Combining Traditional and Modern Medicine</h2>
            <div class="article-meta">
              <span><i class="far fa-calendar-alt"></i> May 28, 2023</span>
              <span><i class="far fa-user"></i> Dr. Priya Sharma</span>
              <span><i class="far fa-clock"></i> 7 min read</span>
            </div>
            <div class="article-text">
              <p>In recent years, there has been growing recognition of the value in integrating traditional healing practices with modern medical approaches. This holistic model of healthcare considers the whole person - body, mind, and spirit - and combines the best of both worlds to promote optimal health and healing. This article explores evidence-based complementary therapies and how they can work synergistically with conventional medicine.</p>
              
              <h4>The Principles of Holistic Healing</h4>
              <p>Holistic medicine is based on several key principles:</p>
              <ul>
                <li><strong>Whole-person care:</strong> Addressing physical, emotional, social, and spiritual aspects of health</li>
                <li><strong>Prevention and self-care:</strong> Empowering patients to take an active role in their health</li>
                <li><strong>Individualized treatment:</strong> Recognizing that each person has unique health needs</li>
                <li><strong>Integration of therapies:</strong> Combining conventional and complementary approaches when appropriate</li>
                <li><strong>Healing-oriented medicine:</strong> Focusing on the underlying causes of illness rather than just symptoms</li>
              </ul>
              
              <h4>Evidence-Based Traditional Therapies</h4>
              
              <h5>1. Acupuncture</h5>
              <p>This ancient Chinese practice involves inserting thin needles into specific points on the body. Research shows it can be effective for:</p>
              <ul>
                <li>Chronic pain (back pain, osteoarthritis, migraines)</li>
                <li>Nausea and vomiting (especially post-operative and chemotherapy-induced)</li>
                <li>Stress and anxiety reduction</li>
              </ul>
              
              <h5>2. Ayurveda</h5>
              <p>Originating in India over 3,000 years ago, Ayurveda emphasizes balance among body, mind, and spirit through diet, herbal remedies, yoga, and meditation. Evidence supports its benefits for:</p>
              <ul>
                <li>Stress reduction and improved sleep</li>
                <li>Digestive health</li>
                <li>Inflammation reduction</li>
              </ul>
              
              <h5>3. Herbal Medicine</h5>
              <p>Many modern pharmaceuticals originated from plant compounds. Some well-researched herbal remedies include:</p>
              <ul>
                <li><strong>Turmeric:</strong> Anti-inflammatory properties helpful for arthritis</li>
                <li><strong>Ginger:</strong> Effective for nausea and digestive issues</li>
                <li><strong>St. John's Wort:</strong> May help mild to moderate depression</li>
                <li><strong>Echinacea:</strong> May reduce duration of colds</li>
              </ul>
              
              <h5>4. Mind-Body Practices</h5>
              <p>Techniques like meditation, yoga, and tai chi have been shown to:</p>
              <ul>
                <li>Reduce stress and anxiety</li>
                <li>Lower blood pressure</li>
                <li>Improve sleep quality</li>
                <li>Enhance immune function</li>
              </ul>
              
              <h4>Integrating Traditional and Modern Medicine</h4>
              <p>For safe and effective integration:</p>
              <ol>
                <li><strong>Consult with your healthcare provider:</strong> Always inform your doctor about any complementary therapies you're using.</li>
                <li><strong>Choose evidence-based approaches:</strong> Look for therapies with scientific research supporting their efficacy.</li>
                <li><strong>Find qualified practitioners:</strong> Seek licensed or certified professionals for complementary therapies.</li>
                <li><strong>Be cautious with herbal supplements:</strong> Some can interact with medications or have side effects.</li>
                <li><strong>Use as complement, not replacement:</strong> For serious conditions, conventional treatments should not be abandoned without medical advice.</li>
              </ol>
              
              <h4>The Future of Integrative Medicine</h4>
              <p>Many leading medical institutions now have integrative medicine centers that combine conventional treatments with evidence-based complementary therapies. This approach recognizes that health is more than just the absence of disease, and that optimal care often requires addressing multiple aspects of a person's wellbeing.</p>
              
              <p>As research continues to validate traditional healing practices, we're likely to see even greater integration of these approaches into mainstream healthcare, offering patients more comprehensive options for achieving and maintaining health.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Article Modal 7 -->
  <div class="modal fade article-modal" id="articleModal7" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" 
               class="article-image" alt="Health Screenings">
          <div class="article-content">
            <span class="article-category">PREVENTIVE CARE</span>
            <h2 class="article-title">Essential Health Screenings You Shouldn't Ignore at Any Age</h2>
            <div class="article-meta">
              <span><i class="far fa-calendar-alt"></i> May 25, 2023</span>
              <span><i class="far fa-user"></i> Dr. Amanda Lee</span>
              <span><i class="far fa-clock"></i> 6 min read</span>
            </div>
            <div class="article-text">
              <p>Preventive health screenings are crucial for early detection of potential health issues, often before symptoms appear. Many serious conditions can be effectively treated or even prevented when caught early. This comprehensive guide outlines the essential health screenings recommended at different life stages, helping you stay proactive about your health.</p>
              
              <h4>Screenings for All Adults (18+ Years)</h4>
              
              <h5>1. Blood Pressure</h5>
              <p><strong>Frequency:</strong> At least every 2 years if normal (<120/80), annually if elevated (120-129/<80)</p>
              <p><strong>Why it's important:</strong> High blood pressure often has no symptoms but can lead to heart disease, stroke, and kidney damage.</p>
              
              <h5>2. Cholesterol</h5>
              <p><strong>Frequency:</strong> Every 4-6 years for adults at average risk, more often if risk factors exist</p>
              <p><strong>Why it's important:</strong> High cholesterol contributes to plaque buildup in arteries, increasing heart disease risk.</p>
              
              <h5>3. Body Mass Index (BMI)</h5>
              <p><strong>Frequency:</strong> At annual checkups</p>
              <p><strong>Why it's important:</strong> Helps assess weight-related health risks.</p>
              
              <h5>4. Diabetes (Blood Glucose)</h5>
              <p><strong>Frequency:</strong> Every 3 years starting at age 35, or earlier if overweight with additional risk factors</p>
              <p><strong>Why it's important:</strong> Early detection can prevent complications like nerve damage, kidney disease, and vision problems.</p>
              
              <h4>Screenings for Women</h4>
              
              <h5>1. Breast Cancer (Mammogram)</h5>
              <p><strong>Frequency:</strong> Every 1-2 years starting at age 40-50 (varies by guidelines)</p>
              <p><strong>Why it's important:</strong> Early detection improves treatment outcomes significantly.</p>
              
              <h5>2. Cervical Cancer (Pap Smear/HPV Test)</h5>
              <p><strong>Frequency:</strong> Every 3-5 years starting at age 21-25</p>
              <p><strong>Why it's important:</strong> Can detect precancerous changes before they develop into cancer.</p>
              
              <h5>3. Bone Density (DEXA Scan)</h5>
              <p><strong>Frequency:</strong> At least once at age 65, earlier if risk factors exist</p>
              <p><strong>Why it's important:</strong> Screens for osteoporosis which increases fracture risk.</p>
              
              <h4>Screenings for Men</h4>
              
              <h5>1. Prostate Cancer (PSA Test)</h5>
              <p><strong>Frequency:</strong> Discuss with doctor starting at age 50 (45 for high-risk men)</p>
              <p><strong>Why it's important:</strong> Early detection improves treatment options.</p>
              
              <h5>2. Abdominal Aortic Aneurysm</h5>
              <p><strong>Frequency:</strong> One-time screening for men 65-75 who have ever smoked</p>
              <p><strong>Why it's important:</strong> Can detect dangerous enlargement of the aorta before rupture occurs.</p>
              
              <h4>Screenings for Older Adults (50+ Years)</h4>
              
              <h5>1. Colorectal Cancer</h5>
              <p><strong>Options:</strong> Colonoscopy (every 10 years), stool tests (annually), or other methods</p>
              <p><strong>Why it's important:</strong> Colorectal cancer is highly treatable when caught early.</p>
              
              <h5>2. Lung Cancer (Low-Dose CT Scan)</h5>
              <p><strong>Frequency:</strong> Annual screening for current or former heavy smokers aged 50-80</p>
              
              <h5>3. Eye Exams</h5>
              <p><strong>Frequency:</strong> Every 1-2 years after age 50</p>
              <p><strong>Why it's important:</strong> Checks for glaucoma, cataracts, and macular degeneration.</p>
              
              <h4>Additional Important Screenings</h4>
              <ul>
                <li><strong>Depression screening:</strong> Recommended for all adults</li>
                <li><strong>Hepatitis C:</strong> One-time screening for all adults 18+</li>
                <li><strong>HIV:</strong> At least once for all teens and adults 15-65</li>
                <li><strong>Skin checks:</strong> Regular self-exams and professional exams if high risk</li>
              </ul>
              
              <h4>Personalizing Your Screening Plan</h4>
              <p>Your ideal screening schedule may vary based on:</p>
              <ul>
                <li>Family history of diseases</li>
                <li>Personal health history</li>
                <li>Lifestyle factors (smoking, diet, exercise)</li>
                <li>Ethnicity (some groups have higher risks for certain conditions)</li>
              </ul>
              
              <p>Work with your healthcare provider to develop a personalized preventive care plan. Remember that screenings are just one part of prevention - maintaining a healthy lifestyle with proper nutrition, regular exercise, stress management, and avoiding tobacco is equally important for long-term health.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Initialize all modals
    document.addEventListener('DOMContentLoaded', function() {
      // Enable all modals
      var modals = document.querySelectorAll('.article-modal');
      modals.forEach(function(modal) {
        modal.addEventListener('show.bs.modal', function (event) {
          // Optional: Add any specific modal show logic here
        });
      });
    });
  </script>
</body>
</html>