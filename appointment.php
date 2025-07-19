<?php
session_start();
require_once 'scripts/connect.php';

// Initialize variables
$doctor = [
    'name' => 'Doctor',
    'email' => 'Not available',
    'image_path' => 'images/doctor-avatar.png',
    'specialization' => 'General Practitioner'
];
$error = '';
$success = '';
$doctor_id = null;
$appointments = [];

// Fetch doctor_id from users table if logged in
if (isset($_SESSION['id'])) {
    try {
        $stmt = $db->prepare("SELECT doctor_id FROM users WHERE id = ? AND role = '3'");
        $stmt->execute([$_SESSION['id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData && !empty($userData['doctor_id'])) {
            $doctor_id = $userData['doctor_id'];
            $_SESSION['doctor_id'] = $doctor_id;

            // Fetch doctor details
            $stmt = $db->prepare("SELECT name, email, image_path, specialization FROM doctors WHERE doctor_id = ?");
            $stmt->execute([$doctor_id]);
            $doctorData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($doctorData) {
                $doctor['name'] = $doctorData['name'];
                $doctor['email'] = $doctorData['email'] ?? 'Not available';
                $doctor['specialization'] = $doctorData['specialization'];
                $doctor['image_path'] = $doctorData['image_path'] ?? 'images/doctor-avatar.png';
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Fetch appointments with patient details
if ($doctor_id) {
    try {
        $stmt = $db->prepare("
            SELECT 
                CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) AS patient_name,
                u.phone AS patient_phone,
                u.email AS patient_email,
                a.appointment_date,
                a.appointment_time,
                a.status,
                a.notes
            FROM appointments a
            JOIN users u ON a.user_id = u.id
            WHERE a.doctor_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $stmt->execute([$doctor_id]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error fetching appointments: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management | MediBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-light: #3b82f6;
            --sidebar-bg: #1e293b;
            --sidebar-text: #e2e8f0;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            display: flex;
            min-height: 100vh;
        }

        .doctor-photo-container {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid rgba(255, 255, 255, 0.2);
    margin: 20px 0;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.doctor-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            height: 100vh;
            position: fixed;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            flex-grow: 1;
            transition: all 0.3s;
        }

        .nav-link {
            color: var(--sidebar-text);
            padding: 0.75rem;
            border-radius: 8px;
            margin: 0.25rem 0;
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
        }

        .logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            overflow: hidden;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #d39e00;
        }

        .status-confirmed {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }

        .status-completed {
            background-color: rgba(13, 110, 253, 0.2);
            color: #0d6efd;
        }

        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover {
            background-color: #f8f9fa;
        }

        #cameraModal .modal-body {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #cameraFeed {
            width: 100%;
            max-width: 500px;
            background: #000;
        }

        .call-controls {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .card {
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            border: none;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                z-index: 1000;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="d-flex justify-content-center">
        <div class="doctor-photo-container">
            <img src="<?php echo htmlspecialchars($doctor['image_path']); ?>" alt="Doctor" class="doctor-photo">
        </div>
    </div>
    
    <h5 class="text-center mb-4">Dr. <?php echo htmlspecialchars($doctor['name']); ?></h5>
    
    <nav class="nav flex-column">
        <a class="nav-link" href="doctor.php">
            <i class="fas fa-home me-2"></i> Dashboard
        </a>
        <a class="nav-link active" href="appointment.php">
            <i class="fas fa-calendar-check me-2"></i> Appointments
        </a>
    </nav>
    
    <div class="mt-auto pt-3">
        <a href="doctor.php" class="btn btn-outline-light w-100">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>
</div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-check me-2"></i>Appointment Management</h2>
            <button class="btn btn-primary d-lg-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Your Appointments</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="appointmentsTable">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Patient_Name</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr class="clickable-row" data-id="<?php echo $counter; ?>">
                                    <td><?php echo $counter; ?></td>
                                    <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['patient_phone']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary start-call">
                                            <i class="fas fa-phone"></i> Call
                                        </button>
                                    </td>
                                </tr>
                                <?php $counter++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Camera Modal -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Video Call</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <video id="cameraFeed" autoplay playsinline></video>
                    <div class="call-controls">
                        <button id="endCall" class="btn btn-danger">
                            <i class="fas fa-phone-slash"></i> End Call
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Toggle sidebar on mobile
    $('#sidebarToggle').click(function() {
        $('.sidebar').toggleClass('active');
    });

    // Initialize DataTable
    $('#appointmentsTable').DataTable({
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });

    // Clickable rows for appointment details
    $(document).on('click', '.clickable-row', function(e) {
        // Don't trigger if clicked on a button inside the row
        if ($(e.target).is('button') || $(e.target).is('input') || $(e.target).is('a')) {
            return;
        }
        
        const row = $(this);
        const patientName = row.find('td:nth-child(2)').text();
        const patientPhone = row.find('td:nth-child(3)').text();
        const appointmentDate = row.find('td:nth-child(4)').text();
        const appointmentTime = row.find('td:nth-child(5)').text();
        const status = row.find('td:nth-child(6)').text();
        
        // Create modal content
        const modalContent = `
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-user me-2"></i> Patient Information</h5>
                    <table class="table table-sm">
                        <tr>
                            <th>Name:</th>
                            <td>${patientName}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>${patientPhone}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5><i class="fas fa-calendar-alt me-2"></i> Appointment Details</h5>
                    <table class="table table-sm">
                        <tr>
                            <th>Date:</th>
                            <td>${appointmentDate}</td>
                        </tr>
                        <tr>
                            <th>Time:</th>
                            <td>${appointmentTime}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>${status}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        
        // Create modal if it doesn't exist
        if ($('#detailsModal').length === 0) {
            $('body').append(`
                <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Appointment Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="modalDetails">
                                ${modalContent}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        } else {
            $('#modalDetails').html(modalContent);
        }
        
        // Show the modal
        $('#detailsModal').modal('show');
    });

    // Start call button - Open camera
    $(document).on('click', '.start-call', function(e) {
        e.stopPropagation();
        const row = $(this).closest('tr');
        const appointmentId = row.data('id');
        
        // Store appointment ID and row reference in modal
        $('#cameraModal').data({
            'appointmentId': appointmentId,
            'row': row
        });
        
        // Request camera access
        navigator.mediaDevices.getUserMedia({ video: true, audio: true })
            .then(function(stream) {
                // Show the camera modal
                $('#cameraModal').modal('show');
                
                // Display the camera feed
                const video = document.getElementById('cameraFeed');
                video.srcObject = stream;
                
                // Store the stream for later cleanup
                window.currentStream = stream;
            })
            .catch(function(err) {
                console.error("Error accessing camera: ", err);
                alert("Could not access camera. Please check permissions.");
            });
    });

    // End call button
    $('#endCall').click(function() {
        const modalData = $('#cameraModal').data();
        const appointmentId = modalData.appointmentId;
        const row = modalData.row;
        
        // Stop all tracks in the stream
        if (window.currentStream) {
            window.currentStream.getTracks().forEach(track => track.stop());
        }
        
        // Update status to completed via AJAX
        $.ajax({
            url: 'includes/update_appointment.php',
            type: 'POST',
            data: {
                action: 'complete_appointment',
                appointment_id: appointmentId
            },
            success: function(response) {
                // Update the status in the table
                const statusBadge = row.find('.status-badge');
                statusBadge.removeClass('status-pending status-confirmed')
                            .addClass('status-completed')
                            .text('Completed');
                
                // Close the modal
                $('#cameraModal').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error("Error updating status:", error);
                alert("Error updating appointment status");
                $('#cameraModal').modal('hide');
            }
        });
    });

    // Clean up when modal is closed
    $('#cameraModal').on('hidden.bs.modal', function() {
        if (window.currentStream) {
            window.currentStream.getTracks().forEach(track => track.stop());
            window.currentStream = null;
        }
    });

    // Auto-close alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
</script>
</body>
</html>