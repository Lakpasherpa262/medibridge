<?php
include 'scripts/connect.php';
session_start();

if (!isset($_SESSION['shop_id'])) {
    die("Shop not identified.");
}

$shop_id = $_SESSION['shop_id'];

try {
    $stmt = $db->prepare("SELECT p.id, p.patient_name, p.patient_email, p.patient_phone, p.user_id, 
                         p.img, p.created_at, p.status, u.dob, u.gender, u.address, u.pincode
                         FROM prescriptions p
                         LEFT JOIN users u ON p.user_id = u.id
                         WHERE p.shop_id = :shop_id 
                         ORDER BY p.created_at DESC");
    $stmt->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
    $stmt->execute();
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching prescriptions: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Management | MediBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        margin-bottom: 30px;
    }

    .header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        color: var(--primary-color);
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

    .badge {
        font-weight: 500;
        padding: 6px 12px;
        font-size: 0.75rem;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .status-approved {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .status-rejected {
        background-color: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
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

    .patient-info {
        margin-bottom: 20px;
    }

    .patient-details h4 {
        margin: 0;
        color: var(--primary-color);
        font-weight: 600;
    }

    .patient-details p {
        margin: 5px 0 0;
        color: var(--secondary-color);
    }

    .prescription-image-container {
        margin-top: 20px;
        text-align: center;
    }

    .prescription-image {
        max-width: 60%;
        max-height: 300px;
        border-radius: 6px;
        box-shadow: var(--shadow-md);
        margin-bottom: 15px;
    }

    .detail-section {
        margin-bottom: 25px;
    }

    .detail-section h5 {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-weight: 600;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
    }

    .detail-section h5 i {
        margin-right: 10px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .detail-item {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 6px;
    }

    .detail-label {
        font-weight: 500;
        color: var(--secondary-color);
        font-size: 0.85rem;
        margin-bottom: 5px;
    }

    .detail-value {
        font-weight: 500;
        color: var(--dark-color);
    }

    .btn {
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 500;
        transition: var(--transition);
        letter-spacing: 0.5px;
    }

    .btn i {
        margin-right: 8px;
    }

    .download-btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
    }

    .download-btn:hover {
        background-color: var(--primary-light);
        color: white;
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
        
        .detail-grid {
            grid-template-columns: 1fr;
        }
        
        .prescription-image {
            max-width: 100%;
        }
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
            <i class="fas fa-prescription-bottle-alt"></i>
            <span>Prescription Management</span>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div>
                <h1><i class="fas fa-prescription-bottle-alt me-2"></i>Prescription Management</h1>
                <hr style="border-top: 2px solid #1e293b; margin-top: 10px; opacity: 0.5;">
            </div>
        </div>        
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i> Prescriptions List</h5>
            </div>
            <div class="card-body">
                <table id="prescriptionsTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th class="d-none">ID</th>
                            <th class="d-none">User ID</th>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $prescription): ?>
                        <tr class="clickable-row" 
                            data-id="<?= $prescription['id'] ?>"
                            data-user-id="<?= $prescription['user_id'] ?>"
                            data-img="<?= htmlspecialchars($prescription['img']) ?>"
                            data-dob="<?= htmlspecialchars($prescription['dob']) ?>"
                            data-gender="<?= htmlspecialchars($prescription['gender']) ?>"
                            data-address="<?= htmlspecialchars($prescription['address']) ?>"
                            data-pincode="<?= htmlspecialchars($prescription['pincode']) ?>"
                            data-status="<?= htmlspecialchars($prescription['status']) ?>">
                            <td class="d-none"><?= $prescription['id'] ?></td>
                            <td class="d-none"><?= $prescription['user_id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($prescription['patient_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($prescription['patient_email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($prescription['patient_phone']) ?></td>
                            <td><?= date('M d, Y h:i A', strtotime($prescription['created_at'])) ?></td>
                            <td>
                                <span class="badge status-<?= strtolower($prescription['status']) ?>">
                                    <?= $prescription['status'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Prescription Details Modal -->
    <div class="modal fade" id="prescriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-prescription me-2"></i>Prescription Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="patient-info">
                        <div class="patient-details">
                            <h4 id="modalPatientName"></h4>
                            <p id="modalPatientEmail"></p>
                            <p id="modalPatientPhone"></p>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h5><i class="fas fa-info-circle me-2"></i>Prescription Information</h5>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Status</div>
                                <div class="detail-value" id="modalPrescriptionStatus"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Date of Birth</div>
                                <div class="detail-value" id="modalPatientDob"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Gender</div>
                                <div class="detail-value" id="modalPatientGender"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Address</div>
                                <div class="detail-value" id="modalPatientAddress"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Pincode</div>
                                <div class="detail-value" id="modalPatientPincode"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="prescription-image-container">
                        <div id="prescriptionImageContainer" class="mt-4"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="approveBtn">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger" id="rejectBtn">
                        <i class="fas fa-times-circle"></i> Reject
                    </button>
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
        var table = $('#prescriptionsTable').DataTable({
            responsive: true,
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search prescriptions...",
            },
            columnDefs: [
                { targets: [0, 1], visible: false }
            ],
            order: [[4, 'desc']]
        });

        var currentPrescriptionId = null;
        var currentStatus = null;
        
        $('#prescriptionsTable tbody').on('click', 'tr', function() {
            var row = $(this);
            currentPrescriptionId = row.data('id');
            currentStatus = row.data('status');
            
            // Set patient info
            $('#modalPatientName').text(row.find('.fw-bold').text());
            $('#modalPatientEmail').text(row.find('.text-muted').text());
            $('#modalPatientPhone').text(row.find('td').eq(2).text());
            
            // Set status
            var statusBadge = row.find('.badge').clone();
            $('#modalPrescriptionStatus').empty().append(statusBadge);
            
            // Set patient details
            $('#modalPatientDob').text(row.data('dob') || 'Not provided');
            $('#modalPatientGender').text(row.data('gender') || 'Not provided');
            $('#modalPatientAddress').text(row.data('address') || 'Not provided');
            $('#modalPatientPincode').text(row.data('pincode') || 'Not provided');
            
            // Set prescription image
            var imageContainer = $('#prescriptionImageContainer');
            imageContainer.empty();
            
            if (row.data('img')) {
                var img = $('<img>', {
                    'class': 'prescription-image',
                    'src': row.data('img'),
                    'alt': 'Prescription'
                });
                
                var downloadLink = $('<a>', {
                    'href': row.data('img'),
                    'download': 'prescription_' + row.data('id'),
                    'class': 'btn download-btn mt-3',
                    'html': '<i class="fas fa-download me-2"></i>Download Prescription'
                });
                
                imageContainer.append(img, $('<div class="text-center">').append(downloadLink));
            } else {
                imageContainer.html('<div class="alert alert-warning text-center">No prescription image available</div>');
            }
            
            // Show/hide buttons based on current status
            if (currentStatus === 'Approved' || currentStatus === 'Rejected') {
                $('#approveBtn, #rejectBtn').hide();
            } else {
                $('#approveBtn, #rejectBtn').show();
            }
            
            // Show modal
            var modal = new bootstrap.Modal(document.getElementById('prescriptionModal'));
            modal.show();
        });

        $('#approveBtn').on('click', function() {
            updatePrescriptionStatus('Approved');
        });

        $('#rejectBtn').on('click', function() {
            updatePrescriptionStatus('Rejected');
        });

        function updatePrescriptionStatus(newStatus) {
            if (!currentPrescriptionId) {
                showToast('No prescription selected', 'error');
                return;
            }
            
            $.ajax({
                url: 'includes/update_prescription_status.php',
                method: 'POST',
                data: {
                    id: currentPrescriptionId,
                    status: newStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Find the row in the table and update its status
                        var row = $('#prescriptionsTable').find(`tr[data-id="${currentPrescriptionId}"]`);
                        row.data('status', newStatus);
                        
                        // Update the status badge
                        var badge = row.find('.badge');
                        badge.removeClass('status-pending status-approved status-rejected')
                            .addClass('status-' + newStatus.toLowerCase())
                            .text(newStatus);
                        
                        // Close the modal
                        $('#prescriptionModal').modal('hide');
                        
                        // Show success message
                        showToast('Prescription ' + newStatus.toLowerCase() + ' successfully', 'success');
                    } else {
                        showToast(response.message || 'Error updating status', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showToast('Error updating status: ' + error, 'error');
                }
            });
        }

        function showToast(message, type) {
            // Create toast element
            var toast = $(`
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            `);
            
            // Add to DOM
            $('body').append(toast);
            
            // Initialize and show toast
            var bsToast = new bootstrap.Toast(toast.find('.toast')[0]);
            bsToast.show();
            
            // Remove toast after it's hidden
            toast.find('.toast').on('hidden.bs.toast', function() {
                toast.remove();
            });
        }
    });
    </script>
</body>
</html>