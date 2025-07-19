<?php
include 'scripts/connect.php';
include 'session.php';
// Initialize user variable
$user = null;

// Get user details if logged in
if (isset($_SESSION['id'])) {
    $userStmt = $db->prepare("SELECT first_name FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
}
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['id'])) {
  echo json_encode(['success' => false, 'message' => 'User not logged in']);
  exit();
}

$userId = $_SESSION['id'];
$pharmacies = $db->query("SELECT id, shop_name FROM shopdetails")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Prescription | MediBridge</title>
<!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link rel="stylesheet" href="css/style.css">
 
  <!-- Custom CSS -->
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --primary-light: #eef2ff;
      --secondary: #4cc9f0;
      --success: #4bb543;
      --danger: #ff3333;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border-radius: 12px;
      --box-shadow: 0 10px 30px rgba(67, 97, 238, 0.15);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8fafc;
      color: var(--dark);
      line-height: 1.6;
    }
    
    /* Header styles */
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--primary);
    }
    
    .navbar-brand span {
      color: var(--secondary);
    }
    
    /* Main container */
    .main-container {
      max-width: 1000px;
      margin: 2rem auto;
      padding: 0 1rem;
    }
    
    /* Prescription card */
    .prescription-card {
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      transition: var(--transition);
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      padding: 1.5rem;
      text-align: center;
    }
    
    .card-header h1 {
      font-weight: 600;
      margin-bottom: 0;
      font-size: 1.8rem;
    }
    
    .card-header i {
      margin-right: 0.8rem;
    }
    
    .card-body {
      padding: 2.5rem;
    }
    
    /* Step indicator */
    .step-indicator {
      display: flex;
      justify-content: space-between;
      margin-bottom: 3rem;
      position: relative;
    }
    
    .step-indicator:before {
      content: '';
      position: absolute;
      top: 20px;
      left: 0;
      right: 0;
      height: 3px;
      background: #e9ecef;
      z-index: 1;
    }
    
    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
      z-index: 2;
      flex: 1;
    }
    
    .step-number {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: #e9ecef;
      color: var(--gray);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      margin-bottom: 0.8rem;
      position: relative;
      z-index: 2;
      transition: var(--transition);
      border: 3px solid white;
    }
    
    .step.active .step-number {
      background: var(--primary);
      color: white;
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    
    .step.completed .step-number {
      background: var(--success);
      color: white;
    }
    
    .step.completed .step-number:before {
      content: '\f00c';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
    }
    
    .step-label {
      font-weight: 500;
      color: var(--gray);
      text-align: center;
      font-size: 0.9rem;
    }
    
    .step.active .step-label {
      color: var(--primary);
      font-weight: 600;
    }
    
    .step.completed .step-label {
      color: var(--success);
    }
    
    /* Upload area */
    .upload-area {
      border: 2px dashed #b8c2ff;
      border-radius: var(--border-radius);
      padding: 3rem 2rem;
      text-align: center;
      background-color: var(--primary-light);
      transition: var(--transition);
      margin-bottom: 2rem;
      cursor: pointer;
    }
    
    .upload-area:hover {
      border-color: var(--primary);
      background-color: rgba(67, 97, 238, 0.05);
      transform: translateY(-2px);
    }
    
    .upload-area.dragover {
      border-color: var(--primary);
      background-color: rgba(67, 97, 238, 0.1);
    }
    
    .upload-icon {
      font-size: 3.5rem;
      color: var(--primary);
      margin-bottom: 1rem;
    }
    
    .upload-text {
      font-size: 1.2rem;
      color: var(--dark);
      margin-bottom: 0.5rem;
      font-weight: 500;
    }
    
    .upload-subtext {
      color: var(--gray);
      font-size: 0.9rem;
    }
    
    .file-input {
      display: none;
    }
    
    .browse-btn {
      background-color: var(--primary);
      color: white;
      padding: 0.6rem 1.8rem;
      border-radius: 8px;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
      display: inline-block;
      margin-top: 1rem;
      border: none;
    }
    
    .browse-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    
    /* File info */
    .file-info-container {
      background: white;
      padding: 1.5rem;
      border-radius: var(--border-radius);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 2rem;
      display: none;
      animation: fadeIn 0.5s ease;
    }
    
    .file-info {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .file-details {
      display: flex;
      align-items: center;
    }
    
    .file-icon {
      font-size: 1.8rem;
      margin-right: 1rem;
      color: var(--primary);
    }
    
    .file-name {
      font-weight: 500;
      margin-bottom: 0.2rem;
    }
    
    .file-size {
      color: var(--gray);
      font-size: 0.85rem;
    }
    
    .remove-file {
      background: none;
      border: none;
      color: var(--danger);
      font-size: 1.2rem;
      cursor: pointer;
      transition: transform 0.2s;
      padding: 0.5rem;
    }
    
    .remove-file:hover {
      transform: scale(1.1);
    }
    
    /* Preview */
    .preview-container {
      margin-top: 2rem;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      display: none;
      animation: fadeIn 0.5s ease;
    }
    
    .prescription-image {
      max-width: 100%;
      max-height: 400px;
      display: block;
      margin: 0 auto;
      object-fit: contain;
    }
    
    /* Form sections */
    .form-section {
      display: none;
      animation: fadeIn 0.5s ease;
    }
    
    .form-section.active {
      display: block;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Form elements */
    .section-title {
      color: var(--primary);
      margin-bottom: 1.5rem;
      font-weight: 600;
      display: flex;
      align-items: center;
    }
    
    .section-title i {
      margin-right: 0.8rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }
    
    .form-label.required:after {
      content: " *";
      color: var(--danger);
    }
    
  /* Form elements */
.form-control, .form-select {
  padding: 0.8rem 1rem;
  border-radius: 8px;
  border: 1px solid #ced4da;
  transition: var(--transition);
  background-color: white;  /* Add this line */
  color: var(--dark);       /* Add this line */
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
  background-color: white;  /* Add this line to ensure focus state is also visible */
}

/* Special instructions textarea */
.special-instructions textarea {
  background-color: white;
  min-height: 120px;
  color: var(--dark);       /* Add this line */
}

/* Invalid feedback text */
.invalid-feedback {
  color: var(--danger);     /* Make sure error messages are visible */
}  
    /* Special instructions */
    .special-instructions {
      background-color: var(--primary-light);
      border-radius: var(--border-radius);
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    
    
    /* Buttons */
    .btn-primary {
      background-color: var(--primary);
      border-color: var(--primary);
      padding: 0.8rem 2rem;
      border-radius: 8px;
      font-weight: 500;
      transition: var(--transition);
      letter-spacing: 0.5px;
    }
    
    .btn-primary:hover {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    
    .btn-outline-secondary {
      padding: 0.8rem 2rem;
      border-radius: 8px;
      font-weight: 500;
      transition: var(--transition);
      letter-spacing: 0.5px;
    }
    
    .btn-outline-secondary:hover {
      transform: translateY(-2px);
    }
    
    /* Action buttons */
    .action-buttons {
      display: flex;
      justify-content: space-between;
      margin-top: 2rem;
    }
    
    /* Loading spinner */
    .spinner {
      display: inline-block;
      width: 1.5rem;
      height: 1.5rem;
      border: 3px solid rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      border-top-color: white;
      animation: spin 1s ease-in-out infinite;
      margin-right: 0.5rem;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    /* Success modal */
    .success-modal {
      background: rgba(0, 0, 0, 0.5);
    }
    
    .success-modal-content {
      border: none;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .success-icon {
      font-size: 5rem;
      color: var(--success);
      margin-bottom: 1.5rem;
      animation: bounceIn 0.6s;
    }
    
    .success-modal-body {
      padding: 3rem;
      text-align: center;
    }
    
    .success-modal-body h3 {
      font-weight: 600;
      margin-bottom: 1rem;
    }
    
    .progress {
      height: 6px;
      border-radius: 3px;
      margin: 1.5rem 0;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .card-body {
        padding: 1.5rem;
      }
      
      .upload-area {
        padding: 2rem 1rem;
      }
      
      .step-label {
        font-size: 0.8rem;
      }
      
      .action-buttons {
        flex-direction: column-reverse;
        gap: 1rem;
      }
      
      .action-buttons .btn {
        width: 100%;
      }
    }
    
    /* Drag and drop animations */
    @keyframes bounceIn {
      0% { transform: scale(0.1); opacity: 0; }
      60% { transform: scale(1.2); opacity: 1; }
      100% { transform: scale(1); }
    }
    
    /* Alert styles */
    .alert-prescription {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      animation: slideInRight 0.3s, fadeOut 0.5s 4.5s forwards;
    }
    
    @keyframes slideInRight {
      from { transform: translateX(100%); }
      to { transform: translateX(0); }
    }
    
    @keyframes fadeOut {
      to { opacity: 0; }
    }
      </style>
</head>
<body>
<!-- Include PHP Components -->
<?php include 'templates/header.php'; ?>


<main class="container my-5">
  <!-- Upload Prescription Form -->
  <form id="prescriptionForm" method="POST" enctype="multipart/form-data" novalidate>
    <div class="prescription-container">
      <h1 class="text-center mb-4" style="color: var(--primary-color);">
        <i class="fas fa-prescription-bottle-alt me-2"></i> Upload Prescription
      </h1>
      
      <!-- Step Indicator -->
      <div class="step-indicator">
        <div class="step active" data-step="1">
          <div class="step-number">1</div>
          <div class="step-label">Upload Prescription</div>
        </div>
        <div class="step" data-step="2">
          <div class="step-number">2</div>
          <div class="step-label">Your Details</div>
        </div>
        <div class="step" data-step="3">
          <div class="step-number">3</div>
          <div class="step-label">Confirmation</div>
        </div>
      </div>
      
      <!-- Upload Section -->
      <div id="uploadSection" class="form-section active">
        <div id="uploadArea" class="upload-area">
          <div class="upload-icon">
            <i class="fas fa-file-medical"></i>
          </div>
          <h4 class="upload-text">Upload your prescription</h4>
          <p class="upload-subtext">Supports: JPG, PNG, PDF (Max 5MB)</p>
          <label for="fileInput" class="browse-btn">
            <i class="fas fa-folder-open me-2"></i> Browse Files
          </label>
          <input type="file" id="fileInput" name="prescriptionFile" class="file-input" 
                 accept="image/*,.pdf" required>
        </div>
        
        <!-- File Info Container (shown after file selection) -->
        <div id="fileInfoContainer" class="file-info-container">
          <div class="file-info">
            <div class="file-details">
              <div class="file-icon">
                <i id="fileTypeIcon" class="fas fa-file"></i>
              </div>
              <div>
                <div id="fileName" class="file-name">filename.pdf</div>
                <div id="fileSize" class="file-size">2.4 MB</div>
              </div>
            </div>
            <button type="button" id="removeFileBtn" class="remove-file">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        
        <!-- Preview Container (for images only) -->
        <div id="previewContainer" class="prescription-preview-container">
          <img id="previewImage" class="prescription-image" alt="Prescription preview">
        </div>
        
        <!-- Special Instructions Section -->
        <div class="special-instructions">
          <h5><i class="fas fa-notes-medical me-2"></i> Special Instructions</h5>
          <textarea class="form-control" id="specialInstructions" name="specialInstructions" 
                    rows="3" placeholder="Any special instructions for the pharmacy..."></textarea>
        </div>
        
        <div class="text-center mt-4">
          <button id="continueBtn" class="btn btn-primary px-4" type="button" disabled>
            Continue <i class="fas fa-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
      
      <!-- Details Section -->
      <div id="detailsSection" class="form-section">
        <h3 class="section-title">
          <i class="fas fa-user-circle"></i> Personal Details
        </h3>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="form-group required">
              <label for="patientName" class="form-label">Full Name</label>
              <input type="text" class="form-control" id="patientName" name="patientName" 
                     pattern="^[a-zA-Z\s]*$" title="Only letters and spaces allowed" required>
              <div class="invalid-feedback">Please enter your full name</div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="form-group required">
              <label for="patientEmail" class="form-label">Email Address</label>
              <input type="email" class="form-control" id="patientEmail" name="patientEmail" required>
              <div class="invalid-feedback">Please enter a valid email address</div>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="form-group required">
              <label for="patientPhone" class="form-label">Phone Number</label>
              <input type="tel" class="form-control" id="patientPhone" name="patientPhone" 
                     pattern="[0-9]{10}" title="10 digit phone number" required>
              <div class="invalid-feedback">Please enter a valid 10-digit phone number</div>
            </div>
          </div>
          
        <!-- Pharmacy Selection -->
        <div class="mb-4">
          <label class="form-label required">Select Pharmacy</label>
          <select class="form-select" name="pharmacyId" required>
            <option value="">-- Select a Pharmacy --</option>
            <?php foreach ($pharmacies as $pharmacy): ?>
              <option value="<?= $pharmacy['id'] ?>"><?= htmlspecialchars($pharmacy['shop_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">Please select a pharmacy</div>
        </div>
        
        
        <div class="action-buttons">
          <button type="button" id="backToUploadBtn" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
          </button>
          <button type="submit" id="submitPrescriptionBtn" class="btn btn-primary px-4">
            Submit Prescription <i class="fas fa-paper-plane ms-2"></i>
          </button>
        </div>
      </div>
    </div>
  </form>
</main>

<?php include 'templates/footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
  // DOM elements
  const fileInput = $('#fileInput');
  const previewImage = $('#previewImage');
  const fileInfoContainer = $('#fileInfoContainer');
  const fileName = $('#fileName');
  const fileSize = $('#fileSize');
  const fileTypeIcon = $('#fileTypeIcon');
  const previewContainer = $('#previewContainer');
  const continueBtn = $('#continueBtn');
  const removeFileBtn = $('#removeFileBtn');
  
  // Handle file selection
  fileInput.on('change', handleFileSelection);
  
  function handleFileSelection() {
    if (fileInput[0].files && fileInput[0].files[0]) {
      const file = fileInput[0].files[0];
      
      // Validate file type
      const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
      if (!validTypes.includes(file.type)) {
        showAlert('Invalid file type. Please upload an image (JPG, PNG) or PDF.');
        return;
      }
      
      // Validate file size (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        showAlert('File size should be less than 5MB.');
        return;
      }
      
      // Display file info
      displayFileInfo(file);
      
      // Display preview for images
      if (file.type.includes('image')) {
        displayImagePreview(file);
      } else {
        previewContainer.hide();
      }
      
      // Enable continue button
      continueBtn.prop('disabled', false);
      fileInput.removeClass('is-invalid');
    }
  }

  function displayFileInfo(file) {
    fileInfoContainer.show();
    fileName.text(file.name);
    fileSize.text(formatFileSize(file.size));
    
    // Set appropriate icon
    if (file.type.includes('image')) {
      fileTypeIcon.removeClass().addClass('fas fa-file-image');
    } else if (file.type.includes('pdf')) {
      fileTypeIcon.removeClass().addClass('fas fa-file-pdf');
    } else {
      fileTypeIcon.removeClass().addClass('fas fa-file');
    }
  }

  function displayImagePreview(file) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      previewImage.attr('src', e.target.result);
      previewContainer.show();
    };
    
    reader.readAsDataURL(file);
  }
  
  // Remove file functionality
  removeFileBtn.on('click', function() {
    resetFileUpload();
  });

  function resetFileUpload() {
    fileInput.val('');
    fileInfoContainer.hide();
    previewContainer.hide();
    continueBtn.prop('disabled', true);
  }
  
  // Format file size
  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }
  
  // Show alert message
  function showAlert(message) {
    // Remove any existing alerts first
    $('.alert').remove();
    
    const alert = `
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;
    $('.prescription-container').prepend(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      $('.alert').alert('close');
    }, 5000);
  }
  
  // Form navigation
  continueBtn.on('click', function() {
    // Validate upload section first
    if (!fileInput[0].files.length) {
      fileInput.addClass('is-invalid');
      showAlert('Please upload a prescription file to continue');
      return;
    }
    
    // Switch to details section
    switchToSection('details');
    updateStepIndicator(2);
  });
  
  $('#backToUploadBtn').on('click', function() {
    switchToSection('upload');
    updateStepIndicator(1);
  });

  function switchToSection(section) {
    $('.form-section').removeClass('active');
    $(`#${section}Section`).addClass('active');
  }
  
  function updateStepIndicator(step) {
    $('.step').removeClass('active completed');
    
    $('.step').each(function(index) {
      const stepElement = $(this);
      if (index + 1 < step) {
        stepElement.addClass('completed');
      } else if (index + 1 === step) {
        stepElement.addClass('active');
      }
    });
  }
  
  // Form submission with AJAX
  $('#prescriptionForm').on('submit', function(e) {
    e.preventDefault();
    
    // Validate form
    if (this.checkValidity() === false) {
      e.stopPropagation();
      $(this).addClass('was-validated');
      return;
    }
    
    // Create FormData object
    const formData = new FormData(this);
    
    // Submit via AJAX
    submitPrescription(formData);
  });

  // Update the submitPrescription function in your existing JavaScript
function submitPrescription(formData) {
    $.ajax({
        url: 'includes/submit_prescription.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#submitPrescriptionBtn').prop('disabled', true).html('<span class="spinner"></span> Processing...');
        },
        success: function(response) {
            handleSubmissionResponse(response);
        },
        error: function(xhr, status, error) {
            handleSubmissionError(error);
        }
    });
}

function handleSubmissionResponse(response) {
    try {
        if (typeof response === 'string') {
            response = JSON.parse(response);
        }
        
        if (response.success) {
            // If we have notification data, send the notification
            if (response.notificationData) {
                sendNotification(response.notificationData)
                    .then(() => showSuccessModal())
                    .catch(error => {
                        console.error('Notification error:', error);
                        showSuccessModal(); // Still show success even if notification fails
                    });
            } else {
                showSuccessModal();
            }
            
            setTimeout(function() {
                window.location.href = response.redirectUrl || 'dashboard.php';
            }, 3000);
        } else {
            showSubmissionError(response.message);
        }
    } catch (e) {
        console.error('Error parsing response:', e, 'Response:', response);
        showSubmissionError('Error processing response');
    }
}

function sendNotification(notificationData) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'includes/send_notification.php',
            type: 'POST',
            data: notificationData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    resolve(response);
                } else {
                    reject(response.message);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}
  
  // Show success modal
  function showSuccessModal() {
    const modalHtml = `
      <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content success-modal-content">
            <div class="modal-body text-center p-5">
              <div class="success-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <h3 class="mb-3">Prescription Uploaded Successfully!</h3>
              <p class="mb-4">Your prescription has been received and is being processed.</p>
              <div class="progress" style="height: 6px; border-radius: 3px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
              </div>
              <p class="mt-3 small text-muted">Redirecting to dashboard...</p>
            </div>
          </div>
        </div>
      </div>
    `;
    
    $('body').append(modalHtml);
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
  }
  
  // Input validation for names (letters only)
  $('#patientName').on('input', function() {
    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
  });
  
  // Input validation for phone (numbers only, max 10 digits)
  $('#patientPhone').on('input', function() {
    this.value = this.value.replace(/\D/g, '').substring(0, 10);
  });

  // Initialize any default values if needed
  function initForm() {
    // You can add any initialization logic here
    // For example, pre-fill user details if available
    <?php if(isset($_SESSION['user_email'])): ?>
      $('#patientEmail').val('<?php echo $_SESSION['user_email']; ?>');
    <?php endif; ?>
  }

  // Call initialization
  initForm();
});
</script>
</body>
</html>