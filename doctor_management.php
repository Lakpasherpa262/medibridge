<?php
include 'scripts/connect.php';
session_start();

// Verify shop_id is available in session
if (!isset($_SESSION['shop_id'])) {
    die("Shop not identified. Please access through your shop dashboard.");
}

$shop_id = $_SESSION['shop_id'];

// Fetch specializations with names
try {
    $stmt = $db->prepare("SELECT s.id, s.specialization_name 
                         FROM specializations s
                         JOIN doctors d ON s.id = d.specialization
                         WHERE d.shop_id = :shop_id 
                         GROUP BY s.id
                         ORDER BY s.specialization_name");
    $stmt->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
    $stmt->execute();
    $specializations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching specializations: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBridge - Doctor Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
    :root {
        --primary-color: #1e293b;
        --primary-light: #1e293b;
        --secondary-color: #6c757d;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
        --sidebar-bg: #1e293b;
        --sidebar-text: #e2e8f0;
        --sidebar-active: #334155;
        --card-radius: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f5f7fa;
        color: var(--dark-color);
        line-height: 1.6;
    }

    .sidebar {
        width: 280px;
        background: var(--sidebar-bg);
        color: var(--sidebar-text);
        height: 100vh;
        position: fixed;
        box-shadow: 2px 0 15px rgba(0,0,0,0.1);
        transition: var(--transition);
        z-index: 1000;
    }

    .sidebar-header {
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.15);
    }

    .logo-img {
        width: 80px;
        height: 80px;
        object-fit: contain;
        margin-bottom: 15px;
        border-radius: 50%;
        background: white;
        padding: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .logo-text {
        font-size: 22px;
        font-weight: 600;
        margin-top: 10px;
    }

    .nav-item {
        color: rgba(255,255,255,0.9);
        padding: 12px 25px;
        display: flex;
        align-items: center;
        transition: var(--transition);
        margin: 5px 10px;
        border-radius: 6px;
        text-decoration: none;
    }

    .nav-item:hover {
        background: rgba(255,255,255,0.15);
        color: white;
    }

    .nav-item.active {
        background: var(--sidebar-active);
        color: #e2e8f0;
        font-weight: 500;
    }

    .nav-item i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        font-size: 18px;
    }

    .main-content {
        margin-left: 280px;
        padding: 30px;
        transition: var(--transition);
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        margin-bottom: 25px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .header h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
    }
    
    .header h1 i {
        margin-right: 10px;
        color: #1e293b;
    }

    .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        margin-bottom: 30px;
        overflow: hidden;
        transition: var(--transition);
    }

    .card:hover {
        box-shadow: var(--shadow-lg);
    }

    .card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 15px 25px;
        border-bottom: none;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    .card-header h5 i {
        margin-right: 10px;
    }

    .table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        border-bottom: none;
        background-color: #f8f9fa;
        font-weight: 500;
        color: var(--secondary-color);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 12px 15px;
    }

    .table tbody tr {
        transition: var(--transition);
    }

    .table tbody tr:hover {
        background-color: rgba(30, 41, 59, 0.05);
        transform: translateY(-2px);
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-top: 1px solid #f0f0f0;
    }

    .specialization-header {
        background-color: var(--primary-color);
        color: white;
        padding: 10px 15px;
        border-radius: 5px 5px 0 0;
        margin-bottom: 0;
    }

    .modal-content {
        border: none;
        border-radius: var(--card-radius);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        background-color: var(--primary-color);
        color: white;
        padding: 20px;
    }

    .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .modal-title i {
        margin-right: 10px;
    }

    .modal-body {
        padding: 25px;
    }

    .section-title {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-weight: 600;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 10px;
    }

    .img-thumbnail {
        border-radius: 50%;
        width: 150px;
        height: 150px;
        object-fit: cover;
    }

    .alert-warning {
        background-color: rgba(255, 193, 7, 0.1);
        border-color: rgba(255, 193, 7, 0.2);
    }

    @media (max-width: 992px) {
        .sidebar {
            width: 240px;
        }
        .main-content {
            margin-left: 240px;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
    }
    /* Add to your existing CSS */
.card-header.bg-light {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6;
}

.table-borderless td, 
.table-borderless th {
    border: none;
    padding: 0.5rem;
}

.table-bordered {
    border: 1px solid #dee2e6;
}

.table-bordered th, 
.table-bordered td {
    border: 1px solid #dee2e6;
    padding: 0.75rem;
}

.text-muted {
    color: #6c757d !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}
/* Improved schedule display */
.schedule-container {
    max-height: 300px;
    overflow-y: auto;
}

.schedule-day {
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 8px;
    background-color: white;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.schedule-day:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.day-name {
    width: 100px;
    font-weight: 600;
    color: var(--primary-color);
}

.time-inputs {
    display: flex;
    align-items: center;
    margin-right: 15px;
}

.time-inputs .form-control-sm {
    width: 100px;
}

.time-display {
    font-size: 0.9rem;
    color: #495057;
    min-width: 180px;
    padding: 5px 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .schedule-day {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .time-inputs {
        margin: 8px 0;
        width: 100%;
    }
    
    .time-display {
        width: 100%;
        margin-top: 5px;
    }
}

/* Loading states */
.btn .fa-spinner {
    margin-right: 8px;
}
/* Add this to your existing CSS */
#updateImageBtn {
    background-color: #28a745;
    border-color: #28a745;
}

#updateImageBtn:hover {
    background-color: #218838;
    border-color: #1e7e34;
}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="text-center">
                <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">
                <div class="logo-text">MediBridge</div>
            </div>
        </div>
        
        <a href="shop.php" class="nav-item">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
        
        <div class="nav-item active">
            <i class="fas fa-user-md"></i>
            <span>Doctor Management</span>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="w-100">
                <h1><i class="fas fa-user-md me-2"></i>Doctor Management</h1>
            </div>
        </div>
        
       <?php if (empty($specializations)): ?>
        <div class="alert alert-info">
            No doctors found for your shop. Please add doctors to see them listed here.
        </div>
    <?php else: ?>
        <?php foreach ($specializations as $spec): ?>
            <?php
                try {
                    $stmt = $db->prepare("SELECT d.doctor_id, d.name, d.email, d.phone, d.image_path, 
                                         s.specialization_name as specialization
                                         FROM doctors d
                                         JOIN specializations s ON d.specialization = s.id
                                         WHERE d.shop_id = :shop_id AND d.specialization = :specialization_id
                                         ORDER BY d.name");
                    $stmt->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
                    $stmt->bindParam(':specialization_id', $spec['id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Error fetching doctors: " . $e->getMessage());
                }
            ?>
            
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-stethoscope me-2"></i> <?= htmlspecialchars($spec['specialization_name']) ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover specialization-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="d-none">ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($doctors)): ?>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <tr class="clickable-row" data-id="<?= $doctor['doctor_id'] ?>">
                                            <td class="d-none"><?= $doctor['doctor_id'] ?></td>
                                            <td><?= htmlspecialchars($doctor['name']) ?></td>
                                            <td><?= htmlspecialchars($doctor['email']) ?></td>
                                            <td><?= htmlspecialchars($doctor['phone']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            No doctors found for <?= htmlspecialchars($spec['specialization_name']) ?> specialization
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>

    <!-- Edit Doctor Modal -->
<div class="modal fade" id="editDoctorModal" tabindex="-1" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDoctorModalLabel">
                    <i class="fas fa-user-md me-2"></i> Doctor Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically via AJAX -->
            </div>
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
    // Make rows clickable to open edit modal
    $(document).on('click', '.clickable-row', function(e) {
        if ($(e.target).is('a, button, input, select, textarea')) return;
        
        var doctorId = $(this).data('id');
        
        $('#editDoctorModal').modal('show');
        $('#editDoctorModal .modal-body').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading doctor details...</p>
            </div>
        `);
        
        $.ajax({
            url: 'includes/fetch_doctor.php',
            type: 'GET',
            data: { id: doctorId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const doctor = response.data;
                    
                    // Function to convert 24h time to 12h AM/PM format
                    function formatTimeToAMPM(time24) {
                        if (!time24) return '';
                        const [hours, minutes] = time24.split(':');
                        const period = +hours >= 12 ? 'PM' : 'AM';
                        const hours12 = +hours % 12 || 12;
                        return `${hours12}:${minutes} ${period}`;
                    }
                    
                    // Build schedule HTML with AM/PM display
                    let scheduleHtml = '';
                    const days = {
                        monday: 'Monday',
                        tuesday: 'Tuesday',
                        wednesday: 'Wednesday',
                        thursday: 'Thursday',
                        friday: 'Friday',
                        saturday: 'Saturday'
                    };
                    
                    for (const [day, dayName] of Object.entries(days)) {
                        const startTime = doctor.schedule[day].start;
                        const endTime = doctor.schedule[day].end;
                        
                        if (startTime || endTime) {
                            scheduleHtml += `
                                <div class="schedule-day d-flex align-items-center mb-2">
                                    <div class="day-name" style="width: 100px;">
                                        <strong>${dayName}</strong>
                                    </div>
                                    <div class="time-inputs d-flex align-items-center me-2">
                                        <input type="time" class="form-control form-control-sm" 
                                               name="${day}_start" value="${startTime || ''}" 
                                               style="width: 90px;">
                                        <span class="mx-1">to</span>
                                        <input type="time" class="form-control form-control-sm" 
                                               name="${day}_end" value="${endTime || ''}" 
                                               style="width: 90px;">
                                    </div>
                                    <div class="time-display text-muted small">
                                        ${startTime ? formatTimeToAMPM(startTime) : 'Not set'} to ${endTime ? formatTimeToAMPM(endTime) : 'Not set'}
                                    </div>
                                </div>
                            `;
                        }
                    }
                    
                    // Handle image path - prepend base URL if it's a relative path
                    let imagePath = doctor.image_path || 'images/default-doctor.png';
                    if (imagePath && !imagePath.startsWith('http') && !imagePath.startsWith('/')) {
                        imagePath = 'images/' + imagePath;
                    }
                    
                    // Populate modal with doctor details
                    $('#editDoctorModal .modal-body').html(`
                        <form id="doctorDetailsForm">
                            <input type="hidden" name="doctor_id" value="${doctor.id}">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Doctor Information</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="name" value="${doctor.name}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="${doctor.email}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" name="phone" value="${doctor.phone}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Specialization</label>
                                        <p class="form-control-static">${doctor.specialization}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Degree</label>
                                        <input type="text" class="form-control" name="degree" value="${doctor.degree || ''}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">License Number</label>
                                        <input type="text" class="form-control" name="license_no" value="${doctor.license_no || ''}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="section-title"><i class="fas fa-image me-2"></i>Doctor Photo</h5>
                                    <div class="text-center mb-3">
                                        <img src="${imagePath}" 
                                             alt="Doctor Photo" 
                                             class="img-thumbnail" 
                                             style="width: 200px; height: 200px; object-fit: cover;"
                                             onerror="this.onerror=null; this.src='images/default-doctor.png'">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Update Photo</label>
                                        <input type="file" class="form-control" name="image" id="doctorImage" accept="image/*">
                                        <button type="button" class="btn btn-success mt-2 w-100" id="updateImageBtn">
                                            <i class="fas fa-upload me-1"></i> Upload Image
                                        </button>
                                    </div>
                                    
                                    <h5 class="section-title mt-4"><i class="fas fa-calendar-alt me-2"></i>Working Schedule</h5>
                                    <div class="schedule-container bg-light p-3 rounded">
                                        ${scheduleHtml || '<p class="text-muted mb-0">No schedule information available</p>'}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-success" id="updateDetailsBtn">
                                    <i class="fas fa-save me-1"></i> Update Details
                                </button>
                                <div>
                                    <button type="button" class="btn btn-danger me-2" id="deleteDoctorBtn">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i> Close
                                    </button>
                                </div>
                            </div>
                        </form>
                    `);

                    // Set up event handlers
                    $('#updateDetailsBtn').click(function() {
                        updateDoctorDetails(doctorId);
                    });

                    $('#updateImageBtn').click(function() {
                        updateDoctorImage(doctorId);
                    });

                    $('#deleteDoctorBtn').click(function() {
                        if(confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
                            deleteDoctor(doctorId);
                        }
                    });
                    
                    // Update time display when time inputs change
                    $('input[type="time"]').on('change', function() {
                        const inputName = $(this).attr('name');
                        const day = inputName.split('_')[0];
                        const startTime = $(`input[name="${day}_start"]`).val();
                        const endTime = $(`input[name="${day}_end"]`).val();
                        
                        const displayElement = $(this).closest('.schedule-day').find('.time-display');
                        displayElement.text(
                            `${startTime ? formatTimeToAMPM(startTime) : 'Not set'} to ${endTime ? formatTimeToAMPM(endTime) : 'Not set'}`
                        );
                    });
                } else {
                    showError(response.message || 'Error fetching doctor details');
                }
            },
            error: function(xhr, status, error) {
                showError('Error fetching doctor details: ' + error);
            }
        });
    });

    // Function to update doctor details
    function updateDoctorDetails(doctorId) {
        // Show loading state
        $('#updateDetailsBtn').html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');
        
        const formData = $('#doctorDetailsForm').serializeArray();
        
        // Convert time inputs to 24-hour format
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        days.forEach(day => {
            const startInput = $(`input[name="${day}_start"]`).val();
            const endInput = $(`input[name="${day}_end"]`).val();
            
            if (startInput) {
                formData.push({name: `${day}_start`, value: startInput});
            }
            if (endInput) {
                formData.push({name: `${day}_end`, value: endInput});
            }
        });
        
        $.ajax({
            url: 'includes/edit_doctor.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    alert(response.message || 'Doctor details updated successfully!');
                    $('#editDoctorModal').modal('hide');
                    location.reload();
                } else {
                    showError(response.message || 'Failed to update details');
                    if (response.error_info) {
                        console.error('Database error:', response.error_info);
                    }
                }
            },
            error: function(xhr, status, error) {
                showError('Error updating doctor details: ' + error);
                console.error(xhr.responseText);
            },
            complete: function() {
                // Reset button state
                $('#updateDetailsBtn').html('<i class="fas fa-save me-1"></i> Update Details');
            }
        });
    }

    // Function to update doctor image
    function updateDoctorImage(doctorId) {
        const imageFile = $('#doctorImage')[0].files[0];
        
        if(!imageFile) {
            alert('Please select an image file first');
            return;
        }
        
        // Show loading state
        $('#updateImageBtn').html('<i class="fas fa-spinner fa-spin me-1"></i> Uploading...');
        
        const formData = new FormData();
        formData.append('doctor_id', doctorId);
        formData.append('image', imageFile);
        
        $.ajax({
            url: 'includes/edit_doctor.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    alert(response.message || 'Doctor image updated successfully!');
                    // Update the image preview without reloading
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#editDoctorModal .img-thumbnail').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(imageFile);
                } else {
                    showError(response.message || 'Failed to update image');
                }
            },
            error: function(xhr, status, error) {
                showError('Error updating doctor image: ' + error);
                console.error(xhr.responseText);
            },
            complete: function() {
                // Reset button state
                $('#updateImageBtn').html('<i class="fas fa-upload me-1"></i> Upload Image');
            }
        });
    }

    // Function to delete doctor
    function deleteDoctor(doctorId) {
        if(confirm('Are you absolutely sure you want to delete this doctor? All associated data will be permanently removed.')) {
            // Show loading state
            $('#deleteDoctorBtn').html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');
            
            $.ajax({
                url: 'includes/delete_doctor.php',
                type: 'POST',
                data: { doctor_id: doctorId },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        alert('Doctor deleted successfully!');
                        $('#editDoctorModal').modal('hide');
                        location.reload();
                    } else {
                        showError(response.message || 'Failed to delete doctor');
                    }
                },
                error: function(xhr, status, error) {
                    showError('Error deleting doctor: ' + error);
                    console.error(xhr.responseText);
                },
                complete: function() {
                    // Reset button state
                    $('#deleteDoctorBtn').html('<i class="fas fa-trash me-1"></i> Delete');
                }
            });
        }
    }

    // Helper function to show error messages
    function showError(message) {
        // Create or update error alert
        let $alert = $('#editDoctorModal .alert-danger');
        if ($alert.length === 0) {
            $alert = $(`
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span class="error-message"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `).prependTo('#editDoctorModal .modal-body');
        }
        
        // Clean HTML tags from message to prevent XSS
        const cleanMsg = $('<div>').text(message).html();
        $alert.find('.error-message').html(cleanMsg);
        
        // Scroll to the error message
        $('html, body').animate({
            scrollTop: $('#editDoctorModal').offset().top
        }, 500);
    }
});
</script>
</body>
</html>