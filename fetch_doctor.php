<?php
// Include database connection
include '../scripts/connect.php';

// Set response header to JSON
header('Content-Type: application/json');

// Check if doctor ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Doctor ID is required'
    ]);
    exit;
}

// Get doctor ID from request
$doctor_id = $_GET['id'];

try {
    // Prepare SQL query to fetch doctor details
    $stmt = $db->prepare("SELECT d.*, s.specialization_name 
                         FROM doctors d
                         JOIN specializations s ON d.specialization = s.id
                         WHERE d.doctor_id = :doctor_id");
    
    // Bind parameter and execute query
    $stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Fetch doctor data
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor) {
        // Function to format time for display
        function formatTime($time) {
            if (empty($time)) return null;
            $parts = explode(':', $time);
            return sprintf("%02d:%02d", $parts[0], isset($parts[1]) ? $parts[1] : '00');
        }
        
        
// Handle image path - use exactly what's in database
$image_path = $doctor['image_path'];
if ($image_path && !file_exists($image_path)) {
    // If the path is relative, try prepending the images directory
    if (!file_exists('images/' . $image_path)) {
        $image_path = null; // Fallback if file doesn't exist
    } else {
        $image_path = 'images/' . $image_path;
    }
}        
        // Prepare response data
        $response = [
            'status' => 'success',
            'data' => [
                'id' => $doctor['doctor_id'],
                'name' => htmlspecialchars($doctor['name']),
                'email' => htmlspecialchars($doctor['email']),
                'phone' => htmlspecialchars($doctor['phone']),
                'specialization' => htmlspecialchars($doctor['specialization_name']),
                'degree' => htmlspecialchars($doctor['degree']),
                'license_no' => htmlspecialchars($doctor['license_no']),
                'image_path' => $image_path,
                'schedule' => [
                    'monday' => [
                        'start' => formatTime($doctor['monday_start']),
                        'end' => formatTime($doctor['monday_end'])
                    ],
                    'tuesday' => [
                        'start' => formatTime($doctor['tuesday_start']),
                        'end' => formatTime($doctor['tuesday_end'])
                    ],
                    'wednesday' => [
                        'start' => formatTime($doctor['wednesday_start']),
                        'end' => formatTime($doctor['wednesday_end'])
                    ],
                    'thursday' => [
                        'start' => formatTime($doctor['thursday_start']),
                        'end' => formatTime($doctor['thursday_end'])
                    ],
                    'friday' => [
                        'start' => formatTime($doctor['friday_start']),
                        'end' => formatTime($doctor['friday_end'])
                    ],
                    'saturday' => [
                        'start' => formatTime($doctor['saturday_start']),
                        'end' => formatTime($doctor['saturday_end'])
                    ]
                ]
            ]
        ];
        
        // Send JSON response
        echo json_encode($response);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Doctor not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>