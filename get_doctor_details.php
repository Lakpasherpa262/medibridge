<?php
include '../scripts/connect.php';
session_start();

if (!isset($_POST['doctor_id'])) {
    die(json_encode(['success' => false, 'error' => 'Doctor ID not provided']));
}

if (!isset($_SESSION['shop_id'])) {
    die(json_encode(['success' => false, 'error' => 'Shop not identified']));
}

$doctor_id = $_POST['doctor_id'];
$shop_id = $_SESSION['shop_id'];

try {
    $stmt = $db->prepare("SELECT * FROM doctors WHERE id = ? AND shop_id = ?");
    $stmt->execute([$doctor_id, $shop_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        die(json_encode(['success' => false, 'error' => 'Doctor not found or not authorized']));
    }
    
    echo json_encode(['success' => true, 'data' => $doctor]);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Database error']));
}
?>