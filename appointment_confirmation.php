<?php
include 'scripts/connect.php';
include 'session.php';

if (!isset($_GET['id'])) {
    header("Location: appointments.php");
    exit();
}

$appointment_id = $_GET['id'];

try {
    // Get appointment details
    $stmt = $db->prepare("SELECT a.*, d.name AS doctor_name, d.image_path, 
                         sp.specialization_name, s.shop_name
                         FROM appointments a
                         JOIN doctors d ON a.doctor_id = d.doctor_id
                         LEFT JOIN specializations sp ON d.specialization = sp.id
                         LEFT JOIN shopdetails s ON d.shop_id = s.id
                         WHERE a.appointment_id = ? AND a.user_id = ?");
    $stmt->execute([$appointment_id, $_SESSION['id']]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        throw new Exception("Appointment not found");
    }
    
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointment Confirmation | MediBridge</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }
    .confirmation-container {
      max-width: 800px;
      margin: 2rem auto;
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .confirmation-header {
      background: linear-gradient(135deg, #4361ee, #3a56d4);
      color: white;
      padding: 2rem;
      text-align: center;
    }
    .confirmation-body {
      padding: 2rem;
    }
    .success-icon {
      font-size: 4rem;
      color: #4bb543;
      margin-bottom: 1rem;
    }
    .doctor-image {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #e0e6ed;
      margin: 0 auto 1rem;
    }
    .appointment-details {
      background-color: #f1f8ff;
      border-radius: 10px;
      padding: 1.5rem;
      margin: 1.5rem 0;
    }
    .detail-item {
      margin-bottom: 0.8rem;
    }
    .detail-label {
      font-weight: 500;
      color: #6c757d;
    }
    .detail-value {
      font-weight: 500;
    }
    .btn-dashboard {
      background-color: #4361ee;
      color: white;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
    }
    .btn-dashboard:hover {
      background-color: #3a56d4;
      color: white;
    }
  </style>
</head>
<body>

<?php include 'templates/header.php'; ?>

<div class="confirmation-container">
  <div class="confirmation-header">
    <h2><i class="fas fa-check-circle"></i> Appointment Confirmed</h2>
    <p>Your appointment has been successfully booked</p>
  </div>
  
  <div class="confirmation-body text-center">
    <div class="success-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    
    <h3 class="mb-4">Thank You for Booking!</h3>
    
    <img src="<?php echo !empty($appointment['image_path']) ? 'uploads/doctors/' . basename($appointment['image_path']) : 'uploads/doctors/default.png'; ?>" 
         alt="Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?>"
         class="doctor-image">
    
    <h4>Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?></h4>
    <p class="text-muted"><?php echo htmlspecialchars($appointment['specialization_name'] ?? 'General Physician'); ?></p>
    
    <div class="appointment-details text-start">
      <div class="detail-item">
        <span class="detail-label">Appointment ID:</span>
        <span class="detail-value">#MB<?php echo str_pad($appointment['appointment_id'], 6, '0', STR_PAD_LEFT); ?></span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Date:</span>
        <span class="detail-value"><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Time:</span>
        <span class="detail-value"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Location:</span>
        <span class="detail-value"><?php echo htmlspecialchars($appointment['shop_name'] ?? 'MediBridge Clinic'); ?></span>
      </div>
      <div class="detail-item">
        <span class="detail-label">Payment Amount:</span>
        <span class="detail-value text-success">₹<?php echo number_format($appointment['consultant_fee'], 2); ?></span>
      </div>
      <?php if (isset($payment) && !empty($payment)): ?>
      <div class="detail-item">
        <span class="detail-label">Transaction ID:</span>
        <span class="detail-value"><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
      </div>
      <?php endif; ?>
    </div>
    
    
    <div class="d-flex justify-content-center gap-3">
      <a href="doctor_book.php" class="btn btn-outline-primary">
        <i class="fas fa-user-md me-2"></i> Book Another
      </a>
    </div>
  </div>
</div>

<?php include 'templates/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
