<?php
include 'scripts/connect.php';
include 'session.php';

// Verify shop owner and get shop ID
$shop_id = $_SESSION['shop_id'] ?? null;
$shopOwner_id = $_SESSION['id'] ?? null;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_notification'])) {
        $notification_id = (int)$_POST['delete_notification'];
        $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND pharmacy_id = ?");
        $stmt->execute([$notification_id, $shop_id]);
        $_SESSION['message'] = 'Notification deleted successfully';
        $_SESSION['message_type'] = 'success';
    } elseif (isset($_POST['clear_all'])) {
        $stmt = $db->prepare("DELETE FROM notifications WHERE pharmacy_id = ?");
        $stmt->execute([$shop_id]);
        $_SESSION['message'] = 'All notifications cleared';
        $_SESSION['message_type'] = 'success';
    }
    header("Location: notification_shop.php");
    exit();
}

// Mark notifications as read when page loads
if ($shop_id && $shopOwner_id) {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE pharmacy_id = ? AND is_read = 0");
    $stmt->execute([$shop_id]);
}

// Get all notifications for this pharmacy
$notifications = [];
if ($shop_id) {
    $stmt = $db->prepare("
        SELECT 
            n.*, 
            u.first_name, 
            u.last_name, 
            u.email, 
            p.status as prescription_status,
            o.status as order_status
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        LEFT JOIN prescriptions p ON n.prescription_id = p.id
        LEFT JOIN orders o ON n.order_id = o.id
        WHERE n.pharmacy_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$shop_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get unread count for the badge
$unreadCount = 0;
if ($shop_id) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE pharmacy_id = ? AND is_read = 0");
    $stmt->execute([$shop_id]);
    $unreadCount = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications | MediBridge</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #2563eb;
      --primary-light: #3b82f6;
      --primary-dark: #1d4ed8;
      --secondary-color: #10b981;
      --accent-color: #6366f1;
      --danger-color: #ef4444;
      --warning-color: #f59e0b;
      --info-color: #06b6d4;
      --light-bg: #f8fafc;
      --dark-text: #1e293b;
      --medium-text: #64748b;
      --light-text: #94a3b8;
      --white: #ffffff;
      --sidebar-bg: #1e293b;
      --sidebar-text: #e2e8f0;
      --sidebar-active: #334155;
      --card-radius: 8px;
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
      --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--light-bg);
      color: var(--dark-text);
      min-height: 100vh;
      display: flex;
      line-height: 1.6;
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
    }

    .sidebar-collapsed {
      width: 80px;
    }

    .sidebar-collapsed .nav-link span,
    .sidebar-collapsed .brand-name,
    .sidebar-collapsed .sidebar-header {
      display: none;
    }

    .sidebar-collapsed .nav-link {
      justify-content: center;
      padding: 0.75rem 0;
    }

    .sidebar-collapsed .logo {
      width: 50px;
      height: 50px;
      margin: 0 auto 1rem;
    }

    .logo {
      width: 80%;
      height: 80%;
      border-radius: 12px;
      background: var(--white);
      margin: 0 auto 1.5rem;
      padding: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .logo-img {
      max-width: 100%;
      max-height: 100%;
      border-radius: 8px;
      object-fit: cover;
    }

    .brand-name {
      font-size: 1.5rem;
      font-weight: 700;
      text-align: center;
      color: var(--white);
      margin-bottom: 2rem;
      transition: var(--transition);
    }

    .sidebar-header {
      color: var(--light-text);
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 1.5rem 0 0.75rem;
      padding-left: 0.75rem;
      transition: var(--transition);
    }

    .sidebar .logout-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0.75rem 1rem;
      color: white;
      background-color: #2c2d3f;
      border: none;
      cursor: pointer;
      margin: 1rem;
      border-radius: 0.5rem;
      font-size: 1rem;
      transition: background 0.3s ease;
    }

    .sidebar .logout-btn:hover {
      background-color: #3d3f57;
    }

    .sidebar .logout-btn i {
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
    }

    .nav-link {
      color: var(--sidebar-text) !important;
      padding: 0.75rem;
      margin: 0.25rem 0;
      border-radius: 8px;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-weight: 500;
      position: relative;
    }

    .nav-link i {
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
    }

    .nav-link:hover, .nav-link.active {
      background: var(--sidebar-active);
      color: var(--white) !important;
    }

    .nav-link.active {
      font-weight: 600;
    }

    .main-content {
      margin-left: 280px;
      padding: 2rem;
      flex-grow: 1;
      transition: var(--transition);
      width: calc(100% - 280px);
    }

    .notification-container {
      max-width: 1200px;
      margin: 0 auto;
      background: var(--white);
      border-radius: var(--card-radius);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }

    .notification-header {
      padding: 1.5rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .notification-list {
      max-height: calc(100vh - 200px);
      overflow-y: auto;
    }

    .notification-item {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      display: flex;
      align-items: flex-start;
      cursor: pointer;
      transition: var(--transition);
      position: relative;
    }

    .notification-item.unread {
      background-color: rgba(59, 130, 246, 0.05);
      border-left: 3px solid var(--primary-color);
    }

    .notification-item:hover {
      background-color: rgba(0, 0, 0, 0.02);
    }

    .notification-avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background-color: var(--primary-color);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      margin-right: 1rem;
      flex-shrink: 0;
    }

    .notification-content {
      flex-grow: 1;
    }

    .notification-sender {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .notification-subject {
      font-weight: 500;
      margin-bottom: 0.25rem;
      display: flex;
      align-items: center;
    }

    .notification-preview {
      color: var(--medium-text);
      font-size: 0.9rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .notification-meta {
      display: flex;
      justify-content: space-between;
      margin-top: 0.5rem;
      font-size: 0.85rem;
      color: var(--medium-text);
    }

    .notification-time {
      white-space: nowrap;
      margin-left: 1rem;
    }

    .notification-actions {
      display: flex;
      gap: 0.5rem;
      margin-left: 1rem;
    }

    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 500;
      margin-left: 0.5rem;
    }

    .status-pending {
      background-color: rgba(245, 158, 11, 0.1);
      color: var(--warning-color);
    }

    .status-processing {
      background-color: rgba(6, 182, 212, 0.1);
      color: var(--info-color);
    }

    .status-completed {
      background-color: rgba(16, 185, 129, 0.1);
      color: var(--secondary-color);
    }

    .status-rejected {
      background-color: rgba(239, 68, 68, 0.1);
      color: var(--danger-color);
    }

    .empty-state {
      text-align: center;
      padding: 3rem;
    }

    .empty-state i {
      font-size: 3rem;
      color: var(--light-text);
      margin-bottom: 1rem;
    }

    .back-btn {
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--sidebar-text);
      padding: 0.5rem 1rem;
      border-radius: var(--card-radius);
      transition: var(--transition);
    }

    .back-btn:hover {
      background-color: var(--sidebar-active);
      color: var(--white);
      text-decoration: none;
    }

    .alert {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      animation: slideInRight 0.3s, fadeOut 0.5s 4.5s forwards;
    }

    @keyframes slideInRight {
      from { transform: translateX(100%); }
      to { transform: translateX(0); }
    }

    @keyframes fadeOut {
      to { opacity: 0; }
    }

    .delete-form {
      display: inline;
    }
    
    .notification-type-badge {
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      margin-right: 0.5rem;
    }
    
    .type-prescription {
      background-color: rgba(37, 99, 235, 0.1);
      color: var(--primary-color);
    }
    
    .type-order {
      background-color: rgba(16, 185, 129, 0.1);
      color: var(--secondary-color);
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo">
      <?php if (!empty($shop['shop_image'])): ?>
        <img src="includes/uploads/<?php echo htmlspecialchars($shop['shop_image']); ?>" alt="Shop Logo" class="logo-img">
      <?php else: ?>
        <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">
      <?php endif; ?>
    </div>
    <div class="brand-name"><?php echo htmlspecialchars($shop['shop_name'] ?? 'Shop Dashboard'); ?></div>
    <hr class="sidebar-divider">
    
    <a href="shop.php" class="back-btn">
      <i class="fas fa-arrow-left"></i>
      <span>Back to Dashboard</span>
    </a>
    
    <div class="sidebar-header">Navigation</div>
    <nav class="nav flex-column">
      <a class="nav-link" href="shop.php">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </a>
      <a class="nav-link active" href="notification_shop.php">
        <i class="fas fa-bell"></i>
        <span>Notifications</span>
        <?php if ($unreadCount > 0): ?>
          <span class="badge bg-danger rounded-pill ms-1"><?= $unreadCount ?></span>
        <?php endif; ?>
      </a>
      <a class="nav-link" href="shop_settings.php">
        <i class="fas fa-cog"></i>
        <span>Shop Settings</span>
      </a>
    </nav>

    <div class="mt-auto pt-3">
      <form action="includes/logout.php" method="post">
        <button type="submit" class="btn btn-outline-light w-100">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </button>
      </form>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">
    <div class="notification-container">
      <div class="notification-header">
        <h3 class="mb-0">
          <i class="fas fa-bell me-2"></i>Notifications
          <?php if ($unreadCount > 0): ?>
            <span class="badge bg-danger rounded-pill ms-2"><?= $unreadCount ?> unread</span>
          <?php endif; ?>
        </h3>
        <div class="d-flex align-items-center">
          <button class="btn btn-sm btn-outline-secondary me-2" id="refreshBtn">
            <i class="fas fa-sync-alt"></i> Refresh
          </button>
          <form method="post" class="delete-form">
            <button type="submit" name="clear_all" class="btn btn-sm btn-outline-danger" 
                    onclick="return confirm('Are you sure you want to clear all notifications?')">
              <i class="fas fa-trash-alt"></i> Clear All
            </button>
          </form>
        </div>
      </div>

      <div class="notification-list">
        <?php if (empty($notifications)): ?>
          <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h4>No notifications yet</h4>
            <p class="text-muted">You'll see notifications here when you receive new orders or prescriptions</p>
          </div>
        <?php else: ?>
          <?php foreach ($notifications as $notification): ?>
            <div class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
              <div class="notification-avatar">
                <?= strtoupper(substr($notification['first_name'] ?? 'U', 0, 1)) ?>
              </div>
              <div class="notification-content">
                <div class="notification-sender">
                  <?= htmlspecialchars($notification['first_name'] ?? 'Unknown') ?>
                  <?= htmlspecialchars($notification['last_name'] ?? '') ?>
                  <span class="text-muted ms-2"><?= htmlspecialchars($notification['email'] ?? '') ?></span>
                </div>
                <div class="notification-subject">
                  <?php if ($notification['prescription_id']): ?>
                    <span class="notification-type-badge type-prescription">Prescription</span>
                    New prescription
                    <?php if ($notification['prescription_status']): ?>
                      <span class="status-badge status-<?= $notification['prescription_status'] ?>">
                        <?= ucfirst($notification['prescription_status']) ?>
                      </span>
                    <?php endif; ?>
                  <?php elseif ($notification['order_id']): ?>
                    <span class="notification-type-badge type-order">Order</span>
                    New order #<?= $notification['order_id'] ?>
                    <?php if ($notification['order_status']): ?>
                      <span class="status-badge status-<?= $notification['order_status'] ?>">
                        <?= ucfirst($notification['order_status']) ?>
                      </span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="notification-type-badge">System</span>
                    <?= htmlspecialchars($notification['message']) ?>
                  <?php endif; ?>
                </div>
                <div class="notification-preview">
                  <?= htmlspecialchars($notification['message']) ?>
                </div>
                <div class="notification-meta">
                  <div class="notification-time">
                    <?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?>
                  </div>
                </div>
              </div>
              <div class="notification-actions">
                <?php if ($notification['prescription_id']): ?>
                  <a href="prescription_management.php?view=<?= $notification['prescription_id'] ?>" 
                     class="btn btn-sm btn-outline-primary" 
                     title="View Prescription">
                    <i class="fas fa-eye"></i>
                  </a>
                <?php elseif ($notification['order_id']): ?>
                  <a href="order_management.php?view=<?= $notification['order_id'] ?>" 
                     class="btn btn-sm btn-outline-primary" 
                     title="View Order">
                    <i class="fas fa-eye"></i>
                  </a>
                <?php endif; ?>
                <form method="post" class="delete-form">
                  <input type="hidden" name="delete_notification" value="<?= $notification['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-secondary" title="Delete"
                          onclick="return confirm('Are you sure you want to delete this notification?')">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
      <?= $_SESSION['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
      unset($_SESSION['message']);
      unset($_SESSION['message_type']);
    ?>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Make notification items clickable
      document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
          // Don't trigger if clicking on action buttons
          if (!e.target.closest('.notification-actions')) {
            const viewBtn = this.querySelector('a[href*="prescription_management"], a[href*="order_management"]');
            if (viewBtn) {
              window.location.href = viewBtn.href;
            }
          }
        });
      });

      // Refresh button
      document.getElementById('refreshBtn').addEventListener('click', function() {
        window.location.reload();
      });

      // Auto-dismiss alerts after 5 seconds
      const alert = document.querySelector('.alert');
      if (alert) {
        setTimeout(() => {
          alert.style.display = 'none';
        }, 5000);
      }
    });
  </script>
</body>
</html>