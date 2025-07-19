<?php
header('Content-Type: application/json');
include '../scripts/connect.php';
session_start();

if (!isset($_SESSION['shop_id'])) {
    echo json_encode(['success' => false, 'message' => 'Shop not identified']);
    exit();
}

$prescriptionId = isset($_POST['id']) ? intval($_POST['id']) : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;

if (!$prescriptionId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$allowedStatuses = ['Pending', 'Approved', 'Rejected'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    $stmt = $db->prepare("UPDATE prescriptions SET status = :status WHERE id = :id AND shop_id = :shop_id");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $prescriptionId, PDO::PARAM_INT);
    $stmt->bindParam(':shop_id', $_SESSION['shop_id'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No prescription found or no changes made']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>