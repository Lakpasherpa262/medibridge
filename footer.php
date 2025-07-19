<style>
  .main-footer {
  background: var(--dark-color);
  color: white;
  padding: 60px 0 30px;
}

.footer-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-col {
  flex: 1;
  min-width: 250px;
  margin-bottom: 30px;
  padding: 0 15px;
}

.footer-heading {
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

.footer-contact-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  color: rgba(255, 255, 255, 0.7);
}

.footer-contact-item i {
  margin-right: 10px;
  color: var(--primary-color);
}

.footer-bottom {
  text-align: center;
  padding-top: 30px;
  margin-top: 30px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
}

.footer-about {
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 20px;
}
</style>
  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">MediBridge</h3>
          <p>Bridging the gap between pharmacies and communities with accessible, affordable healthcare solutions.</p>
        </div>
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Quick Links</h3>
          <ul class="footer-links">
            <li><a href="index.php">Home</a></li>
                        <li><a href="templates/aboutus.php">About US</a></li>
            <li><a href="templates/blog.php">Blog</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Categories</h3>
          <ul class="footer-links">
            <li><a href="view_all.php?category_id=1&category_name=Antibiotics">Antibiotics</a></li>
            <li><a href="view_all.php?category_id=2&category_name=Antipyretics">Antipyretics</a></li>
            <li><a href="view_all.php?category_id=3&category_name=Skincare">Skincare</a></li>
            <li><a href="view_all.php?category_id=4&category_name=Vitamins">Vitamins</a></li>
            <li><a href="view_all.php?category_id=5&category_name=Diabetes">Diabetes Care</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Contact Us</h3>
          <ul class="footer-links">
            <li><i class="fas fa-phone-alt me-2"></i> +91 98765 43210</li>
            <li><i class="fas fa-envelope me-2"></i> info@medibridge.com</li>
           </ul>
        </div>
      </div>
      <div class="copyright">
        <p class="mb-0">&copy; 2025 MediBridge. All Rights Reserved.</p>
      </div>
    </div>
  </footer>
