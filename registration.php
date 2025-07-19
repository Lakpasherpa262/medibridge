<?php
include 'session.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge - Registration Form</title>
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

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
      outline: none;
      background-color: var(--white);
    }

    .input-container {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--medium-text);
      pointer-events: none;
    }

    .form-control.with-icon {
      padding-left: 2.5rem;
    }

    .password-toggle {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--medium-text);
    }

    .error-message {
      color: var(--danger-color);
      font-size: 0.8rem;
      margin-top: 0.25rem;
      display: none;
    }

    .is-invalid {
      border-color: var(--danger-color) !important;
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
 <a href="admin_dashboard.php" class="nav-item">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
      <a class="nav-link active" href="registration.php">
        <i class="fas fa-user-plus"></i>
        <span>Add New Users</span>
      </a>
      
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <h2 class="mb-0">
        
      </h2>
      <div class="user-profile">
        <div class="user-avatar">AD</div>
        <div class="user-info">
          <span class="user-name">Admin User</span>
          <span class="user-role">System Administrator</span>
        </div>
      </div>
    </div>

    <!-- Registration Form -->
    <div class="registration-form">
      <div class="form-header">
        <div class="header-icon">
          <i class="fas fa-user-plus"></i>
        </div>
        <h2>Registration Form</h2>
        <p>Please fill in all required fields to register a new user</p>
      </div>

      <form id="registration-form" method="POST">
        <div class="form-row">
          <div class="form-column">
            <h3 class="section-title">
              <i class="fas fa-user-circle"></i>Personal Information
            </h3>
            
            <div class="form-group">
              <label for="first-name" class="required">First Name</label>
              <div class="input-container">
                <i class="fas fa-user input-icon"></i>
                <input type="text" id="first-name" name="first_name" class="form-control with-icon" placeholder="Enter first name" required maxlength="50">
              </div>
              <div class="error-message" id="first-name-error"></div>
            </div>
            
            <div class="form-group">
              <label for="middle-name">Middle Name</label>
              <div class="input-container">
                <i class="fas fa-user input-icon"></i>
                <input type="text" id="middle-name" name="middle_name" class="form-control with-icon" placeholder="Enter middle name" maxlength="50">
              </div>
              <div class="error-message" id="middle-name-error"></div>
            </div>
            
            <div class="form-group">
              <label for="last-name" class="required">Last Name</label>
              <div class="input-container">
                <i class="fas fa-user input-icon"></i>
                <input type="text" id="last-name" name="last_name" class="form-control with-icon" placeholder="Enter last name" required maxlength="50">
              </div>
              <div class="error-message" id="last-name-error"></div>
            </div>
            
            <div class="form-group">
              <label for="email" class="required">Email</label>
              <div class="input-container">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" id="email" name="email" class="form-control with-icon" placeholder="Enter email" required maxlength="100">
              </div>
              <div class="error-message" id="email-error"></div>
            </div>
            
            <div class="form-group">
              <label for="phone" class="required">Phone Number</label>
              <div class="input-container">
                <i class="fas fa-phone input-icon"></i>
                <input type="tel" id="phone" name="phone" class="form-control with-icon" placeholder="Enter phone number" maxlength="10" required>
              </div>
              <div class="error-message" id="phone-error"></div>
            </div>
          </div>
          
          <div class="form-column">
            <h3 class="section-title">
              <i class="fas fa-map-marked-alt"></i>Address Information
            </h3>
            
            <div class="form-group">
              <label for="address" class="required">Full Address</label>
              <textarea id="address" name="address" class="form-control" placeholder="Enter full address" rows="3" required maxlength="255"></textarea>
              <div class="error-message" id="address-error"></div>
            </div>
            
            <div class="form-group">
              <label for="state" class="required">State</label>
              <input type="text" id="state" name="state" class="form-control" placeholder="Enter state" required maxlength="50">
              <div class="error-message" id="state-error"></div>
            </div>
            
            <div class="form-group">
              <label for="district" class="required">District</label>
              <input type="text" id="district" name="district" class="form-control" placeholder="Enter district" required maxlength="50">
              <div class="error-message" id="district-error"></div>
            </div>
            
            <div class="form-group">
              <label for="pincode" class="required">Pincode</label>
              <input type="text" id="pincode" name="pincode" class="form-control" placeholder="Enter pincode" maxlength="6" required>
              <div class="error-message" id="pincode-error"></div>
            </div>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-column">
            <h3 class="section-title">
              <i class="fas fa-lock"></i>Account Security
            </h3>
            
            <div class="form-group">
              <label for="password" class="required">Password</label>
              <div class="input-container">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" id="password" name="password" class="form-control with-icon" placeholder="Enter password (8-10 chars)" required maxlength="10">
                <span class="password-toggle" onclick="togglePassword('password')">
                  <i class="fa-solid fa-eye-slash"></i>
                </span>
              </div>
              <div class="error-message" id="password-error"></div>
            </div>
            
            <div class="form-group">
              <label for="confirm-password" class="required">Confirm Password</label>
              <div class="input-container">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" id="confirm-password" name="confirm_password" class="form-control with-icon" placeholder="Confirm password" required maxlength="10">
                <span class="password-toggle" onclick="togglePassword('confirm-password')">
                  <i class="fa-solid fa-eye-slash"></i>
                </span>
              </div>
              <div class="error-message" id="confirm-password-error"></div>
            </div>
          </div>
          
          <div class="form-column">
            <h3 class="section-title">
              <i class="fas fa-user-tag"></i>User Role
            </h3>
            
            <div class="form-group">
              <label for="user-role" class="required">Register As</label>
              <select id="user-role" name="user_role" class="form-control" required>
                <option value="">Select Role</option>
                <option value="2">Shop Owner</option>
                <option value="4">Delivery Person</option>
              </select>
              <div class="error-message" id="user-role-error"></div>
            </div>
            
            <div class="form-group">
              <label for="dob" class="required">Date of Birth</label>
              <input type="date" id="dob" name="dob" class="form-control" required>
              <div class="error-message" id="dob-error"></div>
            </div>
            
            <div class="form-group">
              <label for="gender" class="required">Gender</label>
              <select id="gender" name="gender" class="form-control" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
              <div class="error-message" id="gender-error"></div>
            </div>
          </div>
        </div>
        
        <button type="submit" class="submit-btn" id="submit-btn">
          <i class="fas fa-user-plus"></i>
          <span id="submit-text">Register User</span>
          <span id="submit-spinner" class="spinner" style="display: none;"></span>
        </button>
      </form>
    </div>
  </div>

  <div id="alert-message" class="alert alert-dismissible fade show alert-message" role="alert" style="display: none;">
    <span id="alert-text"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
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

    // Validate name fields (no numbers allowed)
    function validateNameField(fieldId, fieldName) {
      const value = $(`#${fieldId}`).val().trim();
      if (/[0-9]/.test(value)) {
          $(`#${fieldId}-error`).text(`${fieldName} cannot contain numbers`).show();
          $(`#${fieldId}`).addClass('is-invalid');
          return false;
      }
      return true;
    }

    // Validate numeric fields (only numbers allowed)
    function validateNumericField(fieldId, fieldName, exactLength = null) {
      const value = $(`#${fieldId}`).val().trim();
      if (!/^\d+$/.test(value)) {
          $(`#${fieldId}-error`).text(`${fieldName} must contain only numbers`).show();
          $(`#${fieldId}`).addClass('is-invalid');
          return false;
      }
      if (exactLength && value.length !== exactLength) {
          $(`#${fieldId}-error`).text(`${fieldName} must be exactly ${exactLength} digits`).show();
          $(`#${fieldId}`).addClass('is-invalid');
          return false;
      }
      return true;
    }

    // Validate email format
    function validateEmail() {
      const email = $('#email').val().trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
          $('#email-error').text('Please enter a valid email address').show();
          $('#email').addClass('is-invalid');
          return false;
      }
      return true;
    }

    // Validate password strength
    function validatePassword() {
      const password = $('#password').val();
      const confirmPassword = $('#confirm-password').val();
      
      if (password.length < 8 || password.length > 10) {
          $('#password-error').text('Password must be 8-10 characters long').show();
          $('#password').addClass('is-invalid');
          return false;
      }
      
      if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
          $('#password-error').text('Password must contain uppercase, lowercase, and numbers').show();
          $('#password').addClass('is-invalid');
          return false;
      }
      
      if (password !== confirmPassword) {
          $('#confirm-password-error').text('Passwords do not match').show();
          $('#confirm-password').addClass('is-invalid');
          return false;
      }
      
      return true;
    }

    // Validate date of birth (must be in the past)
    function validateDOB() {
      const dob = new Date($('#dob').val());
      const today = new Date();
      if (dob >= today) {
          $('#dob-error').text('Date of birth must be in the past').show();
          $('#dob').addClass('is-invalid');
          return false;
      }
      return true;
    }

    // Validate address length
    function validateAddress() {
      const address = $('#address').val().trim();
      if (address.length > 255) {
          $('#address-error').text('Address must be 255 characters or less').show();
          $('#address').addClass('is-invalid');
          return false;
      }
      return true;
    }

    $(document).ready(function() {
      // Form validation and submission
      $('#registration-form').submit(function(e) {
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
        
        if (!isValid) {
            showAlert('danger', 'Please fill all required fields');
            return;
        }
        
        // Validate name fields (no numbers)
        isValid = validateNameField('first-name', 'First name') && isValid;
        isValid = validateNameField('middle-name', 'Middle name') && isValid;
        isValid = validateNameField('last-name', 'Last name') && isValid;
        isValid = validateNameField('state', 'State') && isValid;
        isValid = validateNameField('district', 'District') && isValid;
        
        // Validate numeric fields
        isValid = validateNumericField('phone', 'Phone number', 10) && isValid;
        isValid = validateNumericField('pincode', 'Pincode', 6) && isValid;
        
        // Validate other fields
        isValid = validateEmail() && isValid;
        isValid = validatePassword() && isValid;
        isValid = validateDOB() && isValid;
        isValid = validateAddress() && isValid;
        
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
            url: 'includes/signup.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    // Reset form on success
                    $('#registration-form')[0].reset();
                    // Redirect after 2 seconds
                    setTimeout(() => {
                        window.location.href = "admin_dashboard.php";
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
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                }
                showAlert('danger', errorMessage);
            },
            complete: function() {
                $('#submit-btn').prop('disabled', false);
                $('#submit-text').html('<i class="fas fa-user-plus"></i> Register User');
                $('#submit-spinner').hide();
            }
        });
      });

      // Password toggle click handlers
      $('.password-toggle').click(function() {
          const fieldId = $(this).closest('.input-container').find('input').attr('id');
          togglePassword(fieldId);
      });

      // Prevent entering numbers in name fields
      $('input[name="first_name"], input[name="middle_name"], input[name="last_name"], input[name="state"], input[name="district"]').on('input', function() {
          this.value = this.value.replace(/[0-9]/g, '');
      });

      // Prevent entering non-numeric characters in phone and pincode fields
      $('input[name="phone"], input[name="pincode"]').on('input', function() {
          this.value = this.value.replace(/\D/g, '');
      });
    });
  </script>
</body>
</html>