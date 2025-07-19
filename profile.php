<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include 'scripts/connect.php';

// Get complete user data
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found!");
    }
    
    // Get all users for admin view
    $allUsers = [];
    if ($_SESSION['role'] === 'admin') {
        $stmt = $db->query("SELECT id, first_name, last_name, email, phone, role FROM users");
        $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}

// Handle pincode check AJAX request
if (isset($_GET['check_pincode'])) {
    $pincode = $_GET['pincode'];
    $stmt = $db->prepare("SELECT 1 FROM pincode WHERE number = ?");
    $stmt->execute([$pincode]);
    $valid = $stmt->rowCount() > 0;
    header('Content-Type: application/json');
    echo json_encode(['valid' => $valid]);
    exit();
}

// Handle individual field updates via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field'])) {
    header('Content-Type: application/json');
    
    try {
        $field = $_POST['field'];
        $value = $_POST['value'] ?? '';
        $errors = [];
        
        // Field-specific validation
        switch($field) {
            case 'first_name':
            case 'middle_name':
            case 'last_name':
            case 'state':
            case 'district':
                if (!preg_match("/^[A-Za-z\s]+$/", $value)) {
                    $errors[$field] = "Only letters and spaces allowed";
                }
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Invalid email format";
                }
                break;
                
            case 'phone':
                if (!preg_match("/^[0-9]{10}$/", $value)) {
                    $errors[$field] = "Phone must be 10 digits";
                }
                break;
                
            case 'dob':
                $today = new DateTime();
                $birthdate = new DateTime($value);
                if ($today->diff($birthdate)->y < 18) {
                    $errors[$field] = "You must be at least 18 years old";
                }
                break;
                
            case 'pincode':
                if (!preg_match("/^[0-9]{6}$/", $value)) {
                    $errors[$field] = "Pincode must be 6 digits";
                } else {
                    $stmt = $db->prepare("SELECT 1 FROM pincode WHERE number = ?");
                    $stmt->execute([$value]);
                    if ($stmt->rowCount() === 0) {
                        $errors[$field] = "Invalid pincode - not serviceable in our area";
                    }
                }
                break;
                
            case 'new_password':
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    $errors['confirm_password'] = "Passwords don't match!";
                } elseif (strlen($_POST['new_password']) < 8) {
                    $errors['new_password'] = "Password must be at least 8 characters";
                } elseif (!preg_match("/[A-Z]/", $_POST['new_password'])) {
                    $errors['new_password'] = "Password must contain at least one uppercase letter";
                } elseif (!preg_match("/[a-z]/", $_POST['new_password'])) {
                    $errors['new_password'] = "Password must contain at least one lowercase letter";
                } elseif (!preg_match("/[0-9]/", $_POST['new_password'])) {
                    $errors['new_password'] = "Password must contain at least one number";
                }
                break;
        }
        
        if (empty($errors)) {
            if ($field === 'new_password') {
                $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['id']]);
            } else {
                $stmt = $db->prepare("UPDATE users SET $field = ? WHERE id = ?");
                $stmt->execute([$value, $_SESSION['id']]);
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'errors' => $errors]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => "Database error: " . $e->getMessage()]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .profile-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
        }
        .info-display {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            cursor: pointer;
            min-height: 38px;
        }
        .info-display:hover {
            background-color: #f0f0f0;
        }
        .edit-field {
            display: none;
            margin-bottom: 15px;
        }
        .is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            color: #dc3545;
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
        }
        .pincode-feedback {
            margin-top: 0.25rem;
            font-size: 0.875em;
        }
        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            animation: fadeInOut 3s ease-in-out;
            opacity: 0;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        .users-table {
            margin-top: 30px;
        }
        .users-table th {
            background-color: #f1f1f1;
        }
        .save-btn {
            display: none;
            margin-top: 10px;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .password-input-container {
            position: relative;
        }
        .password-input-container input {
            padding-right: 35px;
        }
        .form-control.no-numbers {
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <div id="header">
        <?php include 'templates/header.php'; ?>
    </div>

    <div class="container py-4 profile-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success success-message"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <h1 class="mb-4">My Profile</h1>
        
        <!-- Personal Information -->
        <div class="profile-section">
            <h2 class="mb-4">Personal Information</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>First Name</label>
                    <div class="info-display" onclick="toggleEdit(this, 'first_name')">
                        <?= htmlspecialchars($user['first_name'] ?? '') ?>
                    </div>
                    <input type="text" name="first_name" class="form-control edit-field no-numbers" 
                           value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" placeholder="Enter first name">
                    <div class="invalid-feedback">Only letters and spaces allowed</div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('first_name', this)">Save</button>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Middle Name</label>
                    <div class="info-display" onclick="toggleEdit(this, 'middle_name')">
                        <?= htmlspecialchars($user['middle_name'] ?? '') ?>
                    </div>
                    <input type="text" name="middle_name" class="form-control edit-field no-numbers" 
                           value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>" placeholder="Enter middle name">
                    <div class="invalid-feedback">Only letters and spaces allowed</div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('middle_name', this)">Save</button>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Last Name</label>
                    <div class="info-display" onclick="toggleEdit(this, 'last_name')">
                        <?= htmlspecialchars($user['last_name'] ?? '') ?>
                    </div>
                    <input type="text" name="last_name" class="form-control edit-field no-numbers" 
                           value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" placeholder="Enter last name">
                    <div class="invalid-feedback">Only letters and spaces allowed</div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('last_name', this)">Save</button>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <div class="info-display" onclick="toggleEdit(this, 'email')">
                        <?= htmlspecialchars($user['email'] ?? '') ?>
                    </div>
                    <input type="email" name="email" class="form-control edit-field" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="Enter email">
                    <div class="invalid-feedback">Please enter a valid email address</div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('email', this)">Save</button>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Phone</label>
                    <div class="info-display" onclick="toggleEdit(this, 'phone')">
                        <?= htmlspecialchars($user['phone'] ?? '') ?>
                    </div>
                    <input type="text" name="phone" class="form-control edit-field" 
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Enter phone number" maxlength="10">
                    <div class="invalid-feedback">Please enter a valid 10-digit phone number</div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('phone', this)">Save</button>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Date of Birth</label>
                    <div class="info-display" onclick="toggleEdit(this, 'dob')">
                        <?= htmlspecialchars($user['dob'] ?? '') ?>
                    </div>
                    <input type="date" name="dob" class="form-control edit-field" 
                           value="<?= htmlspecialchars($user['dob'] ?? '') ?>"
                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
                    <div class="invalid-feedback">You must be at least 18 years old</div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('dob', this)">Save</button>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Gender</label>
                    <div class="info-display" onclick="toggleEdit(this, 'gender')">
                        <?= !empty($user['gender']) ? htmlspecialchars(ucfirst($user['gender'])) : '' ?>
                    </div>
                    <select name="gender" class="form-control edit-field">
                        <option value="">Select Gender</option>
                        <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('gender', this)">Save</button>
                </div>
            </div>
        </div>
        
        <!-- Address Information -->
        <div class="profile-section">
            <h2 class="mb-4">Address Information</h2>
            <div class="mb-3">
                <label>Address</label>
                <div class="info-display" onclick="toggleEdit(this, 'address')">
                    <?= htmlspecialchars($user['address'] ?? '') ?>
                </div>
                <textarea name="address" class="form-control edit-field" rows="3" placeholder="Enter full address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                <button class="btn btn-primary btn-sm save-btn" onclick="saveField('address', this)">Save</button>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>State</label>
                    <div class="info-display" onclick="toggleEdit(this, 'state')">
                        <?= htmlspecialchars($user['state'] ?? '') ?>
                    </div>
                    <input type="text" name="state" class="form-control edit-field no-numbers" 
                           value="<?= htmlspecialchars($user['state'] ?? '') ?>" placeholder="Enter state">
                    <div class="invalid-feedback">Only letters and spaces allowed</div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('state', this)">Save</button>
                </div>
                <div class="col-md-4 mb-3">
                    <label>District</label>
                    <div class="info-display" onclick="toggleEdit(this, 'district')">
                        <?= htmlspecialchars($user['district'] ?? '') ?>
                    </div>
                    <input type="text" name="district" class="form-control edit-field no-numbers" 
                           value="<?= htmlspecialchars($user['district'] ?? '') ?>" placeholder="Enter district">
                    <div class="invalid-feedback">Only letters and spaces allowed</div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('district', this)">Save</button>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Pincode</label>
                    <div class="info-display" onclick="toggleEdit(this, 'pincode')">
                        <?= htmlspecialchars($user['pincode'] ?? '') ?>
                    </div>
                    <input type="text" name="pincode" class="form-control edit-field" 
                           value="<?= htmlspecialchars($user['pincode'] ?? '') ?>" placeholder="Enter pincode" maxlength="6">
                    <div class="invalid-feedback">Please enter a valid 6-digit pincode</div>
                    <div id="pincodeFeedback" class="pincode-feedback"></div>
                    <button class="btn btn-primary btn-sm save-btn" onclick="saveField('pincode', this)">Save</button>
                </div>
            </div>
            
            <div class="mb-3">
                <label>Landmark</label>
                <div class="info-display" onclick="toggleEdit(this, 'landmark')">
                    <?= htmlspecialchars($user['landmark'] ?? '') ?>
                </div>
                <input type="text" name="landmark" class="form-control edit-field" 
                       value="<?= htmlspecialchars($user['landmark'] ?? '') ?>" placeholder="Enter landmark">
                <button class="btn btn-primary btn-sm save-btn" onclick="saveField('landmark', this)">Save</button>
            </div>
        </div>
        
        <!-- Password Change -->
        <div class="profile-section">
            <h2 class="mb-4">Change Password</h2>
            <div class="row">
                <div class="col-md-6 mb-3 password-input-container">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" 
                           placeholder="Enter new password" minlength="8">
                    <i class="fas fa-eye-slash password-toggle" onclick="togglePasswordVisibility(this)"></i>
                    <div class="invalid-feedback">
                        Password must be at least 8 characters with at least one uppercase, 
                        one lowercase letter and one number
                    </div>
                </div>
                <div class="col-md-6 mb-3 password-input-container">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" 
                           placeholder="Confirm new password">
                    <i class="fas fa-eye-slash password-toggle" onclick="togglePasswordVisibility(this)"></i>
                    <div class="invalid-feedback">Passwords must match</div>
                </div>
            </div>
            <button class="btn btn-primary" onclick="savePassword()">Change Password</button>
        </div>

        <!-- Users Table (for admin only) -->
        <?php if ($_SESSION['role'] === 'admin' && !empty($allUsers)): ?>
            <div class="profile-section users-table">
                <h2 class="mb-4">All Users</h2>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['id']) ?></td>
                                    <td><?= htmlspecialchars($u['first_name'] . ' ' . htmlspecialchars($u['last_name'])) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['phone']) ?></td>
                                    <td><?= htmlspecialchars($u['role']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="footer">
        <?php include 'templates/footer.php'; ?>
    </div>

    <script>
        // Improved toggleEdit function with better UX
        function toggleEdit(displayElement, fieldName) {
            // Hide the display element
            displayElement.style.display = 'none';
            
            // Find the corresponding input field
            const inputField = displayElement.nextElementSibling;
            
            // Show the input field and save button
            inputField.style.display = 'block';
            
            // Find the save button (it might not be the immediate next sibling)
            let nextElement = inputField.nextElementSibling;
            while (nextElement) {
                if (nextElement.classList.contains('save-btn')) {
                    nextElement.style.display = 'block';
                    break;
                }
                nextElement = nextElement.nextElementSibling;
            }
            
            inputField.focus();
            
            // Set up event listeners for this field
            setupFieldValidation(inputField, fieldName);
            
            // When input loses focus, show display again if not submitting
            inputField.addEventListener('blur', function() {
                if (this.classList.contains('is-invalid')) {
                    return; // Don't hide if field is invalid
                }
            });
            
            // Also handle Enter key to save and blur
            inputField.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    let saveBtn = this.nextElementSibling;
                    while (saveBtn && !saveBtn.classList.contains('save-btn')) {
                        saveBtn = saveBtn.nextElementSibling;
                    }
                    if (saveBtn) saveBtn.click();
                }
            });
        }
        
        function updateDisplayValue(displayElement, inputField) {
            if (inputField.type === 'date') {
                // Format date for display
                const date = new Date(inputField.value);
                displayElement.innerHTML = date.toLocaleDateString();
            } else if (inputField.tagName === 'SELECT') {
                // Get selected option text
                const selectedOption = inputField.options[inputField.selectedIndex];
                displayElement.innerHTML = selectedOption.text;
            } else {
                displayElement.innerHTML = inputField.value;
            }
            inputField.style.display = 'none';
            
            // Hide the save button (it might not be the immediate next sibling)
            let nextElement = inputField.nextElementSibling;
            while (nextElement) {
                if (nextElement.classList.contains('save-btn')) {
                    nextElement.style.display = 'none';
                    break;
                }
                nextElement = nextElement.nextElementSibling;
            }
            
            displayElement.style.display = 'block';
        }
        
        function setupFieldValidation(inputField, fieldName) {
            switch(fieldName) {
                case 'first_name':
                case 'middle_name':
                case 'last_name':
                case 'state':
                case 'district':
                    inputField.addEventListener('input', function() {
                        // Remove any numbers that might have been pasted
                        this.value = this.value.replace(/[0-9]/g, '');
                        
                        const isValid = /^[A-Za-z\s]*$/.test(this.value);
                        this.classList.toggle('is-invalid', !isValid);
                        
                        // Find the error message (it might not be the immediate next sibling)
                        let nextElement = this.nextElementSibling;
                        while (nextElement) {
                            if (nextElement.classList.contains('invalid-feedback')) {
                                nextElement.style.display = isValid ? 'none' : 'block';
                                break;
                            }
                            nextElement = nextElement.nextElementSibling;
                        }
                    });
                    break;
                    
                case 'email':
                    inputField.addEventListener('input', function() {
                        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
                        this.classList.toggle('is-invalid', !isValid && this.value.trim() !== '');
                        
                        // Find the error message
                        let nextElement = this.nextElementSibling;
                        while (nextElement) {
                            if (nextElement.classList.contains('invalid-feedback')) {
                                nextElement.style.display = (isValid || this.value.trim() === '') ? 'none' : 'block';
                                break;
                            }
                            nextElement = nextElement.nextElementSibling;
                        }
                    });
                    break;
                    
                case 'phone':
                    inputField.addEventListener('input', function() {
                        // Only allow numbers
                        this.value = this.value.replace(/\D/g, '');
                        
                        const isValid = /^\d{0,10}$/.test(this.value);
                        this.classList.toggle('is-invalid', !isValid);
                        
                        // Find the error message
                        let nextElement = this.nextElementSibling;
                        while (nextElement) {
                            if (nextElement.classList.contains('invalid-feedback')) {
                                nextElement.style.display = isValid ? 'none' : 'block';
                                break;
                            }
                            nextElement = nextElement.nextElementSibling;
                        }
                    });
                    break;
                    
                case 'pincode':
                    inputField.addEventListener('input', function() {
                        // Only allow numbers
                        this.value = this.value.replace(/\D/g, '');
                        
                        const isValid = /^\d{0,6}$/.test(this.value);
                        this.classList.toggle('is-invalid', !isValid);
                        
                        // Find the error message
                        let nextElement = this.nextElementSibling;
                        while (nextElement) {
                            if (nextElement.classList.contains('invalid-feedback')) {
                                nextElement.style.display = isValid ? 'none' : 'block';
                                break;
                            }
                            nextElement = nextElement.nextElementSibling;
                        }
                        
                        // Check pincode availability when we have 6 digits
                        if (this.value.length === 6) {
                            checkPincode(this);
                        } else {
                            document.getElementById('pincodeFeedback').textContent = '';
                        }
                    });
                    break;
                    
                case 'dob':
                    inputField.addEventListener('change', function() {
                        const today = new Date();
                        const birthDate = new Date(this.value);
                        const age = today.getFullYear() - birthDate.getFullYear();
                        const m = today.getMonth() - birthDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        
                        const isValid = age >= 18;
                        this.classList.toggle('is-invalid', !isValid);
                        
                        // Find the error message
                        let nextElement = this.nextElementSibling;
                        while (nextElement) {
                            if (nextElement.classList.contains('invalid-feedback')) {
                                nextElement.style.display = isValid ? 'none' : 'block';
                                break;
                            }
                            nextElement = nextElement.nextElementSibling;
                        }
                    });
                    break;
            }
        }
        
        // Pincode availability check with AJAX
        function checkPincode(input) {
            const pincode = input.value;
            const feedback = document.getElementById('pincodeFeedback');
            
            if (pincode.length === 6) {
                feedback.textContent = 'Checking pincode...';
                feedback.style.color = 'blue';
                
                // Make AJAX call to check pincode
                fetch(profile.php?check_pincode=1&pincode=${encodeURIComponent(pincode)})
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            feedback.textContent = '✓ Pincode is serviceable';
                            feedback.style.color = 'green';
                        } else {
                            feedback.textContent = '✗ Pincode not serviceable in our area';
                            feedback.style.color = 'red';
                        }
                    })
                    .catch(error => {
                        feedback.textContent = 'Error checking pincode';
                        feedback.style.color = 'red';
                    });
            }
        }
        
        // Password validation
        document.querySelector('input[name="new_password"]').addEventListener('input', function() {
            const password = this.value;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const isValid = password.length >= 8 && hasUpper && hasLower && hasNumber;
            
            this.classList.toggle('is-invalid', !isValid);
            
            // Find the error message
            let nextElement = this.nextElementSibling;
            while (nextElement) {
                if (nextElement.classList.contains('invalid-feedback')) {
                    nextElement.style.display = isValid ? 'none' : 'block';
                    break;
                }
                nextElement = nextElement.nextElementSibling;
            }
            
            // Also validate confirm password if it has value
            const confirmPassword = document.querySelector('input[name="confirm_password"]');
            if (confirmPassword.value) {
                validatePasswordMatch();
            }
        });
        
        function validatePasswordMatch() {
            const password = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]');
            const isValid = password === confirmPassword.value;
            
            confirmPassword.classList.toggle('is-invalid', !isValid);
            
            // Find the error message
            let nextElement = confirmPassword.nextElementSibling;
            while (nextElement) {
                if (nextElement.classList.contains('invalid-feedback')) {
                    nextElement.style.display = isValid ? 'none' : 'block';
                    break;
                }
                nextElement = nextElement.nextElementSibling;
            }
        }
        
        document.querySelector('input[name="confirm_password"]').addEventListener('input', validatePasswordMatch);
        
        // Toggle password visibility
        function togglePasswordVisibility(icon) {
            const input = icon.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
        
        // Save individual field
        function saveField(fieldName, saveBtn) {
            // Find the input field (it might not be the immediate previous sibling)
            let inputField = saveBtn.previousElementSibling;
            while (inputField && !inputField.classList.contains('edit-field')) {
                inputField = inputField.previousElementSibling;
            }
            
            if (!inputField) return;
            
            // Find the display element (it might not be the immediate previous sibling)
            let displayElement = inputField.previousElementSibling;
            while (displayElement && !displayElement.classList.contains('info-display')) {
                displayElement = displayElement.previousElementSibling;
            }
            
            if (inputField.classList.contains('is-invalid')) {
                alert('Please fix the errors before saving.');
                return;
            }
            
            const formData = new FormData();
            formData.append('field', fieldName);
            formData.append('value', inputField.value);
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Field updated successfully!');
                    updateDisplayValue(displayElement, inputField);
                } else {
                    if (data.errors) {
                        let errorMsg = '';
                        for (const error in data.errors) {
                            errorMsg += data.errors[error] + '\n';
                        }
                        alert(errorMsg);
                    } else {
                        alert(data.error || 'Error updating field');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating field');
            });
        }
        
        // Save password
        function savePassword() {
            const newPassword = document.querySelector('input[name="new_password"]');
            const confirmPassword = document.querySelector('input[name="confirm_password"]');
            
            if (newPassword.classList.contains('is-invalid') || confirmPassword.classList.contains('is-invalid')) {
                alert('Please fix the errors before saving.');
                return;
            }
            
            const formData = new FormData();
            formData.append('field', 'new_password');
            formData.append('new_password', newPassword.value);
            formData.append('confirm_password', confirmPassword.value);
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Password updated successfully!');
                    newPassword.value = '';
                    confirmPassword.value = '';
                } else {
                    if (data.errors) {
                        let errorMsg = '';
                        for (const error in data.errors) {
                            errorMsg += data.errors[error] + '\n';
                        }
                        alert(errorMsg);
                    } else {
                        alert(data.error || 'Error updating password');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating password');
            });
        }
        
        function showSuccessMessage(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'alert alert-success success-message';
            successDiv.textContent = message;
            document.querySelector('.profile-container').prepend(successDiv);
            
            // Trigger animation
            setTimeout(() => {
                successDiv.style.animation = 'fadeInOut 3s ease-in-out';
                setTimeout(() => successDiv.remove(), 3000);
            }, 10);
        }
        
        // Initialize all fields with their validation
        document.querySelectorAll('.edit-field').forEach(field => {
            const fieldName = field.name;
            setupFieldValidation(field, fieldName);
        });
    </script>
</body>
</html>