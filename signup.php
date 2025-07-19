<?php
header('Content-Type: application/json');
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../scripts/connect.php'; // Adjust path as needed

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if database connection exists
if (!isset($db)) {
    $response['message'] = 'Database connection error';
    echo json_encode($response);
    exit;
}

try {
    // Validate required fields
    $required = [
        'first_name', 'last_name', 'email', 'phone', 'dob', 
        'gender', 'address', 'state', 'district', 'pincode',
        'password', 'confirm_password', 'user_role'
    ];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $response['errors'][$field] = 'This field is required';
        }
    }
    
    if (!empty($response['errors'])) {
        throw new Exception('Please fill all required fields');
    }

    // Extract and sanitize data
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $state = trim($_POST['state']);
    $district = trim($_POST['district']);
    $pincode = trim($_POST['pincode']);
    $landmark = trim($_POST['landmark'] ?? '');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = (int)$_POST['user_role'];

    // Validate formats
    if (!preg_match("/^[A-Za-z\s]+$/", $first_name)) {
        $response['errors']['first_name'] = 'Only letters and spaces allowed';
    }
    
    if (!empty($middle_name) && !preg_match("/^[A-Za-z\s]+$/", $middle_name)) {
        $response['errors']['middle_name'] = 'Only letters and spaces allowed';
    }
    
    if (!preg_match("/^[A-Za-z\s]+$/", $last_name)) {
        $response['errors']['last_name'] = 'Only letters and spaces allowed';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = 'Invalid email format';
    }
    
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $response['errors']['phone'] = 'Phone must be 10 digits';
    }
    
    if (!preg_match("/^[0-9]{6}$/", $pincode)) {
        $response['errors']['pincode'] = 'Pincode must be 6 digits';
    }
    
    if (strlen($password) < 8) {
        $response['errors']['password'] = 'Password must be at least 8 characters';
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $response['errors']['password'] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match("/[a-z]/", $password)) {
        $response['errors']['password'] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match("/[0-9]/", $password)) {
        $response['errors']['password'] = 'Password must contain at least one number';
    }
    
    if ($password !== $confirm_password) {
        $response['errors']['confirm_password'] = 'Passwords do not match';
    }
    
    // Age validation
    $today = new DateTime();
    $birthdate = new DateTime($dob);
    if ($today->diff($birthdate)->y < 18) {
        $response['errors']['dob'] = 'You must be at least 18 years old';
    }
    
    // Validate user role
    if (!in_array($role, [2, 4])) { // 2=Shop Owner, 4=Delivery Person
        $response['errors']['user_role'] = 'Invalid user role selected';
    }
    
    if (!empty($response['errors'])) {
        throw new Exception('Validation errors occurred');
    }

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already exists');
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user (without created_at)
    $stmt = $db->prepare("INSERT INTO users 
        (first_name, middle_name, last_name, email, phone, dob, gender, 
         address, state, district, pincode, landmark, password, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $success = $stmt->execute([
        $first_name, $middle_name, $last_name, $email, $phone, $dob, $gender,
        $address, $state, $district, $pincode, $landmark, $password_hash, $role
    ]);

    if (!$success) {
        throw new Exception('Failed to create user account');
    }

    $response['success'] = true;
    $response['message'] = 'Registration successful!';

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    if (empty($response['message'])) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);