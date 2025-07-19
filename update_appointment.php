<?php
session_start();
require_once 'scripts/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) {
    if ($_POST['action'] === 'complete_appointment' && isset($_POST['appointment_id'])) {
        try {
            // Update the appointment status to 'completed'
            $stmt = $db->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
            $stmt->execute([$_POST['appointment_id']]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>