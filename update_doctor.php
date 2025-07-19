<?php
include '../scripts/connect.php';
session_start();

if (!isset($_SESSION['shop_id'])) {
    die(json_encode(['success' => false, 'error' => 'Shop not identified']));
}

$shop_id = $_SESSION['shop_id'];
$doctor_id = $_POST['doctor_id'];

// Validate the doctor belongs to this shop
try {
    $stmt = $db->prepare("SELECT id FROM doctors WHERE id = ? AND shop_id = ?");
    $stmt->execute([$doctor_id, $shop_id]);
    if (!$stmt->fetch()) {
        die(json_encode(['success' => false, 'error' => 'Doctor not found or not authorized']));
    }
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Database error']));
}

// Handle file upload if exists
$image_path = null;
if (!empty($_FILES['image']['name'])) {
    $allowed_types = ['image/jpeg', 'image/png'];
    $file_type = $_FILES['image']['type'];
    
    if (in_array($file_type, $allowed_types)) {
        $upload_dir = '../uploads/doctors/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $image_path = $file_path;
            
            // Delete old image if it exists and isn't the default
            $stmt = $db->prepare("SELECT image_path FROM doctors WHERE id = ?");
            $stmt->execute([$doctor_id]);
            $old_image = $stmt->fetchColumn();
            
            if ($old_image && $old_image !== '../images/default-doctor.png' && file_exists($old_image)) {
                unlink($old_image);
            }
        }
    }
}

// Prepare update query
$fields = [
    'name' => $_POST['name'],
    'specialization' => $_POST['specialization'],
    'phone' => $_POST['phone'],
    'email' => $_POST['email'],
    'monday_start' => $_POST['monday_start'],
    'monday_end' => $_POST['monday_end'],
    'tuesday_start' => $_POST['tuesday_start'],
    'tuesday_end' => $_POST['tuesday_end'],
    'wednesday_start' => $_POST['wednesday_start'],
    'wednesday_end' => $_POST['wednesday_end'],
    'thursday_start' => $_POST['thursday_start'],
    'thursday_end' => $_POST['thursday_end'],
    'friday_start' => $_POST['friday_start'],
    'friday_end' => $_POST['friday_end'],
    'saturday_start' => $_POST['saturday_start'],
    'saturday_end' => $_POST['saturday_end'],
    'sunday_start' => $_POST['sunday_start'],
    'sunday_end' => $_POST['sunday_end']
];

if ($image_path) {
    $fields['image_path'] = $image_path;
}

$set_clause = implode(', ', array_map(function($field) {
    return "`$field` = :$field";
}, array_keys($fields)));

try {
    $stmt = $db->prepare("UPDATE doctors SET $set_clause WHERE id = :doctor_id");
    $stmt->bindValue(':doctor_id', $doctor_id);
    
    foreach ($fields as $key => $value) {
        $stmt->bindValue(":$key", $value ?: null);
    }
    
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'image_path' => $image_path
    ]);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Database error']));
}
?>