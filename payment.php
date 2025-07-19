<?php
// Start session at the very top
session_start();

include 'scripts/connect.php';

// Check if appointment data was passed
if (!isset($_POST['doctor_id'], $_POST['appointment_date'], $_POST['appointment_time'])) {
    die(json_encode(['success' => false, 'message' => 'Missing appointment data']));
}

// Verify user is logged in
if (!isset($_SESSION['id'])) {
    die(json_encode(['success' => false, 'message' => 'User not logged in']));
}

// Get doctor details
$doctor_id = $_POST['doctor_id'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];
$user_id = $_SESSION['id'];

try {
    $stmt = $db->prepare("SELECT d.*, s.shop_name, sp.specialization_name 
                         FROM doctors d
                         LEFT JOIN shopdetails s ON d.shop_id = s.id
                         LEFT JOIN specializations sp ON d.specialization = sp.id
                         WHERE d.doctor_id = ?");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        die(json_encode(['success' => false, 'message' => 'Doctor not found']));
    }
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database error']));
}

// Calculate appointment end time (30 minutes after start time)
$appointment_end = date("h:i A", strtotime($appointment_time) + 1800);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secure Payment | MediBridge</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --primary-light: #eef2ff;
      --secondary: #4cc9f0;
      --success: #4bb543;
      --danger: #ff3333;
      --warning: #ffc107;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border-radius: 12px;
      --box-shadow: 0 10px 30px rgba(67, 97, 238, 0.15);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8fafc;
      color: var(--dark);
      line-height: 1.6;
    }
    
    /* Payment Container */
    .payment-container {
      max-width: 1000px;
      margin: 2rem auto;
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
    }
    
    /* Header Section */
    .payment-header {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      padding: 2rem;
      text-align: center;
    }
    
    .payment-header h2 {
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    
    .payment-header p {
      opacity: 0.9;
      font-weight: 300;
    }
    
    /* Body Section */
    .payment-body {
      padding: 2.5rem;
    }
    
    /* Appointment Summary */
    .appointment-summary {
      background-color: var(--primary-light);
      border-radius: var(--border-radius);
      padding: 2rem;
      margin-bottom: 2.5rem;
      border-left: 4px solid var(--primary);
    }
    
    .summary-title {
      color: var(--primary);
      font-weight: 600;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
    }
    
    .summary-title i {
      margin-right: 0.8rem;
      font-size: 1.2rem;
    }
    
    .doctor-info-card {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    
    .doctor-image-sm {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 1.5rem;
      border: 3px solid #e0e6ed;
    }
    
    .doctor-info-text h5 {
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    
    .doctor-info-text p {
      color: var(--gray);
      margin-bottom: 0;
    }
    
    .appointment-details {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-top: 1.5rem;
    }
    
    .detail-item {
      margin-bottom: 0.5rem;
    }
    
    .detail-label {
      font-weight: 500;
      color: var(--gray);
      font-size: 0.9rem;
    }
    
    .detail-value {
      font-weight: 500;
      color: var(--dark);
    }
    
    .amount-highlight {
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--success);
    }
    
    /* Payment Methods */
    .payment-methods {
      margin-bottom: 2rem;
    }
    
    .section-title {
      color: var(--primary);
      font-weight: 600;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
    }
    
    .section-title i {
      margin-right: 0.8rem;
      font-size: 1.2rem;
    }
    
    /* Payment Method Cards */
    .payment-method-card {
      border: 1px solid #e0e6ed;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      margin-bottom: 1rem;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
    }
    
    .payment-method-card:hover {
      border-color: var(--primary);
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
    }
    
    .payment-method-card.selected {
      border-color: var(--primary);
      background-color: rgba(67, 97, 238, 0.05);
    }
    
    .payment-method-icon {
      font-size: 2rem;
      margin-right: 1.2rem;
      color: var(--primary);
      width: 50px;
      text-align: center;
    }
    
    .payment-method-info h5 {
      font-weight: 600;
      margin-bottom: 0.3rem;
    }
    
    .payment-method-info p {
      color: var(--gray);
      font-size: 0.9rem;
      margin-bottom: 0;
    }
    
    /* Credit Card Form */
    .card-form {
      display: none;
      animation: fadeIn 0.5s ease;
    }
    
    .card-form.active {
      display: block;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Credit Card Preview */
    .credit-card-preview {
      background: linear-gradient(135deg, #4a6bff, #2a52be);
      border-radius: var(--border-radius);
      padding: 1.5rem;
      color: white;
      margin-bottom: 1.5rem;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
      height: 200px;
    }
    
    .credit-card-preview::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }
    
    .credit-card-preview::after {
      content: '';
      position: absolute;
      bottom: -30%;
      right: -20%;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }
    
    .card-chip {
      width: 50px;
      margin-bottom: 1.5rem;
    }
    
    .card-number-display {
      font-size: 1.4rem;
      letter-spacing: 2px;
      margin-bottom: 1.5rem;
      font-family: 'Courier New', monospace;
      word-spacing: 8px;
    }
    
    .card-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .card-name-display {
      font-size: 1rem;
      text-transform: uppercase;
      opacity: 0.9;
    }
    
    .card-expiry-display {
      font-size: 1rem;
      opacity: 0.9;
    }
    
    /* Form Elements */
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .form-control {
      padding: 0.8rem 1rem;
      border-radius: 8px;
      border: 1px solid #ced4da;
      transition: var(--transition);
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
    }
    
    .input-with-icon {
      position: relative;
    }
    
    .card-type-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      height: 25px;
    }
    
    /* Payment Button */
    .btn-pay {
      background-color: var(--success);
      color: white;
      border: none;
      padding: 1rem;
      border-radius: 8px;
      font-weight: 500;
      width: 100%;
      margin-top: 1rem;
      transition: var(--transition);
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .btn-pay:hover {
      background-color: #3d9c3a;
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(75, 181, 67, 0.3);
    }
    
    .btn-pay i {
      margin-right: 0.8rem;
    }
    
    .btn-pay:disabled {
      background-color: #95a5a6;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    
    /* Error Handling */
    .error-message {
      color: var(--danger);
      font-size: 0.85rem;
      margin-top: 0.5rem;
      display: none;
    }
    
    .has-error .form-control {
      border-color: var(--danger);
    }
    
    .has-error .error-message {
      display: block;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .payment-container {
        margin: 0;
        border-radius: 0;
      }
      
      .payment-body {
        padding: 1.5rem;
      }
      
      .doctor-info-card {
        flex-direction: column;
        text-align: center;
      }
      
      .doctor-image-sm {
        margin-right: 0;
        margin-bottom: 1rem;
      }
      
      .appointment-details {
        grid-template-columns: 1fr;
      }
      
      .credit-card-preview {
        height: 180px;
      }
      
      .card-number-display {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>

<?php include 'templates/header.php'; ?>
<main class="payment-container">
  <div class="payment-header">
    <h2><i class="fas fa-lock"></i> Secure Payment</h2>
    <p>Your payment information is encrypted and secure</p>
  </div>
  
  <div class="payment-body">
    <div class="appointment-summary">
      <h4 class="summary-title"><i class="fas fa-calendar-check"></i> Appointment Summary</h4>
      
      <div class="doctor-info-card">
        <img src="<?php echo !empty($doctor['image_path']) ? 'uploads/doctors/' . basename($doctor['image_path']) : 'uploads/doctors/default.png'; ?>" 
             alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>"
             class="doctor-image-sm">
        <div class="doctor-info-text">
          <h5>Dr. <?php echo htmlspecialchars($doctor['name']); ?></h5>
          <p><?php echo htmlspecialchars($doctor['specialization_name'] ?? 'General Physician'); ?></p>
          <p><?php echo htmlspecialchars($doctor['shop_name'] ?? 'MediBridge Clinic'); ?></p>
        </div>
      </div>
      
      <div class="appointment-details">
        <div class="detail-item">
          <span class="detail-label">Appointment Date:</span>
          <span class="detail-value"><?php echo htmlspecialchars($appointment_date); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Appointment Time:</span>
          <span class="detail-value"><?php echo htmlspecialchars($appointment_time); ?></span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Consultation Fee:</span>
          <span class="detail-value amount-highlight">₹<?php echo number_format($doctor['fee'], 2); ?></span>
        </div>
      </div>
    </div>
    
   <form id="paymentForm" method="post" action="process_payment.php">
  <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
  <input type="hidden" name="appointment_date" value="<?php echo $appointment_date; ?>">
  <input type="hidden" name="appointment_time" value="<?php echo $appointment_time; ?>">
  <input type="hidden" name="consultant_fee" value="<?php echo $doctor['fee']; ?>">
  <input type="hidden" name="user_id" value="<?php echo $_SESSION['id']; ?>">
      
      <div class="payment-methods">
        <h4 class="section-title"><i class="fas fa-credit-card"></i> Payment Method</h4>
        
        <div class="payment-method-card selected" data-method="credit-card">
          <div class="payment-method-icon">
            <i class="far fa-credit-card"></i>
          </div>
          <div class="payment-method-info">
            <h5>Credit/Debit Card</h5>
            <p>Pay with Visa, Mastercard, American Express, etc.</p>
          </div>
        </div>
      </div>
      
      <div class="card-form active" id="creditCardForm">
        <h4 class="section-title"><i class="fas fa-credit-card"></i> Card Details</h4>
        
        <div class="credit-card-preview">
          <img src="https://cdn-icons-png.flaticon.com/512/196/196578.png" alt="Chip" class="card-chip">
          <div class="card-number-display" id="cardNumberDisplay">•••• •••• •••• ••••</div>
          <div class="card-bottom">
            <div class="card-name-display" id="cardNameDisplay">CARDHOLDER NAME</div>
            <div class="card-expiry-display" id="cardExpiryDisplay">••/••</div>
          </div>
        </div>
        
        <div class="form-group" id="cardNameGroup">
          <label for="cardName" class="form-label">Cardholder Name</label>
          <input type="text" class="form-control" id="cardName" name="card_name" 
                 placeholder="Name as it appears on your card" required>
          <div class="error-message" id="cardNameError">Please enter cardholder name</div>
        </div>
        
        <div class="form-group" id="cardNumberGroup">
          <label for="cardNumber" class="form-label">Card Number</label>
          <div class="input-with-icon">
            <input type="text" class="form-control" id="cardNumber" name="card_number" 
                   placeholder="1234 5678 9012 3456" required>
            <img src="https://cdn-icons-png.flaticon.com/512/196/196578.png" alt="Card" 
                 class="card-type-icon" id="cardTypeIcon">
            <div class="error-message" id="cardNumberError">Please enter a valid 16-digit card number</div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <div class="form-group" id="expiryDateGroup">
              <label for="expiryDate" class="form-label">Expiry Date</label>
              <input type="text" class="form-control" id="expiryDate" name="expiry_date" 
                     placeholder="MM/YY" required>
              <div class="error-message" id="expiryDateError">Please enter a valid expiry date (MM/YY)</div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="form-group" id="cvvGroup">
              <label for="cvv" class="form-label">CVV</label>
              <div class="input-with-icon">
                <input type="password" class="form-control" id="cvv" name="cvv" 
                       placeholder="123" required>
                <i class="fas fa-question-circle card-icon" 
                   title="3-digit security code on back of card"></i>
                <div class="error-message" id="cvvError">Please enter a valid 3-digit CVV</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <button type="submit" class="btn btn-pay" id="payButton">
        <i class="fas fa-lock"></i> Book Now ₹<?php echo number_format($doctor['fee'], 2); ?>
      </button>
    </form>
  </div>
</main>

<?php include 'templates/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
  // Form validation and UI interactions
  $('.payment-method-card').click(function() {
    $('.payment-method-card').removeClass('selected');
    $(this).addClass('selected');
  });

  // Card number formatting
  $('#cardNumber').on('input', function() {
    let value = $(this).val().replace(/\D/g, '');
    if (value.length > 16) value = value.substring(0, 16);
    value = value.replace(/(\d{4})/g, '$1 ').trim();
    $(this).val(value);
    $('#cardNumberDisplay').text(value || '•••• •••• •••• ••••');
  });

  // Card name display
  $('#cardName').on('input', function() {
    $('#cardNameDisplay').text($(this).val().toUpperCase() || 'CARDHOLDER NAME');
  });

  // Expiry date formatting
  $('#expiryDate').on('input', function() {
    let value = $(this).val().replace(/\D/g, '');
    if (value.length > 4) value = value.substring(0, 4);
    if (value.length > 2) value = value.substring(0, 2) + '/' + value.substring(2, 4);
    $(this).val(value);
    $('#cardExpiryDisplay').text(value || '••/••');
  });

  // Form submission
  $('#paymentForm').submit(function(e) {
    e.preventDefault();
    
    // Validate form
    let isValid = true;
    
    // Validate card name
    if ($('#cardName').val().trim() === '') {
      $('#cardNameGroup').addClass('has-error');
      isValid = false;
    } else {
      $('#cardNameGroup').removeClass('has-error');
    }
    
    // Validate card number (16 digits)
    const cardNumber = $('#cardNumber').val().replace(/\s/g, '');
    if (!/^\d{16}$/.test(cardNumber)) {
      $('#cardNumberGroup').addClass('has-error');
      isValid = false;
    } else {
      $('#cardNumberGroup').removeClass('has-error');
    }
    
    // Validate expiry date (MM/YY format and not expired)
    const expiryDate = $('#expiryDate').val();
    if (!/^(0[1-9]|1[0-2])\/?([0-9]{2})$/.test(expiryDate)) {
      $('#expiryDateGroup').addClass('has-error');
      isValid = false;
    } else {
      const [month, year] = expiryDate.split('/');
      const currentDate = new Date();
      const currentYear = currentDate.getFullYear() % 100;
      const currentMonth = currentDate.getMonth() + 1;
      
      if (parseInt(year) < currentYear || 
          (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
        $('#expiryDateError').text('Card has expired');
        $('#expiryDateGroup').addClass('has-error');
        isValid = false;
      } else {
        $('#expiryDateGroup').removeClass('has-error');
      }
    }
    
    // Validate CVV (3 digits)
    if (!/^\d{3}$/.test($('#cvv').val())) {
      $('#cvvGroup').addClass('has-error');
      isValid = false;
    } else {
      $('#cvvGroup').removeClass('has-error');
    }
    
    if (!isValid) {
      return false;
    }
    
    // Disable button and show loading
    $('#payButton').prop('disabled', true);
    $('#payButton').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...');
    
    // Submit form via AJAX
     $.ajax({
      url: 'includes/process_appointment.php', // Corrected path
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      // After successful payment processing:
// In your AJAX success handler:
success: function(response) {
    if (response.success) {
        // Use window.location.replace() to prevent back button issues
        window.location.replace('appointment_confirmation.php?id=' + response.appointment_id);
    } else {
        // Show error message
        alert('Error: ' + response.message);
        $('#payButton').prop('disabled', false);
        $('#payButton').html('<i class="fas fa-lock"></i> Book Now ₹<?php echo number_format($doctor["fee"], 2); ?>');
    }
},
      error: function(xhr, status, error) {
        // Show more detailed error information
        alert('An error occurred: ' + error + '\nStatus: ' + status + '\nResponse: ' + xhr.responseText);
        $('#payButton').prop('disabled', false);
        $('#payButton').html('<i class="fas fa-lock"></i> Book Now ₹<?php echo number_format($doctor["fee"], 2); ?>');
      }
    });
  });
});
</script>
</body>
</html>