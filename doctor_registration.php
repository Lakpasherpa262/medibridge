<?php
include 'session.php'; 
include 'scripts/connect.php'; 

try {
    $stmt = $db->prepare("SELECT id, specialization_name FROM specializations ORDER BY specialization_name");
    $stmt->execute();
    $specializations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching specializations: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge - Doctor Registration</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #1e293b;
      --secondary-color: #334155;
      --accent-color: #4db6ac;
      --light-bg: #f8f9fa;
      --dark-text: #212529;
      --white: #ffffff;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
      --sidebar-bg: #1e293b;
      --sidebar-text: #e2e8f0;
      --sidebar-active: #334155;
      --card-radius: 12px;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fa;
      color: var(--dark-text);
      min-height: 100vh;
      display: flex;
    }
    
    /* Sidebar Styles */
    .sidebar {
      width: 280px;
      background: var(--sidebar-bg);
      color: var(--sidebar-text);
      height: 100vh;
      position: fixed;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .sidebar-header {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    
    .logo {
      width: 60px;
      height: 60px;
      margin: 0 auto;
      margin-bottom: 0.5rem;
    }
    
    .logo-img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    .brand-name {
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 1rem;
      color: var(--sidebar-text);
    }
    
    .divider {
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      margin: 0.8rem 0;
    }
    
    .sidebar-nav {
      flex-grow: 1;
    }
    
    .nav-item {
      margin-bottom: 0.3rem;
    }
    
    .nav-link {
      color: var(--sidebar-text);
      padding: 0.6rem 0.8rem;
      border-radius: 6px;
      display: flex;
      align-items: center;
      transition: var(--transition);
      font-size: 0.9rem;
      text-decoration: none;
    }
    
    .nav-link:hover, .nav-link.active {
      background-color: var(--sidebar-active);
      color: var(--sidebar-text);
    }
    
    .nav-link i {
      margin-right: 8px;
      width: 18px;
      text-align: center;
      font-size: 0.9rem;
    }
    
    .logout-btn {
      background-color: transparent;
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: var(--sidebar-text);
      width: 100%;
      padding: 0.6rem;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
      margin-top: auto;
      font-size: 0.9rem;
    }
    
    .logout-btn:hover {
      background-color: var(--sidebar-active);
    }
    
    /* Main Content Styles */
    .main-content {
      flex-grow: 1;
      margin-left: 280px;
      padding: 1.5rem;
    }
    
    /* Registration Form Styles */
    .registration-form {
      background: white;
      border-radius: var(--card-radius);
      box-shadow: var(--shadow);
      padding: 1.8rem;
      position: relative;
      overflow: hidden;
      border-top: 4px solid var(--secondary-color);
      max-width: 1000px;
      margin: 0 auto;
    }
    
    .form-header {
      text-align: center;
      margin-bottom: 1.8rem;
    }
    
    .form-header .header-icon {
      font-size: 2.5rem;
      color: var(--secondary-color);
      margin-bottom: 0.8rem;
      display: inline-block;
      background: var(--light-bg);
      width: 70px;
      height: 70px;
      line-height: 70px;
      border-radius: 50%;
      box-shadow: 0 4px 8px rgba(0, 77, 64, 0.1);
    }
    
    .form-header h2 {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 0.4rem;
      font-size: 1.6rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .form-header p {
      color: var(--secondary-color);
      font-size: 0.95rem;
    }
    
    .form-row {
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin-bottom: 1rem;
    }
    
    .form-column {
      flex: 1;
      min-width: 280px;
    }
    
    .section-title {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 1rem;
      padding-bottom: 0.4rem;
      border-bottom: 2px solid var(--light-bg);
      display: flex;
      align-items: center;
      font-size: 1rem;
    }
    
    .section-title i {
      margin-right: 0.6rem;
      font-size: 1.1rem;
      color: var(--secondary-color);
    }
    
    .form-group {
      margin-bottom: 1.2rem;
      position: relative;
    }
    
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--primary-color);
      font-size: 0.85rem;
    }
    
    .input-container {
      position: relative;
    }
    
    .input-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--secondary-color);
      font-size: 1rem;
      z-index: 2;
      width: 18px;
      text-align: center;
      pointer-events: none;
    }
    
    .form-control {
      width: 100%;
      padding: 10px 12px 10px 38px;
      border: 2px solid #e0e0e0;
      border-radius: 6px;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      background-color: #f8f9fa;
      color: var(--dark-text);
      height: 42px;
    }
    
    .form-control:focus {
      border-color: var(--accent-color);
      box-shadow: 0 0 0 3px rgba(77, 182, 172, 0.2);
      outline: none;
      background-color: white;
    }
    
    .password-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--secondary-color);
      font-size: 1rem;
      z-index: 2;
    }
    
    .submit-btn {
      background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 2rem auto 0;
      width: 200px;
      box-shadow: 0 4px 8px rgba(0, 77, 64, 0.2);
    }
    
    .submit-btn:hover {
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 77, 64, 0.3);
    }
    
    .error-message {
      color: #dc3545;
      font-size: 0.8rem;
      margin-top: 0.4rem;
      display: none;
    }
    
    .spinner {
      display: inline-block;
      width: 1rem;
      height: 1rem;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 0.8s linear infinite;
      margin-left: 0.5rem;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    .alert-message {
      position: fixed;
      top: 1.2rem;
      right: 1.2rem;
      z-index: 1000;
      max-width: 350px;
      display: none;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
      border: none;
      border-left: 4px solid;
      font-size: 0.9rem;
      padding: 0.8rem 1rem;
    }
    
    /* Image upload styling */
    .image-upload-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 1.2rem;
    }
    
    .image-preview {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      background-color: #f8f9fa;
      border: 2px dashed #ddd;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      margin-bottom: 0.8rem;
      position: relative;
    }
    
    .image-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .image-upload-btn {
      background-color: var(--secondary-color);
      color: white;
      border: none;
      padding: 0.4rem 0.8rem;
      border-radius: 5px;
      cursor: pointer;
      transition: var(--transition);
      font-size: 0.85rem;
    }
    
    .image-upload-btn:hover {
      background-color: var(--primary-color);
    }
    
    /* Schedule styling */
    .schedule-container {
      display: flex;
      flex-wrap: wrap;
      gap: 0.8rem;
    }
    
    .schedule-day {
      flex: 1;
      min-width: 110px;
    }
    
    .schedule-day label {
      display: block;
      margin-bottom: 0.4rem;
      font-weight: 600;
      color: var(--primary-color);
      font-size: 0.85rem;
    }
    
    .schedule-time {
      display: flex;
      gap: 0.4rem;
    }
    
    .schedule-time select {
      flex: 1;
      padding: 0.4rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 0.85rem;
      height: 38px;
    }
    
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .form-row {
        flex-direction: column;
        gap: 1rem;
      }
      
      .schedule-day {
        min-width: 100%;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar Navigation -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <img src="images/logo.png" alt="MediBridge Logo" class="logo-img"> 
      </div>
      <div class="brand-name">MediBridge</div>
    </div>
    <div class="divider"></div>
    
    <div class="sidebar-nav">
      <div class="nav-item">
        <a href="shop.php" class="nav-link">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
      </div>
     
      <div class="nav-item">
        <a href="doctor_registration.php" class="nav-link active">
          <i class="fas fa-user-plus"></i> Add Doctor
        </a>
      </div>
    </div>
    
    <div class="divider"></div>
    
    <button class="logout-btn" onclick="window.location.href='shop.php'">
      <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </button>
  </div>
  
  <!-- Main Content -->
  <div class="main-content">
    <div id="alert-message" class="alert alert-dismissible fade show alert-message" role="alert">
      <span id="alert-text"></span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <form class="registration-form" id="doctor-registration-form" method="POST" enctype="multipart/form-data">
      <div class="form-header">
        <div class="header-icon">
          <i class="fas fa-user-md"></i>
        </div>
        <h2>Doctor Registration</h2>
        <p>Please fill in all required fields to register a new doctor</p>
      </div>

      <div class="form-row">
        <div class="form-column">
          <h3 class="section-title">
            <i class="fas fa-user-circle"></i> Personal Information
          </h3>
          
          <div class="form-group">
            <label for="doctor-name" class="required-field">Full Name</label>
            <div class="input-container">
              <i class="fas fa-user input-icon"></i>
              <input type="text" id="doctor-name" name="doctor_name" class="form-control" placeholder="Enter doctor's full name" required>
            </div>
            <div class="error-message" id="doctor-name-error"></div>
          </div>
          
          <div class="form-group">
            <label for="doctor-email" class="required-field">Email</label>
            <div class="input-container">
              <i class="fas fa-envelope input-icon"></i>
              <input type="email" id="doctor-email" name="doctor_email" class="form-control" placeholder="Enter email" required>
            </div>
            <div class="error-message" id="doctor-email-error"></div>
          </div>
          
          <div class="form-group">
            <label for="doctor-phone" class="required-field">Phone Number</label>
            <div class="input-container">
              <i class="fas fa-phone input-icon"></i>
              <input type="tel" id="doctor-phone" name="doctor_phone" class="form-control" placeholder="Enter phone number" maxlength="10" required>
            </div>
            <div class="error-message" id="doctor-phone-error"></div>
          </div>
          
          <div class="form-group">
            <label for="doctor-specialization" class="required-field">Specialization</label>
            <div class="input-container">
              <i class="fas fa-stethoscope input-icon"></i>
              <select id="doctor-specialization" name="doctor_specialization" class="form-control" required>
                <option value="">Select Specialization</option>
                <?php foreach ($specializations as $spec): ?>
                  <option value="<?= htmlspecialchars($spec['id']) ?>">
                    <?= htmlspecialchars($spec['specialization_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="error-message" id="doctor-specialization-error"></div>
          </div>
          
          <div class="form-group">
            <label for="doctor-degree" class="required-field">Degree</label>
            <div class="input-container">
              <i class="fas fa-graduation-cap input-icon"></i>
              <input type="text" id="doctor-degree" name="doctor_degree" class="form-control" placeholder="e.g., MBBS, MD, etc." required>
            </div>
            <div class="error-message" id="doctor-degree-error"></div>
          </div>
          
          <div class="form-group">
            <label for="doctor-license" class="required-field">License Number</label>
            <div class="input-container">
              <i class="fas fa-id-card input-icon"></i>
              <input type="text" id="doctor-license" name="doctor_license" class="form-control" placeholder="Enter license number" required>
            </div>
            <div class="error-message" id="doctor-license-error"></div>
          </div>
          
          <div class="form-group">
            <label for="doctor-fee" class="required-field">Consultation Fee (₹)</label>
            <div class="input-container">
              <i class="fas fa-rupee-sign input-icon"></i>
              <input type="text" id="doctor-fee" name="doctor_fee" class="form-control" placeholder="Enter consultation fee" required>
            </div>
            <div class="error-message" id="doctor-fee-error"></div>
          </div>
        </div>
        
        <div class="form-column">
          <h3 class="section-title">
            <i class="fas fa-image"></i> Profile Image
          </h3>
          
          <div class="image-upload-container">
            <div class="image-preview" id="image-preview">
              <i class="fas fa-user-md" style="font-size: 2.5rem; color: #ccc;"></i>
            </div>
            <input type="file" id="doctor-image" name="doctor_image" accept="image/*" style="display: none;">
            <button type="button" class="image-upload-btn" onclick="document.getElementById('doctor-image').click()">
              <i class="fas fa-upload me-2"></i>Upload Image
            </button>
            <div class="error-message" id="doctor-image-error"></div>
          </div>
          
          <h3 class="section-title">
            <i class="fas fa-calendar-alt"></i> Consultation Schedule
          </h3>
          
          <div class="form-group">
            <label class="required-field">Available Days & Times</label>
            <div class="schedule-container">
              <?php
              $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
              foreach($days as $day): 
              ?>
              <div class="schedule-day">
                <label><?= ucfirst($day) ?></label>
                <div class="schedule-time">
                  <select name="<?= $day ?>_start" class="form-control">
                    <option value="">Start Time</option>
                    <?php for($i=8; $i<=18; $i++): ?>
                      <option value="<?= sprintf('%02d:00', $i) ?>"><?= sprintf('%02d:00', $i) ?></option>
                    <?php endfor; ?>
                  </select>
                  <select name="<?= $day ?>_end" class="form-control">
                    <option value="">End Time</option>
                    <?php for($i=9; $i<=19; $i++): ?>
                      <option value="<?= sprintf('%02d:00', $i) ?>"><?= sprintf('%02d:00', $i) ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="error-message" id="schedule-error"></div>
          </div>
          
          <h3 class="section-title">
            <i class="fas fa-lock"></i> Account Security
          </h3>
          
          <div class="form-group">
            <label for="doctor-password" class="required-field">Password</label>
            <div class="input-container">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="doctor-password" name="doctor_password" class="form-control" placeholder="Enter password" required>
              <span class="password-toggle" onclick="togglePassword('doctor-password')">
                <i class="fa-solid fa-eye-slash"></i>
              </span>
            </div>
            <div class="error-message" id="doctor-password-error"></div>
          </div>
          
          <div class="form-group">
            <label for="doctor-confirm-password" class="required-field">Confirm Password</label>
            <div class="input-container">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="doctor-confirm-password" name="doctor_confirm_password" class="form-control" placeholder="Confirm password" required>
              <span class="password-toggle" onclick="togglePassword('doctor-confirm-password')">
                <i class="fa-solid fa-eye-slash"></i>
              </span>
            </div>
            <div class="error-message" id="doctor-confirm-password-error"></div>
          </div>
        </div>
      </div>
      
      <button type="submit" class="submit-btn" id="submit-btn">
        <i class="fas fa-user-md"></i>
        <span id="submit-text">Register Doctor</span>
        <span id="submit-spinner" class="spinner" style="display: none;"></span>
      </button>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      // Image preview functionality
      $('#doctor-image').change(function() {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            $('#image-preview').html(`<img src="${e.target.result}" alt="Doctor Preview">`);
          }
          reader.readAsDataURL(file);
        }
      });
      
      // Toggle password visibility
      function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = $(`#${fieldId}`).siblings('.password-toggle').find('i');
        field.type = field.type === "password" ? "text" : "password";
        icon.toggleClass('fa-eye-slash fa-eye');
      }
      
      // Show alert message
      function showAlert(type, message) {
        const alert = $('#alert-message');
        const icon = type === 'success' ? 
            '<i class="fas fa-check-circle me-2"></i>' : 
            '<i class="fas fa-exclamation-circle me-2"></i>';
        
        alert.removeClass('alert-success alert-danger')
             .addClass(`alert-${type}`)
             .find('#alert-text').html(icon + message);
             
        alert.fadeIn().css('display', 'flex');
        
        setTimeout(() => {
            alert.fadeOut();
        }, 5000);
      }
      
      // Close alert when close button is clicked
      $('.btn-close').click(function() {
          $('#alert-message').fadeOut();
      });
      
      // Phone number validation (only numbers)
      $('input[name="doctor_phone"]').on('input', function() {
          $(this).val($(this).val().replace(/\D/g, '').substring(0, 10));
          $(`#${this.id}-error`).hide();
          $(this).removeClass('is-invalid');
      });
      
      // Name validation (only letters and spaces allowed)
      $('#doctor-name').on('input', function() {
          // Remove any numbers or special characters
          $(this).val($(this).val().replace(/[^a-zA-Z\s]/g, ''));
          $(`#${this.id}-error`).hide();
          $(this).removeClass('is-invalid');
      });
      
      // Fee validation (only numbers allowed)
      $('#doctor-fee').on('input', function() {
          $(this).val($(this).val().replace(/[^0-9]/g, ''));
          $(`#${this.id}-error`).hide();
          $(this).removeClass('is-invalid');
      });
      
      // Degree validation (letters and commas only)
      $('#doctor-degree').on('input', function() {
          const degreeRegex = /^[a-zA-Z\s,]*$/;
          if (!degreeRegex.test($(this).val())) {
              $('#doctor-degree-error').text('Degree should contain only letters and commas').show();
              $(this).addClass('is-invalid');
          } else {
              $('#doctor-degree-error').hide();
              $(this).removeClass('is-invalid');
          }
      });
      
      // License validation (alphanumeric allowed)
      $('#doctor-license').on('input', function() {
          const licenseRegex = /^[a-zA-Z0-9]*$/;
          if (!licenseRegex.test($(this).val())) {
              $('#doctor-license-error').text('License should contain only letters and numbers').show();
              $(this).addClass('is-invalid');
          } else {
              $('#doctor-license-error').hide();
              $(this).removeClass('is-invalid');
          }
      });
      
      // Email validation
      $('#doctor-email').on('blur', function() {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test($(this).val())) {
              $('#doctor-email-error').text('Please enter a valid email address').show();
              $(this).addClass('is-invalid');
          } else {
              $('#doctor-email-error').hide();
              $(this).removeClass('is-invalid');
          }
      });      
      
      // Password strength validation
      $('#doctor-password').on('input', function() {
          const password = $(this).val();
          let error = '';
          
          if (password.length < 8) {
              error = 'Password must be at least 8 characters';
          } else if (!/[A-Z]/.test(password)) {
              error = 'Password must contain at least one uppercase letter';
          } else if (!/[a-z]/.test(password)) {
              error = 'Password must contain at least one lowercase letter';
          } else if (!/[0-9]/.test(password)) {
              error = 'Password must contain at least one number';
          }
          
          if (error) {
              $('#doctor-password-error').text(error).show();
              $(this).addClass('is-invalid');
          } else {
              $('#doctor-password-error').hide();
              $(this).removeClass('is-invalid');
          }
      });
      
      // Confirm password validation
      $('#doctor-confirm-password').on('input', function() {
          if ($(this).val() !== $('#doctor-password').val()) {
              $('#doctor-confirm-password-error').text('Passwords do not match').show();
              $(this).addClass('is-invalid');
          } else {
              $('#doctor-confirm-password-error').hide();
              $(this).removeClass('is-invalid');
          }
      });
      
      // Form submission handler
      $('#doctor-registration-form').submit(function(e) {
          e.preventDefault();
          
          // Clear previous errors
          $('.error-message').hide();
          $('.form-control').removeClass('is-invalid');
          
          // Validate all required fields
          let isValid = true;
          $('[required]').each(function() {
              if (!$(this).val().trim()) {
                  $(`#${this.id}-error`).text('This field is required').show();
                  $(this).addClass('is-invalid');
                  isValid = false;
              }
          });
          
          // Additional validations
          const nameRegex = /^[a-zA-Z\s]+$/;
          if ($('#doctor-name').val() && !nameRegex.test($('#doctor-name').val())) {
              $('#doctor-name-error').text('Name should contain only letters and spaces').show();
              $('#doctor-name').addClass('is-invalid');
              isValid = false;
          }
          
          const degreeRegex = /^[a-zA-Z\s,]+$/;
          if ($('#doctor-degree').val() && !degreeRegex.test($('#doctor-degree').val())) {
              $('#doctor-degree-error').text('Degree should contain only letters and commas').show();
              $('#doctor-degree').addClass('is-invalid');
              isValid = false;
          }
          
          const licenseRegex = /^[a-zA-Z0-9]+$/;
          if ($('#doctor-license').val() && !licenseRegex.test($('#doctor-license').val())) {
              $('#doctor-license-error').text('License should contain only letters and numbers').show();
              $('#doctor-license').addClass('is-invalid');
              isValid = false;
          }
          
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if ($('#doctor-email').val() && !emailRegex.test($('#doctor-email').val())) {
              $('#doctor-email-error').text('Please enter a valid email address').show();
              $('#doctor-email').addClass('is-invalid');
              isValid = false;
          }
          
          if ($('#doctor-phone').val() && $('#doctor-phone').val().length !== 10) {
              $('#doctor-phone-error').text('Phone number must be 10 digits').show();
              $('#doctor-phone').addClass('is-invalid');
              isValid = false;
          }
          
          // Fee validation
          const fee = $('#doctor-fee').val();
          if (!fee || isNaN(fee)) {
              $('#doctor-fee-error').text('Please enter a valid fee amount').show();
              $('#doctor-fee').addClass('is-invalid');
              isValid = false;
          } else if (parseInt(fee) <= 0) {
              $('#doctor-fee-error').text('Fee must be greater than 0').show();
              $('#doctor-fee').addClass('is-invalid');
              isValid = false;
          }
          
          if ($('#doctor-password').val() !== $('#doctor-confirm-password').val()) {
              $('#doctor-confirm-password-error').text('Passwords do not match').show();
              $('#doctor-confirm-password').addClass('is-invalid');
              isValid = false;
          }
          
          // Password strength validation
          const password = $('#doctor-password').val();
          if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
              $('#doctor-password-error').text('Password must be 8+ chars with uppercase, lowercase, and number').show();
              $('#doctor-password').addClass('is-invalid');
              isValid = false;
          }
          
          // Validate at least one schedule day is selected
          let hasSchedule = false;
          $('select[name$="_start"]').each(function() {
              if ($(this).val()) {
                  hasSchedule = true;
                  return false; // break loop
              }
          });
          
          if (!hasSchedule) {
              $('#schedule-error').text('Please set at least one available day').show();
              isValid = false;
          }
          
          if (!isValid) {
              showAlert('danger', 'Please correct the highlighted errors');
              return;
          }
          
          // Show loading state
          $('#submit-btn').prop('disabled', true);
          $('#submit-text').text('Processing...');
          $('#submit-spinner').show();
          
          // Get form data
          const formData = new FormData(this);
          
          // Submit form via AJAX
          $.ajax({
              url: 'includes/doctor_signup.php',
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              dataType: 'json',
              success: function(response) {
                  if (response.success) {
                      showAlert('success', response.message);
                      // Reset form on success
                      $('#doctor-registration-form')[0].reset();
                      $('#image-preview').html('<i class="fas fa-user-md" style="font-size: 2.5rem; color: #ccc;"></i>');
                      // Redirect after 2 seconds
                      setTimeout(() => {
                          window.location.href = "shop.php";
                      }, 2000);
                  } else {
                      showAlert('danger', response.message);
                      // Highlight specific errors if available
                      if (response.errors) {
                          Object.keys(response.errors).forEach(field => {
                              $(`#${field}-error`).text(response.errors[field]).show();
                              $(`#${field}`).addClass('is-invalid');
                          });
                      }
                  }
              },
              error: function(xhr, status, error) {
                  let errorMessage = "An error occurred while processing your request.";
                  console.log("Status:", status);
                  console.log("Error:", error);
                  console.log("Response:", xhr.responseText);
                  
                  try {
                      const response = JSON.parse(xhr.responseText);
                      if (response.message) {
                          errorMessage = response.message;
                      }
                  } catch (e) {
                      console.error("Error parsing response:", e);
                      errorMessage = xhr.responseText || errorMessage;
                  }
                  showAlert('danger', errorMessage);
              },
              complete: function() {
                  $('#submit-btn').prop('disabled', false);
                  $('#submit-text').html('<i class="fas fa-user-md"></i> Register Doctor');
                  $('#submit-spinner').hide();
              }
          });
      });
      
      // Password toggle click handlers
      $('.password-toggle').click(function() {
          const fieldId = $(this).closest('.input-container').find('input').attr('id');
          togglePassword(fieldId);
      });
    });
  </script>
</body>
</html>