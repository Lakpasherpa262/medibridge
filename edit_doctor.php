<?php
// Ensure no output before headers
ob_start();

include '../scripts/connect.php';

// Set proper headers FIRST
header('Content-Type: application/json; charset=utf-8');

// Check if doctor_id is provided
if (!isset($_POST['doctor_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Doctor ID is required']);
    exit;
}

$doctor_id = $_POST['doctor_id'];

try {
    $response = ['status' => 'success', 'message' => ''];
    
    // Handle image upload if present
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/doctors/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "doctor_" . $doctor_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false) {
            throw new Exception('File is not an image');
        }
        
        // Try to upload file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Update database with new image path (relative path)
            $relative_path = "../uploads/doctors/" . $new_filename;
            $stmt = $db->prepare("UPDATE doctors SET image_path = :image_path WHERE doctor_id = :doctor_id");
            $stmt->bindParam(':image_path', $relative_path);
            $stmt->bindParam(':doctor_id', $doctor_id);
            $stmt->execute();
            
            $response['message'] .= 'Image updated successfully. ';
        } else {
            throw new Exception('Error uploading image');
        }
    }
    
    // Handle other details update
    $fields = [
        'name', 'email', 'phone', 'degree', 'license_no',
        'monday_start', 'monday_end',
        'tuesday_start', 'tuesday_end',
        'wednesday_start', 'wednesday_end',
        'thursday_start', 'thursday_end',
        'friday_start', 'friday_end',
        'saturday_start', 'saturday_end'
    ];
    
    $updateParts = [];
    $params = [':doctor_id' => $doctor_id];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updateParts[] = "$field = :$field";
            $params[":$field"] = !empty($_POST[$field]) ? $_POST[$field] : null;
        }
    }
    
    if (!empty($updateParts)) {
        $sql = "UPDATE doctors SET " . implode(', ', $updateParts) . " WHERE doctor_id = :doctor_id";
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Database update failed');
        }
        
        $response['message'] .= 'Details updated successfully.';
    }
    
    // Clean any output buffers before sending JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    echo json_encode($response);
    exit;
    
} catch (Exception $e) {
    // Clean any output buffers before sending JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
}
?>