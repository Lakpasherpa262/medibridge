<?php
session_start();
include 'scripts/connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: doctors.php");
    exit();
}

// Validate required fields
$required = ['doctor_id', 'appointment_date', 'appointment_time', 'amount', 'card_name', 'card_number', 'expiry_date', 'cvv', 'transaction_id'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
}

// Get user ID from session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}
$user_id = $_SESSION['user_id'];

// Process payment data
$doctor_id = $_POST['doctor_id'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];
$amount = $_POST['amount'];
$transaction_id = $_POST['transaction_id'];

try {
    $db->beginTransaction();
    
    // 1. Insert payment record
    $payment_stmt = $db->prepare("
        INSERT INTO payments (
            user_id, 
            doctor_id, 
            amount, 
            payment_method, 
            transaction_id,
            transaction_date, 
            status
        ) VALUES (?, ?, ?, 'card', ?, NOW(), 'completed')
    ");
    $payment_stmt->execute([$user_id, $doctor_id, $amount, $transaction_id]);
    $payment_id = $db->lastInsertId();
    
    // 2. Insert appointment record
    $appointment_stmt = $db->prepare("
        INSERT INTO appointments (
            user_id, 
            doctor_id, 
            appointment_date, 
            appointment_time, 
            status, 
            payment_id, 
            created_at
        ) VALUES (?, ?, ?, ?, 'confirmed', ?, NOW())
    ");
    $appointment_stmt->execute([
        $user_id, 
        $doctor_id, 
        $appointment_date, 
        $appointment_time, 
        $payment_id
    ]);
    $appointment_id = $db->lastInsertId();
    
    $db->commit();
    
    // Return success with appointment ID
    echo json_encode([
        'success' => true,
        'appointment_id' => $appointment_id,
        'message' => 'Payment and appointment booking successful'
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Payment processing error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your payment. Please try again.'
    ]);
}
?>