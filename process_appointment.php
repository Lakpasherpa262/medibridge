<?php
// Start session and ensure no output before headers
session_start();
ob_start();

// Set proper error reporting
error_reporting(0); // Turn off error display (log them instead in production)
header('Content-Type: application/json');

include '../scripts/connect.php';

// Check if all required data is present
$required_fields = ['doctor_id', 'appointment_date', 'appointment_time', 'consultant_fee', 'user_id'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
        exit();
    }
}

// Verify session user matches posted user - USING user_id NOT id
if (!isset($_SESSION['id']) || $_SESSION['id'] != $_POST['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Session expired or invalid user']);
    exit();
}

try {
    $user_id = $_SESSION['id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $consultant_fee = $_POST['consultant_fee'];
    
    // Check if the time slot is available
    $stmt = $db->prepare("SELECT * FROM appointments 
                         WHERE doctor_id = ? 
                         AND appointment_date = ? 
                         AND appointment_time = ?
                         AND status != 'cancelled'");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('This time slot is already booked. Please choose another time.');
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    // Insert the appointment
    $stmt = $db->prepare("INSERT INTO appointments 
                         (user_id, doctor_id, appointment_date, appointment_time, 
                          status, consultant_fee, created_at) 
                         VALUES (?, ?, ?, ?, 'confirmed', ?, NOW())");
    $stmt->execute([
        $user_id,
        $doctor_id,
        $appointment_date,
        $appointment_time,
        $consultant_fee
    ]);
    
    $appointment_id = $db->lastInsertId();
    
    // Commit transaction
    $db->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Appointment booked successfully',
        'appointment_id' => $appointment_id
    ]);
    
} catch (PDOException $e) {
    // Roll back transaction if error occurs
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Roll back transaction if error occurs
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Clean any output buffer
ob_end_flush();
?>