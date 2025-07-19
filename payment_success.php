<?php
include 'scripts/connect.php';
include 'session.php';

// Check if appointment ID was provided
if (!isset($_GET['appointment_id'])) {
    header("Location: doctors.php");
    exit();
}

$appointment_id = $_GET['appointment_id'];

try {
    // Get appointment details
    $stmt = $db->prepare("
        SELECT a.*, d.name as doctor_name, d.specialization, 
               s.shop_name, p.amount, p.transaction_id
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        JOIN payments p ON a.payment_id = p.payment_id
        LEFT JOIN shopdetails s ON d.shop_id = s.id
        WHERE a.appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);
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
    <!-- Head content similar to payment.php -->
    <title>Payment Success | MediBridge</title>
    <style>
        .success-icon {
            font-size: 5rem;
            color: var(--success);
            margin-bottom: 2rem;
            animation: bounce 1s;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-30px); }
            60% { transform: translateY(-15px); }
        }
        .appointment-details {
            background-color: var(--primary-light);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-top: 2rem;
            border-left: 4px solid var(--success);
        }
    </style>
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <?php include 'templates/nav.php'; ?>

    <main class="container my-5">
        <div class="text-center">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="mb-3">Payment Successful!</h1>
            <p class="lead mb-4">Your appointment has been confirmed.</p>
            
            <div class="appointment-details text-start">
                <h3 class="mb-4">Appointment Details</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($appointment['specialization']); ?></p>
                        <p><strong>Clinic:</strong> <?php echo htmlspecialchars($appointment['shop_name'] ?? 'MediBridge Clinic'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($appointment['appointment_time']); ?></p>
                        <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($appointment['transaction_id']); ?></p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <p class="h4">Amount Paid: ₹<?php echo number_format($appointment['amount'], 2); ?></p>
                </div>
            </div>
            
            <div class="mt-5">
                <a href="dashboard.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                </a>
                <a href="#" class="btn btn-outline-primary btn-lg ms-3">
                    <i class="fas fa-calendar-alt me-2"></i> Add to Calendar
                </a>
            </div>
        </div>
    </main>

    <?php include 'templates/footer.php'; ?>
</body>
</html>