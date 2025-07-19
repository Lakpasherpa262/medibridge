<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['id']]);
    $count = $stmt->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'count' => $count
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}