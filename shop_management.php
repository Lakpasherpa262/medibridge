<?php
include 'scripts/connect.php';
session_start();

// Verify admin access
if (!isset($_SESSION['id'])) {
    die("Access denied. Please login first.");
}

// Fetch shops with basic info
try {
    $stmt = $db->prepare("SELECT s.id, s.shop_name, s.email, s.shop_number, s.address, 
                         s.district, s.state, s.pincode, s.trade_license, 
                         s.retail_drug_license, s.shop_image, s.owner_signature,
                         CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) AS owner_name
                         FROM shopdetails s
                         JOIN users u ON s.shopOwner_id = u.id");
    $stmt->execute();
    $shops = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching shops: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBridge - Shop Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: #f5f7fa;
    color: var(--dark-color);
    line-height: 1.6;
    min-height: 100vh;
}

.sidebar {
    width: 280px;
    background: var(--sidebar-bg);
    color: var(--sidebar-text);
    height: 100vh;
    position: fixed;
    padding: 1.5rem 1rem;
    box-shadow: var(--shadow-md);
    z-index: 1000;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.sidebar-collapsed {
    width: 80px;
    overflow: hidden;
}

.sidebar-collapsed .brand-name,
.sidebar-collapsed .nav-link span,
.sidebar-collapsed .sidebar-header {
    display: none;
}

.sidebar-collapsed .nav-link {
    justify-content: center;
}

.sidebar-collapsed .logo {
    width: 50px;
    height: 50px;
}

.sidebar-header {
    color: var(--sidebar-text);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 1.5rem 0 0.75rem;
    padding-left: 0.75rem;
    transition: var(--transition);
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
    color: white;
    text-align: center;
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
    background: #334155;
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

.header h1, .header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
}

.header h1 i, .header h2 i {
    margin-right: 10px;
    color: #1e293b;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 500;
}

.user-role {
    font-size: 0.8rem;
    color: var(--secondary-color);
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
    background-color: rgba(43, 108, 128, 0.05);
    transform: translateY(-2px);
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
    border-top: 1px solid #f0f0f0;
}

.clickable-row {
    cursor: pointer;
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

.user-info, .shop-info {
    margin-bottom: 20px;
}

.user-details h4, .shop-details h4 {
    margin: 0;
    color: var(--primary-color);
    font-weight: 600;
}

.user-details p, .shop-details p {
    margin: 5px 0 0;
    color: var(--secondary-color);
}

.user-image-container, .shop-image-container {
    margin-top: 20px;
    text-align: center;
}

.user-image, .shop-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #eee;
    margin-bottom: 15px;
}

.document-image {
    max-width: 100%;
    max-height: 400px;
    border-radius: var(--card-radius);
    box-shadow: var(--shadow-sm);
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

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: #0f172a;
    border-color: #0f172a;
}

.btn-danger {
    background-color: var(--danger-color);
    border-color: var(--danger-color);
}

.btn-danger:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
}

.btn-secondary {
    background-color: var(--secondary-color);
    border-color: var(--secondary-color);
}

.btn-secondary:hover {
    background-color: #5c636a;
    border-color: #565e64;
}

.btn-outline-light {
    color: var(--sidebar-text);
    border-color: var(--sidebar-text);
}

.btn-outline-light:hover {
    background-color: rgba(255,255,255,0.1);
    color: white;
}

/* Toast notification */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1100;
}

.toast {
    background-color: var(--primary-color);
    color: white;
    border-radius: var(--card-radius);
}

/* Responsive adjustments */
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

