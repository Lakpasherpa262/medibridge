<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../scripts/connect.php');

// Verify database connection
if (!$db) {
    die(json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . print_r($db->errorInfo(), true)
    ]));
}

header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'errors' => []];

try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method.");
    }

    // Validate required fields
    $requiredFields = [
        'shop_name', 'shop_number', 'email', 'district', 'state', 'pincode',
        'trade_license', 'retail_drug_license', 'registration_number', 'address'
    ];
    
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
            $response['errors'][$field] = 'This field is required';
        }
    }
    
    if (!empty($missingFields)) {
        throw new Exception("Please fill in all required fields.");
    }

    // Sanitize inputs
    $shopName = trim($_POST['shop_name']);
    $shopNumber = trim($_POST['shop_number']);
    $email = trim($_POST['email']);
    $district = trim($_POST['district']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);
    $address = trim($_POST['address']);
    $tradeLicense = trim($_POST['trade_license']);
    $retailDrugLicense = trim($_POST['retail_drug_license']);
    $registrationNumber = trim($_POST['registration_number']);

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = 'Invalid email format';
        throw new Exception("Invalid email format.");
    }

    if (strlen($shopNumber) !== 10 || !ctype_digit($shopNumber)) {
        $response['errors']['shop_number'] = 'Shop number must be 10 digits';
        throw new Exception("Shop number must be 10 digits.");
    }

    if (strlen($pincode) !== 6 || !ctype_digit($pincode)) {
        $response['errors']['pincode'] = 'Pincode must be 6 digits';
        throw new Exception("Pincode must be 6 digits.");
    }

    // Verify shop owner ID
    if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
        throw new Exception("Invalid shop owner ID in session");
    }

    // Handle file uploads
    $baseDir = dirname(__DIR__);
    $uploadDir = $baseDir . '/includes/uploads/';
    
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory.");
        }
    }

    // Process shop image
    $shopImageName = '';
    if (!empty($_FILES['shop_image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['shop_image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, PNG, and GIF images are allowed for shop image.");
        }
        
        if ($_FILES['shop_image']['size'] > 2 * 1024 * 1024) {
            throw new Exception("Shop image must be less than 2MB.");
        }
        
        $shopImageName = 'shop_' . uniqid() . '_' . basename($_FILES['shop_image']['name']);
        $shopImagePath = $uploadDir . $shopImageName;
        
        if (!move_uploaded_file($_FILES['shop_image']['tmp_name'], $shopImagePath)) {
            throw new Exception("Failed to upload shop image.");
        }
    } else {
        throw new Exception("Shop image is required.");
    }

    // Process owner signature
    $ownerSignatureName = '';
    if (!empty($_FILES['owner_signature']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['owner_signature']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            if (file_exists($uploadDir . $shopImageName)) {
                unlink($uploadDir . $shopImageName);
            }
            throw new Exception("Only JPG, PNG, and GIF images are allowed for signature.");
        }
        
        if ($_FILES['owner_signature']['size'] > 1 * 1024 * 1024) {
            if (file_exists($uploadDir . $shopImageName)) {
                unlink($uploadDir . $shopImageName);
            }
            throw new Exception("Signature must be less than 1MB.");
        }
        
        $ownerSignatureName = 'signature_' . uniqid() . '_' . basename($_FILES['owner_signature']['name']);
        $ownerSignaturePath = $uploadDir . $ownerSignatureName;
        
        if (!move_uploaded_file($_FILES['owner_signature']['tmp_name'], $ownerSignaturePath)) {
            if (file_exists($uploadDir . $shopImageName)) {
                unlink($uploadDir . $shopImageName);
            }
            throw new Exception("Failed to upload owner signature.");
        }
    } else {
        if (file_exists($uploadDir . $shopImageName)) {
            unlink($uploadDir . $shopImageName);
        }
        throw new Exception("Owner signature is required.");
    }

    // Start transaction
    $db->beginTransaction();
    
    try {
        $stmt = $db->prepare("INSERT INTO shopdetails (
            shop_name, shop_image, shop_number, email, district, state, pincode, address,
            trade_license, retail_drug_license, registration_number, owner_signature, shopOwner_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $success = $stmt->execute([
            $shopName, 
             $shopImageName, 
            $shopNumber, 
            $email, 
            $district, 
            $state, 
            $pincode, 
            $address,
            $tradeLicense, 
            $retailDrugLicense, 
            $registrationNumber, 
            'includes/uploads/' . $ownerSignatureName, 
            $_SESSION['id']
        ]);

        if (!$success) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Database error: " . $errorInfo[2]);
        }

        $db->commit();
        $response['success'] = true;
        $response['message'] = "Shop registered successfully! ID: " . $db->lastInsertId();
        
    } catch (Exception $e) {
        $db->rollBack();
        // Clean up files
        if (file_exists($uploadDir . $shopImageName)) {
            unlink($uploadDir . $shopImageName);
        }
        if (file_exists($uploadDir . $ownerSignatureName)) {
            unlink($uploadDir . $ownerSignatureName);
        }
        throw $e;
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>