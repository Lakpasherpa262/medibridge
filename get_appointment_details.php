<?php
session_start();
require_once 'scripts/connect.php';

if (!isset($_SESSION['id']) || !isset($_GET['id'])) {
    die("Unauthorized access");
}

$appointment_id = $_GET['id'];
$doctor_id = $_SESSION['doctor_id'] ?? null;

try {
    // Fetch appointment details with patient and doctor information
    $stmt = $db->prepare("
        SELECT 
            a.id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            a.notes,
            a.created_at,
            CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) AS patient_name,
            u.email AS patient_email,
            u.phone AS patient_phone,
            u.date_of_birth AS patient_dob,
            u.gender AS patient_gender,
            CONCAT(d.name) AS doctor_name,
            d.specialization AS doctor_specialization
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.id = ? AND a.doctor_id = ?
    ");
    $stmt->execute([$appointment_id, $doctor_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        die("Appointment not found or you don't have permission to view it");
    }

    // Format the output
    $output = '
    <div class="row">
        <div class="col-md-6">
            <h5><i class="fas fa-user me-2"></i> Patient Information</h5>
            <table class="table table-sm">
                <tr>
                    <th>Name:</th>
                    <td>'.htmlspecialchars($appointment['patient_name']).'</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>'.htmlspecialchars($appointment['patient_email']).'</td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td>'.htmlspecialchars($appointment['patient_phone']).'</td>
                </tr>
                <tr>
                    <th>Date of Birth:</th>
                    <td>'.(!empty($appointment['patient_dob']) ? date('M j, Y', strtotime($appointment['patient_dob'])) : 'N/A').'</td>
                </tr>
                <tr>
                    <th>Gender:</th>
                    <td>'.(!empty($appointment['patient_gender']) ? htmlspecialchars($appointment['patient_gender']) : 'N/A').'</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5><i class="fas fa-calendar-alt me-2"></i> Appointment Details</h5>
            <table class="table table-sm">
                <tr>
                    <th>Date:</th>
                    <td>'.date('M j, Y', strtotime($appointment['appointment_date'])).'</td>
                </tr>
                <tr>
                    <th>Time:</th>
                    <td>'.date('h:i A', strtotime($appointment['appointment_time'])).'</td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td><span class="status-badge status-'.strtolower($appointment['status']).'">'.ucfirst($appointment['status']).'</span></td>
                </tr>
                <tr>
                    <th>Doctor:</th>
                    <td>'.htmlspecialchars($appointment['doctor_name']).' ('.htmlspecialchars($appointment['doctor_specialization']).')</td>
                </tr>
                <tr>
                    <th>Created At:</th>
                    <td>'.date('M j, Y h:i A', strtotime($appointment['created_at'])).'</td>
                </tr>
            </table>
        </div>
    </div>';

    if (!empty($appointment['notes'])) {
        $output .= '
        <div class="mt-4">
            <h5><i class="fas fa-sticky-note me-2"></i> Notes</h5>
            <div class="card p-3">
                '.nl2br(htmlspecialchars($appointment['notes'])).'
            </div>
        </div>';
    }

    echo $output;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>