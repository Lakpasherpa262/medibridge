<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

// Optional: Verify the doctor still exists in database
require '../scripts/connect.php';
$stmt = $db->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$_SESSION['id']]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>