@media (max-width: 576px) {
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .user-profile {
        margin-top: 10px;
    }
}
    </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">
      <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">
    </div>
    <div class="brand-name">MediBridge</div>

   <div class="sidebar-header">Navigation</div>
    <nav class="nav flex-column">
        <a href="admin_dashboard.php" class="nav-item">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>        
      <a class="nav-link active" href="shop_management.php">
        <i class="fas fa-store"></i>
        <span>Shop Management</span>
      </a>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <h2 class="mb-0">
        <i class="fas fa-store text-primary me-2"></i>
        Shop Management
      </h2>
      <div class="user-profile">
        <div class="user-avatar">AD</div>
        <div class="user-info">
          <span class="user-name">Admin User</span>
          <span class="user-role">System Administrator</span>
        </div>
      </div>
    </div>

    <!-- Shops Table -->
    <div class="card">
      <div class="card-header">
        <h5><i class="fas fa-list"></i> Registered Shops</h5>
      </div>
      <div class="card-body">
        <table id="shopsTable" class="table table-hover" style="width:100%">
          <thead>
            <tr>
              <th class="d-none">ID</th>
              <th>Shop Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Owner</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($shops as $shop): ?>
            <tr class="clickable-row" 
                data-id="<?= $shop['id'] ?>"
                data-shop-name="<?= htmlspecialchars($shop['shop_name']) ?>"
                data-email="<?= htmlspecialchars($shop['email']) ?>"
                data-phone="<?= htmlspecialchars($shop['shop_number']) ?>"
                data-owner-name="<?= htmlspecialchars($shop['owner_name']) ?>">
              <td class="d-none"><?= $shop['id'] ?></td>
              <td><?= htmlspecialchars($shop['shop_name']) ?></td>
              <td><?= htmlspecialchars($shop['email']) ?></td>
              <td><?= htmlspecialchars($shop['shop_number']) ?></td>
              <td><?= htmlspecialchars($shop['owner_name']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Shop Details Modal -->
  <div class="modal fade" id="shopModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-store"></i> Shop Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Shop Image at the top -->
          <div class="shop-image-container">
            <img id="shopImage" src="" alt="Shop Image" class="shop-image">
          </div>
          
          <!-- Basic Information Section -->
          <div class="detail-section">
            <h5><i class="fas fa-info-circle"></i> Basic Information</h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">Shop Name</div>
                  <div class="detail-value" id="modalShopName"></div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">Owner</div>
                  <div class="detail-value" id="modalShopOwner"></div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">Email</div>
                  <div class="detail-value" id="modalShopEmail"></div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">Phone Number</div>
                  <div class="detail-value" id="modalShopPhone"></div>
                </div>
              </div>
              <div class="col-12">
                <div class="detail-item">
                  <div class="detail-label">Owner Signature</div>
                  <img id="ownerSignatureImg" src="" alt="Owner Signature" class="img-fluid mt-2" style="max-height: 100px;">
                </div>
              </div>
            </div>
          </div>
          
          <!-- Address Information Section -->
          <div class="detail-section">
            <h5><i class="fas fa-map-marker-alt"></i> Address Information</h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">Address</div>
                  <div class="detail-value" id="modalShopAddress"></div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">State</div>
                  <div class="detail-value" id="modalShopState"></div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">District</div>
                  <div class="detail-value" id="modalShopDistrict"></div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">Pincode</div>
                  <div class="detail-value" id="modalShopPincode"></div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- License Information Section -->
          <div class="detail-section">
            <h5><i class="fas fa-file-contract"></i> License Information</h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">Trade License</div>
                  <div class="detail-value" id="modalTradeLicense"></div>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="detail-item">
                  <div class="detail-label">Retail Drug License</div>
                  <div class="detail-value" id="modalRetailLicense"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="deleteShopBtn">
            <i class="fas fa-trash-alt"></i> Delete Shop
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Document View Modal -->
  <div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="documentModalTitle">Document</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img id="documentImage" src="" alt="Document" class="document-image img-fluid">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
      // Initialize DataTable
      var table = $('#shopsTable').DataTable({
          responsive: true,
          dom: '<"top"f>rt<"bottom"lip><"clear">',
          language: {
              search: "_INPUT_",
              searchPlaceholder: "Search shops...",
          },
          columnDefs: [
              { targets: [0], visible: false }
          ],
          order: [[1, 'asc']]
      });

      var currentShopId = null;
      
      // Handle row click to show shop details
      $('#shopsTable tbody').on('click', 'tr', function() {
          var row = $(this);
          currentShopId = row.data('id');
          
          // Set basic shop info from data attributes
          $('#modalShopName').text(row.data('shop-name'));
          $('#modalShopOwner').text(row.data('owner-name'));
          $('#modalShopEmail').text(row.data('email'));
          $('#modalShopPhone').text(row.data('phone'));
          
          // Fetch additional details via AJAX
          $.ajax({
              url: 'includes/get_shop_details.php',
              method: 'POST',
              data: { id: currentShopId },
              dataType: 'json',
             success: function(response) {
    if (response.success) {
        const shop = response.data;
        console.log('Shop data:', shop); // Debugging
        
        // Set shop image with error handling
        var shopImage = $('#shopImage');
        if (shop.shop_image) {
            shopImage.attr('src', 'includes/uploads/' + shop.shop_image)
                .on('error', function() {
                    shopImage.attr('src', 'images/default-shop.png');
                });
        } else {
            shopImage.attr('src', 'images/default-shop.png');
        }
        
        // Set owner signature with error handling
        var signatureImg = $('#ownerSignatureImg');
        if (shop.owner_signature) {
            signatureImg.attr('src', 'includes/uploads/' + shop.owner_signature)
                .on('error', function() {
                    signatureImg.hide();
                });
        } else {
            signatureImg.hide();
        }     
                      // Set address info
                      $('#modalShopAddress').text(shop.address || 'Not provided');
                      $('#modalShopState').text(shop.state || 'Not provided');
                      $('#modalShopDistrict').text(shop.district || 'Not provided');
                      $('#modalShopPincode').text(shop.pincode || 'Not provided');
                      
                      // Set license info
                      $('#modalTradeLicense').text(shop.trade_license || 'Not provided');
                      $('#modalRetailLicense').text(shop.retail_drug_license || 'Not provided');
                      
                  } else {
                      alert(response.message || 'Error loading shop details');
                  }
              },
              error: function(xhr, status, error) {
                  console.error('AJAX Error:', status, error);
                  alert('Error loading shop details');
              }
          });
          
          // Show modal
          var modal = new bootstrap.Modal(document.getElementById('shopModal'));
          modal.show();
      });

      // Handle delete shop button
      $('#deleteShopBtn').on('click', function() {
          if (!currentShopId) {
              alert('No shop selected');
              return;
          }
          
          if (confirm('Are you sure you want to delete this shop? This action cannot be undone.')) {
              $.ajax({
                  url: 'includes/delete_shop.php',
                  method: 'POST',
                  data: {
                      id: currentShopId
                  },
                  dataType: 'json',
                  success: function(response) {
                      if (response.success) {
                          // Remove the row from the table
                          table.row($('tr[data-id="' + currentShopId + '"]')).remove().draw();
                          
                          // Close the modal
                          $('#shopModal').modal('hide');
                          
                          // Show success message
                          alert('Shop deleted successfully');
                      } else {
                          alert(response.message || 'Error deleting shop');
                      }
                  },
                  error: function(xhr, status, error) {
                      console.error('AJAX Error:', status, error);
                      alert('Error deleting shop: ' + error);
                  }
              });
          }
      });
  });
  </script>
</body>
</html>