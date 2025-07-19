<?php
session_start();
include '../scripts/connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $name = trim($_POST['doctor_name'] ?? '');
        $email = trim($_POST['doctor_email'] ?? '');
        $phone = trim($_POST['doctor_phone'] ?? '');
        $specialization_id = trim($_POST['doctor_specialization'] ?? '');
        $degree = trim($_POST['doctor_degree'] ?? '');
        $license = trim($_POST['doctor_license'] ?? '');
        $fee = trim($_POST['doctor_fee'] ?? '');
        $password = $_POST['doctor_password'] ?? '';
        $confirm_password = $_POST['doctor_confirm_password'] ?? '';

        // Validate required fields
        $errors = [];
        if (empty($name)) $errors['doctor-name'] = 'Full name is required';
        if (empty($email)) $errors['doctor-email'] = 'Email is required';
        if (empty($phone)) $errors['doctor-phone'] = 'Phone number is required';
        if (empty($specialization_id)) $errors['doctor-specialization'] = 'Specialization is required';
        if (empty($degree)) $errors['doctor-degree'] = 'Degree is required';
        if (empty($license)) $errors['doctor-license'] = 'License number is required';
        if (empty($fee)) $errors['doctor-fee'] = 'Consultation fee is required';
        if (empty($password)) $errors['doctor-password'] = 'Password is required';

        // Validate image upload
        if (!isset($_FILES['doctor_image']) || $_FILES['doctor_image']['error'] !== UPLOAD_ERR_OK) {
            $errors['doctor-image'] = 'Doctor image is required';
        }

        // Validate name
        if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
            $errors['doctor-name'] = 'Name should contain only letters and spaces';
        }

        // Validate fee
        if (!is_numeric($fee)) {
            $errors['doctor-fee'] = 'Fee must be a numeric value';
        } elseif ($fee < 0) {
            $errors['doctor-fee'] = 'Fee cannot be negative';
        }

        // Validate degree
        if (!preg_match('/^[a-zA-Z\s,]+$/', $degree)) {
            $errors['doctor-degree'] = 'Degree should contain only letters and commas';
        }

        // Validate license
        if (!preg_match('/^[a-zA-Z0-9]{10}$/', $license)) {
            $errors['doctor-license'] = 'License should be exactly 10 characters (letters and numbers only, no spaces)';
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['doctor-email'] = 'Please enter a valid email address';
        }

        // Validate phone
        if (!preg_match('/^\d{10}$/', $phone)) {
            $errors['doctor-phone'] = 'Phone number must be 10 digits';
        }

        // Validate password match
        if ($password !== $confirm_password) {
            $errors['doctor-confirm-password'] = 'Passwords do not match';
        }

        // Validate password strength
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors['doctor-password'] = 'Password must be 8+ chars with uppercase, lowercase, and number';
        }

        // Check duplicate email in doctors
        $stmt = $db->prepare("SELECT doctor_id FROM doctors WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors['doctor-email'] = 'Email already exists';
        }

        // Check duplicate email in users
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors['doctor-email'] = 'Email already exists in system';
        }

        // Check duplicate license number
        $stmt = $db->prepare("SELECT doctor_id FROM doctors WHERE license_no = ?");
        $stmt->execute([$license]);
        if ($stmt->rowCount() > 0) {
            $errors['doctor-license'] = 'License number already exists';
        }

        // Validate specialization exists
        $stmt = $db->prepare("SELECT specialization_name FROM specializations WHERE id = ?");
        $stmt->execute([$specialization_id]);
        if ($stmt->rowCount() === 0) {
            $errors['doctor-specialization'] = 'Invalid specialization selected';
        }

        // Process schedule
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $schedule = [];
        $hasSchedule = false;

        foreach ($days as $day) {
            $start = $_POST["{$day}_start"] ?? '';
            $end = $_POST["{$day}_end"] ?? '';

            if (!empty($start) && !empty($end)) {
                $schedule[$day] = ['start' => $start, 'end' => $end];
                $hasSchedule = true;
            }
        }

        if (!$hasSchedule) {
            $errors['schedule'] = 'Please set at least one available day';
        }

        // Return errors
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = 'Please correct the highlighted errors';
            echo json_encode($response);
            exit;
        }

        // Process image upload
        $imagePath = null;
        if (isset($_FILES['doctor_image']) && $_FILES['doctor_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/doctors/';
            $relativePath = 'uploads/doctors/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = pathinfo($_FILES['doctor_image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('doc_') . '.' . strtolower($fileExtension);
            $targetPath = $uploadDir . $fileName;

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['doctor_image']['type'];

            if (in_array($fileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES['doctor_image']['tmp_name'], $targetPath)) {
                    $imagePath = $relativePath . $fileName;
                } else {
                    throw new Exception('Failed to move uploaded file');
                }
            } else {
                throw new Exception('Invalid image file type. Only JPEG, PNG, and GIF are allowed');
            }
        } else {
            throw new Exception('Doctor image is required');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $shop_id = $_SESSION['shop_id'] ?? null;

        if (empty($shop_id)) {
            throw new Exception('Shop ID is missing');
        }

        $db->beginTransaction();

        try {
            $doctorData = [
                'shop_id' => $shop_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'specialization' => $specialization_id,
                'degree' => $degree,
                'license_no' => $license,
                'fee' => $fee,
                'password' => $hashedPassword,
                'image_path' => $imagePath,
                'monday_start' => $schedule['monday']['start'] ?? null,
                'monday_end' => $schedule['monday']['end'] ?? null,
                'tuesday_start' => $schedule['tuesday']['start'] ?? null,
                'tuesday_end' => $schedule['tuesday']['end'] ?? null,
                'wednesday_start' => $schedule['wednesday']['start'] ?? null,
                'wednesday_end' => $schedule['wednesday']['end'] ?? null,
                'thursday_start' => $schedule['thursday']['start'] ?? null,
                'thursday_end' => $schedule['thursday']['end'] ?? null,
                'friday_start' => $schedule['friday']['start'] ?? null,
                'friday_end' => $schedule['friday']['end'] ?? null,
                'saturday_start' => $schedule['saturday']['start'] ?? null,
                'saturday_end' => $schedule['saturday']['end'] ?? null,
                'sunday_start' => null,
                'sunday_end' => null
            ];

            $doctorColumns = implode(', ', array_keys($doctorData));
            $doctorPlaceholders = ':' . implode(', :', array_keys($doctorData));

            $sql = "INSERT INTO doctors ($doctorColumns) VALUES ($doctorPlaceholders)";
            $stmt = $db->prepare($sql);
            if (!$stmt->execute($doctorData)) {
                throw new Exception('Error saving doctor: ' . implode(' ', $stmt->errorInfo()));
            }

            $doctor_id = $db->lastInsertId();

            $userData = [
                'first_name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 3,
                'doctor_id' => $doctor_id
            ];

            $userColumns = implode(', ', array_keys($userData));
            $userPlaceholders = ':' . implode(', :', array_keys($userData));

            $sql = "INSERT INTO users ($userColumns) VALUES ($userPlaceholders)";
            $stmt = $db->prepare($sql);
            if (!$stmt->execute($userData)) {
                throw new Exception('Error creating user account: ' . implode(' ', $stmt->errorInfo()));
            }

            $db->commit();

            $response['success'] = true;
            $response['message'] = 'Doctor and user account created successfully';
            $response['doctor_id'] = $doctor_id;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
        error_log($e->getMessage());

        if (isset($imagePath) && file_exists('../' . $imagePath)) {
            unlink('../' . $imagePath);
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>