<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include database connection
require __DIR__ . '/../scripts/connect.php';

// Set response header to plain text
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $first_name = trim($_POST['fname']);
    $middle_name = trim($_POST['mname'] ?? ''); // Optional field
    $last_name = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $state = trim($_POST['state']);
    $district = trim($_POST['district']);
    $pincode = trim($_POST['pincode']);
    $landmark = trim($_POST['landmark'] ?? ''); // Optional field
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $stay_logged_in = isset($_POST['stay_logged_in']) ? 1 : 0; // Ensure this matches the form field name

    // Validate inputs
    $errors = [];

   // ... [previous code remains the same until the validation section]

    // Required fields
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($dob)) $errors[] = "Date of birth is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($state)) $errors[] = "State is required.";
    if (empty($district)) $errors[] = "District is required.";
    if (empty($pincode)) $errors[] = "Pincode is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($confirm_password)) $errors[] = "Confirm password is required.";

    // Age validation (18+ years)
    if (!empty($dob)) {
        $today = new DateTime();
        $birthdate = new DateTime($dob);
        $age = $today->diff($birthdate)->y;
        
        if ($age < 18) {
            $errors[] = "You must be at least 18 years old to register.";
        }
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }


    // Phone number validation (10 digits)
    if (strlen($phone) !== 10 || !ctype_digit($phone)) {
        $errors[] = "Phone number must be 10 digits.";
    }

    // Pincode validation (6 digits)
    if (strlen($pincode) !== 6 || !ctype_digit($pincode)) {
        $errors[] = "Pincode must be 6 digits.";
    }

    // Password validation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already exists.";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $errors[] = "Database error. Please try again.";
        }
    }

    // If there are errors, return them as a plain text response
    if (!empty($errors)) {
        echo implode("<br>", $errors);
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    try {
        $stmt = $db->prepare("
            INSERT INTO users (
                first_name, middle_name, last_name, email, phone, dob, gender, address, state, district, pincode, landmark, password, stay_logged_in, role
            ) VALUES (
                :first_name, :middle_name, :last_name, :email, :phone, :dob, :gender, :address, :state, :district, :pincode, :landmark, :password, :stay_logged_in, :role
            )
        ");

        $stmt->bindParam(":first_name", $first_name);
        $stmt->bindParam(":middle_name", $middle_name);
        $stmt->bindParam(":last_name", $last_name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":dob", $dob);
        $stmt->bindParam(":gender", $gender);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":state", $state);
        $stmt->bindParam(":district", $district);
        $stmt->bindParam(":pincode", $pincode);
        $stmt->bindParam(":landmark", $landmark);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":stay_logged_in", $stay_logged_in, PDO::PARAM_INT);
        $stmt->bindValue(":role", 5, PDO::PARAM_INT); // Default role is 5

        $stmt->execute();
        $user_id = $db->lastInsertId();

        // Set session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['id'] = $user_id;
        $_SESSION['username'] = $first_name;
        $_SESSION['role'] = 5; // Default role for normal users

        // Return success response
        echo "success";
        exit();
    } catch (PDOException $e) {
        // Log the error for debugging
        error_log("Signup error: " . $e->getMessage());

        // Return a generic error message
        echo "Database error. Please try again.";
        exit();
    }
} else {
    // Invalid request method
    echo "Invalid request method.";
    exit();
}
?>