<?php
include 'session.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge - Shop Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #2563eb;
      --primary-light: #3b82f6;
      --primary-dark: #1d4ed8;
      --secondary-color: #10b981;
      --accent-color: #6366f1;
      --danger-color: #ef4444;
      --warning-color: #f59e0b;
      --info-color: #06b6d4;
      --light-bg: #f8fafc;
      --dark-text: #1e293b;
      --medium-text: #64748b;
      --light-text: #94a3b8;
      --white: #ffffff;
      --sidebar-bg: #1e293b;
      --sidebar-text: #e2e8f0;
      --sidebar-active: #334155;
      --card-radius: 12px;
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
      --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light-bg);
      color: var(--dark-text);
      min-height: 100vh;
      display: flex;
      line-height: 1.6;
    }

    .sidebar {
      width: 280px;
      background: var(--sidebar-bg);
      color: var(--sidebar-text);
      height: 100vh;
      position: fixed;
      padding: 1.5rem 1rem;
      box-shadow: var(--shadow-md);
      z-index: 1000;
      transition: var(--transition);
      display: flex;
      flex-direction: column;
    }

    .logo {
      width: 80px;
      height: 80px;
      border-radius: 12px;
      background: var(--white);
      margin: 0 auto 1.5rem;
      padding: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .logo-img {
      max-width: 100%;
      max-height: 100%;
      border-radius: 8px;
    }

    .brand-name {
      font-size: 1.5rem;
      font-weight: 700;
      text-align: center;
      color: var(--white);
      margin-bottom: 2rem;
      transition: var(--transition);
    }

    .sidebar-header {
      color: var(--light-text);
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 1.5rem 0 0.75rem;
      padding-left: 0.75rem;
      transition: var(--transition);
    }

    .nav-link {
      color: var(--sidebar-text) !important;
      padding: 0.75rem;
      margin: 0.25rem 0;
      border-radius: 8px;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-weight: 500;
    }

    .nav-link i {
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
    }

    .nav-link:hover, .nav-link.active {
      background: var(--sidebar-active);
      color: var(--white) !important;
    }

    .nav-link.active {
      font-weight: 600;
    }

    .main-content {
      margin-left: 280px;
      padding: 2rem;
      flex-grow: 1;
      transition: var(--transition);
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: var(--primary-color);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }

    .registration-form {
      background: var(--white);
      border-radius: var(--card-radius);
      padding: 2rem;
      box-shadow: var(--shadow-sm);
      margin-bottom: 2rem;
    }

    .form-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .form-header .header-icon {
      font-size: 3rem;
      color: var(--primary-color);
      margin-bottom: 1rem;
    }

    .form-header h2 {
      color: var(--dark-text);
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .form-header p {
      color: var(--medium-text);
    }

    .form-row {
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .form-column {
      flex: 1;
      min-width: 300px;
    }

    .section-title {
      color: var(--dark-text);
      font-weight: 600;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .section-title i {
      color: var(--primary-color);
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--dark-text);
      font-size: 0.9rem;
    }

    .form-group label.required:after {
      content: " *";
      color: var(--danger-color);
    }

    .form-control {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 0.95rem;
      transition: var(--transition);
      background-color: var(--light-bg);
    }

    textarea.form-control {
      height: auto;
      min-height: 100px;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
      outline: none;
      background-color: var(--white);
    }

    .file-input-wrapper {
      position: relative;
      overflow: hidden;
      display: inline-block;
      width: 100%;
    }

    .file-input-wrapper input[type="file"] {
      font-size: 100px;
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
    }

    .file-input-btn {
      display: inline-flex;
      align-items: center;
      padding: 0.75rem 1rem;
      background-color: var(--light-bg);
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      color: var(--dark-text);
      cursor: pointer;
      width: 100%;
      transition: var(--transition);
    }

    .file-input-btn:hover {
      background-color: #e9ecef;
      border-color: var(--primary-color);
    }

    .file-icon {
      margin-right: 0.75rem;
      color: var(--medium-text);
    }

    .file-name {
      flex-grow: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-size: 0.9rem;
    }

    .form-note {
      font-size: 0.8rem;
      color: var(--medium-text);
      margin-top: 0.5rem;
      font-style: italic;
    }

    .image-preview {
      margin-top: 1rem;
      display: none;
    }

    .image-preview img {
      max-width: 200px;
      max-height: 200px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
    }

    .submit-btn {
      background-color: var(--primary-color);
      color: var(--white);
      border: none;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      font-weight: 500;
      border-radius: 8px;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 1rem;
      box-shadow: var(--shadow-sm);
    }

    .submit-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .spinner {
      display: inline-block;
      width: 1rem;
      height: 1rem;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .alert-message {
      position: fixed;
      top: 1.5rem;
      right: 1.5rem;
      z-index: 1000;
      max-width: 400px;
      display: none;
    }

    .is-invalid {
      border-color: var(--danger-color) !important;
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
      }
    }

    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
      }
      
      .form-column {
        min-width: 100%;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">
    </div>
    <div class="brand-name">MediBridge</div>

    <div class="sidebar-header">Navigation</div>
    <nav class="nav flex-column">
      <a class="nav-link" href="Shop_Owner.php">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </a>
      <a class="nav-link active" href="shop_registration.php">
        <i class="fas fa-store"></i>
        <span>Register Shop</span>
      </a>

    </nav>

    <div class="mt-auto">
      <button class="btn btn-outline-light w-100 mt-3" onclick="window.location.href='Shop_Owner.php'">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Dashboard</span>
      </button>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <h2 class="mb-0">
        <i class="fas fa-store text-primary me-2"></i>
        Shop Registration
      </h2>
      <div class="user-profile">
        <div class="user-avatar">SO</div>
        <div class="user-info">
          <span class="user-name">Shop Owner</span>
          <span class="user-role">Shop Administrator</span>
        </div>
      </div>
    </div>

    <!-- Registration Form -->
    <div class="registration-form">
      <div class="form-header">
        <div class="header-icon">
          <i class="fas fa-store-alt"></i>
        </div>
        <h2>Register Your Shop</h2>
        <p>Fill in all required fields to register your shop with MediBridge</p>
      </div>

      <form id="shop-registration-form" enctype="multipart/form-data">
        <div class="form-row">
          <div class="form-column">
            <h3 class="section-title">
              <i class="fas fa-info-circle"></i> Basic Information
            </h3>
            
            <div class="form-group">
              <label for="shop-name" class="required">Shop Name</label>
              <input type="text" class="form-control" id="shop-name" name="shop_name" placeholder="Shop name" required>
              <div class="error-message" id="shop-name-error"></div>
            </div>
            
            <div class="form-group">
              <label for="shop-image" class="required">Shop Image</label>
              <div class="file-input-wrapper">
                <button type="button" class="file-input-btn">
                  <i class="fas fa-image file-icon"></i>
                  <span class="file-name" id="shop-image-name">Choose image (JPEG/PNG)</span>
                </button>
                <input type="file" class="form-control" id="shop-image" name="shop_image" accept="image/*" required>
              </div>
              <div class="form-note">Recommended size: 800x600px, Max 2MB</div>
              <div class="error-message" id="shop-image-error"></div>
              <div class="image-preview" id="shop-image-preview">
                <img id="shop-image-preview-img" src="#" alt="Shop Image Preview">
              </div>
            </div>
            
            <div class="form-group">
              <label for="shop-number" class="required">Contact Number</label>
              <input type="tel" class="form-control" id="shop-number" name="shop_number" placeholder="10-digit number" required maxlength="10">
              <div class="error-message" id="shop-number-error"></div>
            </div>
            
            <div class="form-group">
              <label for="email" class="required">Email Address</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Business email" required>
              <div class="error-message" id="email-error"></div>
            </div>
          </div>
          
          <div class="form-column">
            <h3 class="section-title">
              <i class="fas fa-map-marker-alt"></i> Location Details
            </h3>
            
            <div class="form-group">
              <label for="state" class="required">State</label>
              <input type="text" class="form-control" id="state" name="state" placeholder="State" required>
              <div class="error-message" id="state-error"></div>
            </div>
            
            <div class="form-group">
              <label for="district" class="required">District</label>
              <input type="text" class="form-control" id="district" name="district" placeholder="District" required>
              <div class="error-message" id="district-error"></div>
            </div>
            
            <div class="form-group">
              <label for="pincode" class="required">Pincode</label>
              <input type="text" class="form-control" id="pincode" name="pincode" placeholder="6-digit pincode" required maxlength="6">
              <div class="error-message" id="pincode-error"></div>
            </div>
            
            <div class="form-group">
              <label for="address" class="required">Full Address</label>
              <textarea class="form-control" id="address" name="address" placeholder="Full shop address" required></textarea>
              <div class="error-message" id="address-error"></div>
            </div>
          </div>
        </div>
        
        <h3 class="section-title">
          <i class="fas fa-file-contract"></i> Business Licenses & Documents
        </h3>
        
        <div class="form-row">
          <div class="form-column">
            <div class="form-group">
              <label for="trade-license" class="required">Trade License Number</label>
              <input type="text" class="form-control" id="trade-license" name="trade_license" placeholder="License number" required>
              <div class="error-message" id="trade-license-error"></div>
            </div>
            
            <div class="form-group">
              <label for="retail-drug-license" class="required">Retail Drug License</label>
              <input type="text" class="form-control" id="retail-drug-license" name="retail_drug_license" placeholder="Drug license" required>
              <div class="error-message" id="retail-drug-license-error"></div>
            </div>
            
            <div class="form-group">
              <label for="registration-number" class="required">Registration Number</label>
              <input type="text" class="form-control" id="registration-number" name="registration_number" placeholder="Registration number" required>
              <div class="error-message" id="registration-number-error"></div>
            </div>
          </div>
          
          <div class="form-column">
            <div class="form-group">
              <label for="owner-signature" class="required">Owner Signature Image</label>
              <div class="file-input-wrapper">
                <button type="button" class="file-input-btn">
                  <i class="fas fa-signature file-icon"></i>
                  <span class="file-name" id="signature-name">Upload signature</span>
                </button>
                <input type="file" class="form-control" id="owner-signature" name="owner_signature" accept="image/*" required>
              </div>
              <div class="form-note">Upload a clear image of your signature (Max 1MB)</div>
              <div class="error-message" id="owner-signature-error"></div>
              <div class="image-preview" id="signature-preview">
                <img id="signature-preview-img" src="#" alt="Signature Preview">
              </div>
            </div>
          </div>
        </div>
        
        <button type="submit" class="submit-btn" id="submit-btn">
          <i class="fas fa-save"></i>
          <span id="submit-text">Register Shop</span>
          <span id="submit-spinner" class="spinner" style="display: none;"></span>
        </button>
      </form>
    </div>
  </div>

  <div id="alert-message" class="alert alert-dismissible fade show alert-message" role="alert" style="display: none;">
    <span id="alert-text"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
      // Prevent numbers in text fields
      $('input[type="text"][id="state"], input[type="text"][id="district"], input[type="text"][id="shop-name"]').on('input', function() {
        this.value = this.value.replace(/[0-9]/g, '');
      });

      // Prevent text in number fields
      $('input[type="tel"][id="shop-number"], input[type="text"][id="pincode"]').on('input', function() {
        this.value = this.value.replace(/\D/g, '');
      });

      // Validate license numbers (exactly 10 characters, no spaces)
      $('input[id="trade-license"], input[id="retail-drug-license"], input[id="registration-number"]').on('input', function() {
        // Remove spaces and special characters
        this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
        
        // Trim to 10 characters
        if (this.value.length > 10) {
          this.value = this.value.substring(0, 10);
        }
      });

      // File input handlers
      $('#shop-image').change(function() {
        var fileName = $(this).val().split('\\').pop();
        $('#shop-image-name').text(fileName || 'Choose image (JPEG/PNG)');
        $(`#${this.id}-error`).hide();
        $(this).removeClass('is-invalid');
        
        if (this.files && this.files[0]) {
          const file = this.files[0];
          const validTypes = ['image/jpeg', 'image/png'];
          
          if (!validTypes.includes(file.type)) {
            $(`#${this.id}-error`).text('Only JPG or PNG images are allowed').show();
            $(this).addClass('is-invalid');
            return;
          }
          
          if (file.size > 2 * 1024 * 1024) {
            $(`#${this.id}-error`).text('Image size must be less than 2MB').show();
            $(this).addClass('is-invalid');
            return;
          }
          
          var reader = new FileReader();
          reader.onload = function(e) {
            $('#shop-image-preview-img').attr('src', e.target.result);
            $('#shop-image-preview').show();
          }
          reader.readAsDataURL(file);
        }
      });
      
      $('#owner-signature').change(function() {
        var fileName = $(this).val().split('\\').pop();
        $('#signature-name').text(fileName || 'Upload signature');
        $(`#${this.id}-error`).hide();
        $(this).removeClass('is-invalid');
        
        if (this.files && this.files[0]) {
          const file = this.files[0];
          const validTypes = ['image/jpeg', 'image/png'];
          
          if (!validTypes.includes(file.type)) {
            $(`#${this.id}-error`).text('Only JPG or PNG images are allowed').show();
            $(this).addClass('is-invalid');
            return;
          }
          
          if (file.size > 1 * 1024 * 1024) {
            $(`#${this.id}-error`).text('Signature size must be less than 1MB').show();
            $(this).addClass('is-invalid');
            return;
          }
          
          var reader = new FileReader();
          reader.onload = function(e) {
            $('#signature-preview-img').attr('src', e.target.result);
            $('#signature-preview').show();
          }
          reader.readAsDataURL(file);
        }
      });

      // Alert function
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

      // Form submission
      $('#shop-registration-form').submit(function(e) {
        e.preventDefault();
        
        $('.error-message').hide();
        $('.form-control').removeClass('is-invalid');
        
        let isValid = true;
        
        // Validate required fields
        $('[required]').each(function() {
            if (!$(this).val() || ($(this).is('input[type="file"]') && $(this)[0].files.length === 0)) {
                $(`#${this.id}-error`).text('This field is required').show();
                $(this).addClass('is-invalid');
                isValid = false;
            }
        });
        
        // Validate phone number
        const shopNumber = $('#shop-number').val();
        if (shopNumber.length !== 10) {
          $('#shop-number-error').text('Phone number must be exactly 10 digits').show();
          $('#shop-number').addClass('is-invalid');
          isValid = false;
        }
        
        // Validate pincode
        const pincode = $('#pincode').val();
        if (pincode.length !== 6) {
          $('#pincode-error').text('Pincode must be exactly 6 digits').show();
          $('#pincode').addClass('is-invalid');
          isValid = false;
        }
        
        // Validate license numbers
        const validateLicense = (id, name) => {
          const value = $(`#${id}`).val();
          if (value.length !== 10) {
            $(`#${id}-error`).text(`${name} must be exactly 10 characters`).show();
            $(`#${id}`).addClass('is-invalid');
            isValid = false;
          }
        };
        
        validateLicense('trade-license', 'Trade license');
        validateLicense('retail-drug-license', 'Drug license');
        validateLicense('registration-number', 'Registration number');
        
        if (!isValid) {
          showAlert('danger', 'Please correct the highlighted errors');
          return;
        }
        
        $('#submit-btn').prop('disabled', true);
        $('#submit-text').text('Processing...');
        $('#submit-spinner').show();
        
        const formData = new FormData(this);
        
        $.ajax({
          url: 'includes/addshop.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              showAlert('success', response.message);
              $('#shop-registration-form')[0].reset();
              $('#shop-image-name').text('Choose image (JPEG/PNG)');
              $('#signature-name').text('Upload signature');
              $('.image-preview').hide();
              
              setTimeout(() => {
                window.location.href = "Shop_Owner.php";
              }, 2000);
            } else {
              showAlert('danger', response.message);
              if (response.errors) {
                Object.keys(response.errors).forEach(field => {
                  $(`#${field}-error`).text(response.errors[field]).show();
                  $(`#${field}`).addClass('is-invalid');
                });
              }
            }
          },
          error: function(xhr) {
            let errorMessage = "An error occurred while processing your request.";
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) errorMessage = response.message;
            } catch (e) {}
            showAlert('danger', errorMessage);
          },
          complete: function() {
            $('#submit-btn').prop('disabled', false);
            $('#submit-text').html('<i class="fas fa-save"></i> Register Shop');
            $('#submit-spinner').hide();
          }
        });
      });
    });
  </script>
</body>
</html>