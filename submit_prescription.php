<?php
include '../scripts/connect.php';
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['id'];

// Create upload directory if it doesn't exist
$uploadDir = '../uploads/prescriptions/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Validate required fields
    $required = ['patientName', 'patientEmail', 'patientPhone', 'pharmacyId'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Get the selected pharmacy ID
    $pharmacyId = (int)$_POST['pharmacyId'];
    
    // Validate pharmacy exists
    $stmt = $db->prepare("SELECT id, shop_name FROM shopdetails WHERE id = ?");
    $stmt->execute([$pharmacyId]);
    $pharmacy = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pharmacy) {
        throw new Exception("Selected pharmacy does not exist");
    }

    // Process prescription file
    $prescriptionImage = null;
    if (!empty($_FILES['prescriptionFile']['name'])) {
        $file = $_FILES['prescriptionFile'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file['type'], $allowedTypes) || !in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
            throw new Exception('Invalid file type. Only images and PDFs are allowed.');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size exceeds 5MB limit.');
        }
        
        $fileName = 'prescription_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
        $uploadPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload prescription file.');
        }
        
        $prescriptionImage = 'uploads/prescriptions/' . $fileName;
    } else {
        throw new Exception('No prescription file uploaded.');
    }

    // Prepare data
    $patientName = htmlspecialchars(trim($_POST['patientName']));
    $patientEmail = filter_var(trim($_POST['patientEmail']), FILTER_VALIDATE_EMAIL);
    if (!$patientEmail) {
        throw new Exception('Invalid email address provided.');
    }
    $patientPhone = preg_replace('/[^0-9]/', '', $_POST['patientPhone']);
    $specialInstructions = !empty($_POST['specialInstructions']) ? htmlspecialchars(trim($_POST['specialInstructions'])) : null;

    // Begin transaction
    $db->beginTransaction();

    try {
        // Insert prescription
        $stmt = $db->prepare("
            INSERT INTO prescriptions (
                patient_name, 
                patient_email,
                patient_phone, 
                img, 
                special_instructions, 
                user_id, 
                shop_id,
                status,
                created_at
            ) VALUES (
                :patient_name, 
                :patient_email,
                :patient_phone, 
                :img, 
                :special_instructions, 
                :user_id, 
                :shop_id,
                'pending',
                NOW()
            )
        ");

        $stmt->execute([
            ':patient_name' => $patientName,
            ':patient_email' => $patientEmail,
            ':patient_phone' => $patientPhone,
            ':img' => $prescriptionImage,
            ':special_instructions' => $specialInstructions,
            ':user_id' => $userId,
            ':shop_id' => $pharmacyId
        ]);

        $prescriptionId = $db->lastInsertId();

        // Create notification data
        $notificationData = [
            'user_id' => $userId,
            'pharmacy_id' => $pharmacyId,
            'prescription_id' => $prescriptionId,
            'message' => "New prescription received from $patientName"
        ];

        // Commit transaction
        $db->commit();

        // Send success response with notification data
        $response = [
            'success' => true,
            'prescriptionId' => $prescriptionId,
            'message' => 'Prescription submitted successfully',
            'redirectUrl' => 'prescription.php?id=' . $prescriptionId,
            'notificationData' => $notificationData
        ];

    } catch (PDOException $e) {
        $db->rollBack();
        throw new Exception('Database error: ' . $e->getMessage());
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>