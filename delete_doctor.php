<?php
include '../scripts/connect.php';
header('Content-Type: application/json');

// Check if doctor_id is provided
if (!isset($_POST['doctor_id']) || empty($_POST['doctor_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Doctor ID is required']);
    exit;
}

$doctor_id = $_POST['doctor_id'];

try {
    // First, get the image path to delete the file
    $stmt = $db->prepare("SELECT image_path FROM doctors WHERE doctor_id = :doctor_id");
    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor && $doctor['image_path']) {
        $image_path = "../uploads/doctors/" . $doctor['image_path'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete the doctor record
    $stmt = $db->prepare("DELETE FROM doctors WHERE doctor_id = :doctor_id");
    $stmt->bindParam(':doctor_id', $doctor_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Doctor deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting doctor']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>