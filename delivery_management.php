<?php
include 'scripts/connect.php';
session_start();

// Verify admin access
if (!isset($_SESSION['id'])) {
    die("Access denied. Please login first.");
}

// Fetch delivery personnel (role_id = 4)
try {
    $stmt = $db->prepare("SELECT id, first_name, middle_name, last_name, email, phone, gender, dob, address, state, 
    district, pincode, landmark FROM users WHERE role = 4");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}

// Fetch all shops
try {
    $shopStmt = $db->prepare("SELECT id, shop_name, pincode FROM shopdetails");
    $shopStmt->execute();
    $shops = $shopStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching shops: " . $e->getMessage());
}

// Handle shop assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_shop'])) {
    try {
        $deliveryPersonId = $_POST['delivery_person_id'];
        $shopId = $_POST['shop_id'];
        
        // Check if this shop is already assigned to someone else
        $checkStmt = $db->prepare("SELECT id FROM delivery_assignments WHERE shop_id = :shop_id AND delivery_person_id != :delivery_person_id");
        $checkStmt->bindParam(':shop_id', $shopId);
        $checkStmt->bindParam(':delivery_person_id', $deliveryPersonId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'This shop is already assigned to another delivery person'];
        } else {
            // Check if this delivery person already has this shop assigned
            $existingStmt = $db->prepare("SELECT id FROM delivery_assignments WHERE shop_id = :shop_id AND delivery_person_id = :delivery_person_id");
            $existingStmt->bindParam(':shop_id', $shopId);
            $existingStmt->bindParam(':delivery_person_id', $deliveryPersonId);
            $existingStmt->execute();
            
            if ($existingStmt->rowCount() > 0) {
                $_SESSION['message'] = ['type' => 'info', 'text' => 'This shop is already assigned to this delivery person'];
            } else {
                // Assign the shop
                $assignStmt = $db->prepare("INSERT INTO delivery_assignments (delivery_person_id, shop_id, assigned_at) 
                                           VALUES (:delivery_person_id, :shop_id, NOW())");
                $assignStmt->bindParam(':delivery_person_id', $deliveryPersonId);
                $assignStmt->bindParam(':shop_id', $shopId);
                $assignStmt->execute();
                
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Shop assigned successfully'];
            }
        }
        
        header("Location: delivery_management.php");
        exit();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Handle shop unassignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_shop'])) {
    try {
        $assignmentId = $_POST['assignment_id'];
        
        $unassignStmt = $db->prepare("DELETE FROM delivery_assignments WHERE id = :id");
        $unassignStmt->bindParam(':id', $assignmentId);
        $unassignStmt->execute();
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Shop unassigned successfully'];
        header("Location: delivery_management.php");
        exit();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Get assigned shops for modal (AJAX)
if (isset($_GET['get_assigned_shops'])) {
    try {
        $deliveryPersonId = $_GET['delivery_person_id'];
        
        $assignedStmt = $db->prepare("SELECT da.id as assignment_id, s.id as shop_id, s.shop_name, s.pincode 
                                     FROM delivery_assignments da
                                     JOIN shopdetails s ON da.shop_id = s.id
                                     WHERE da.delivery_person_id = :delivery_person_id");
        $assignedStmt->bindParam(':delivery_person_id', $deliveryPersonId);
        $assignedStmt->execute();
        $assignedShops = $assignedStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'assignedShops' => $assignedShops]);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching assigned shops: ' . $e->getMessage()]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBridge - Delivery Management</title>
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

    .user-info {
        margin-bottom: 20px;
    }

    .user-details h4 {
        margin: 0;
        color: var(--primary-color);
        font-weight: 600;
    }

    .user-details p {
        margin: 5px 0 0;
        color: var(--secondary-color);
    }

    .user-image-container {
        margin-top: 20px;
        text-align: center;
    }

    .user-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #eee;
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

    .btn-assign {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    .btn-assign:hover {
        background-color: #218838;
        border-color: #1e7e34;
        color: white;
    }

    .assigned-shops {
        margin-top: 20px;
    }

    .shop-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .shop-info {
        flex: 1;
    }

    .shop-name {
        font-weight: 500;
    }

    .shop-pincode {
        font-size: 0.85rem;
        color: var(--secondary-color);
    }

    .shop-actions {
        margin-left: 15px;
    }

    .form-select {
        border-radius: 6px;
        padding: 10px 15px;
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
        
        <a href="admin_dashboard.php" class="nav-item">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
        
        <div class="nav-item active">
            <i class="fas fa-truck"></i>
            <span>Delivery Management</span>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="w-100">
                <h1><i class="fas fa-truck me-2"></i>Delivery Management</h1>
            </div>
        </div>       
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['message']['text'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i> Delivery Personnel List</h5>
            </div>
            <div class="card-body">
                <table id="usersTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th class="d-none">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Pincode</th>
                            <th>Assigned Shops</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): 
                            $full_name = $user['first_name'];
                            if (!empty($user['middle_name'])) {
                                $full_name .= ' ' . $user['middle_name'];
                            }
                            $full_name .= ' ' . $user['last_name'];
                            
                            // Get assigned shops count for this delivery person
                            $shopCountStmt = $db->prepare("SELECT COUNT(*) as shop_count FROM delivery_assignments WHERE delivery_person_id = :id");
                            $shopCountStmt->bindParam(':id', $user['id']);
                            $shopCountStmt->execute();
                            $shopCount = $shopCountStmt->fetch(PDO::FETCH_ASSOC)['shop_count'];
                        ?>
                        <tr class="clickable-row" 
                            data-id="<?= $user['id'] ?>"
                            data-first-name="<?= htmlspecialchars($user['first_name']) ?>"
                            data-middle-name="<?= htmlspecialchars($user['middle_name']) ?>"
                            data-last-name="<?= htmlspecialchars($user['last_name']) ?>"
                            data-email="<?= htmlspecialchars($user['email']) ?>"
                            data-phone="<?= htmlspecialchars($user['phone']) ?>"
                            data-dob="<?= isset($user['dob']) ? htmlspecialchars($user['dob']) : '' ?>"
                            data-gender="<?= htmlspecialchars($user['gender']) ?>"
                            data-address="<?= isset($user['address']) ? htmlspecialchars($user['address']) : '' ?>"
                            data-state="<?= isset($user['state']) ? htmlspecialchars($user['state']) : '' ?>"
                            data-district="<?= isset($user['district']) ? htmlspecialchars($user['district']) : '' ?>"
                            data-pincode="<?= isset($user['pincode']) ? htmlspecialchars($user['pincode']) : '' ?>"
                            data-landmark="<?= isset($user['landmark']) ? htmlspecialchars($user['landmark']) : '' ?>">
                            <td class="d-none"><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($full_name) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phone']) ?></td>
                            <td><?= htmlspecialchars($user['gender']) ?></td>
                            <td><?= htmlspecialchars($user['pincode']) ?></td>
                            <td><?= $shopCount ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i>Delivery Personnel Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="user-info">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="user-details">
                                    <h4 id="modalUserName"></h4>
                                    <p id="modalUserEmail"></p>
                                    <p id="modalUserPhone"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h5><i class="fas fa-info-circle me-2"></i>Personal Information</h5>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Date of Birth</div>
                                <div class="detail-value" id="modalUserDob"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Gender</div>
                                <div class="detail-value" id="modalUserGender"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Pincode</div>
                                <div class="detail-value" id="modalUserPincode"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h5><i class="fas fa-map-marker-alt me-2"></i>Address Information</h5>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="detail-label">Address</div>
                                <div class="detail-value" id="modalUserAddress"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">State</div>
                                <div class="detail-value" id="modalUserState"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">District</div>
                                <div class="detail-value" id="modalUserDistrict"></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Landmark</div>
                                <div class="detail-value" id="modalUserLandmark"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h5><i class="fas fa-store me-2"></i>Shop Assignment</h5>
                        <form id="assignShopForm" method="post" action="delivery_management.php">
                            <input type="hidden" name="delivery_person_id" id="deliveryPersonId">
                            <div class="row">
                                <div class="col-md-8">
                                    <select class="form-select mb-3" name="shop_id" id="shopSelect" required>
                                        <option value="">Select a shop to assign</option>
                                        <?php foreach ($shops as $shop): ?>
                                            <option value="<?= $shop['id'] ?>"><?= htmlspecialchars($shop['shop_name']) ?> (Pincode: <?= $shop['pincode'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" name="assign_shop" class="btn btn-assign w-100">
                                        <i class="fas fa-link"></i> Assign Shop
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="assigned-shops">
                            <h6>Currently Assigned Shops</h6>
                            <div id="assignedShopsContainer">
                                <!-- Assigned shops will be loaded here via AJAX -->
                                <div class="alert alert-info">Loading assigned shops...</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <button type="button" class="btn btn-danger" id="deleteUserBtn">
                            <i class="fas fa-trash-alt"></i> Delete Personnel
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
        var table = $('#usersTable').DataTable({
            responsive: true,
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search personnel...",
            },
            columnDefs: [
                { targets: [0], visible: false }
            ],
            order: [[1, 'asc']]
        });

        var currentUserId = null;
        
        $('#usersTable tbody').on('click', 'tr', function() {
            var row = $(this);
            currentUserId = row.data('id');
            
            // Build full name
            var firstName = row.data('first-name');
            var middleName = row.data('middle-name');
            var lastName = row.data('last-name');
            
            var fullName = firstName;
            if (middleName) fullName += ' ' + middleName;
            fullName += ' ' + lastName;
            
            // Set user info
            $('#modalUserName').text(fullName);
            $('#modalUserEmail').text(row.data('email'));
            $('#modalUserPhone').text(row.data('phone') || 'Not provided');
            
            // Set user details
            $('#modalUserDob').text(row.data('dob') || 'Not provided');
            $('#modalUserGender').text(row.data('gender') || 'Not provided');
            $('#modalUserAddress').text(row.data('address') || 'Not provided');
            $('#modalUserState').text(row.data('state') || 'Not provided');
            $('#modalUserDistrict').text(row.data('district') || 'Not provided');
            $('#modalUserPincode').text(row.data('pincode') || 'Not provided');
            $('#modalUserLandmark').text(row.data('landmark') || 'Not provided');
            
            // Set delivery person ID in form
            $('#deliveryPersonId').val(currentUserId);
            
            // Load assigned shops
            loadAssignedShops(currentUserId);
            
            // Show modal
            var modal = new bootstrap.Modal(document.getElementById('userModal'));
            modal.show();
        });

        function loadAssignedShops(deliveryPersonId) {
            $('#assignedShopsContainer').html('<div class="alert alert-info">Loading assigned shops...</div>');
            
            $.ajax({
                url: 'delivery_management.php?get_assigned_shops=1&delivery_person_id=' + deliveryPersonId,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var container = $('#assignedShopsContainer');
                        container.empty();
                        
                        if (response.assignedShops.length > 0) {
                            response.assignedShops.forEach(function(shop) {
                                var shopHtml = `
                                    <div class="shop-item">
                                        <div class="shop-info">
                                            <div class="shop-name">${shop.shop_name}</div>
                                            <div class="shop-pincode">Pincode: ${shop.pincode}</div>
                                        </div>
                                        <div class="shop-actions">
                                            <form method="post" action="delivery_management.php" class="d-inline">
                                                <input type="hidden" name="assignment_id" value="${shop.assignment_id}">
                                                <input type="hidden" name="unassign_shop" value="1">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to unassign this shop?')">
                                                    <i class="fas fa-unlink"></i> Unassign
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                `;
                                container.append(shopHtml);
                            });
                        } else {
                            container.html('<div class="alert alert-warning">No shops assigned to this delivery person.</div>');
                        }
                    } else {
                        showToast(response.message || 'Error loading assigned shops', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showToast('Error loading assigned shops: ' + error, 'error');
                }
            });
        }

        $('#deleteUserBtn').on('click', function() {
            if (!currentUserId) {
                showToast('No personnel selected', 'error');
                return;
            }
            
            if (confirm('Are you sure you want to delete this delivery personnel? This action cannot be undone.')) {
                $.ajax({
                    url: 'includes/delete_delivery.php',
                    method: 'POST',
                    data: {
                        id: currentUserId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Remove the row from the table
                            table.row($('tr[data-id="' + currentUserId + '"]')).remove().draw();
                            
                            // Close the modal
                            $('#userModal').modal('hide');
                            
                            // Show success message
                            showToast('Delivery personnel deleted successfully', 'success');
                        } else {
                            showToast(response.message || 'Error deleting personnel', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        showToast('Error deleting personnel: ' + error, 'error');
                    }
                });
            }
        });

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