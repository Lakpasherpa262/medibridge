<?php
include 'session.php';
// Check if the signup was successful
if (isset($_SESSION['signup_success']) && $_SESSION['signup_success']) {
    echo "<script>alert('Sign up successful!');</script>";
    unset($_SESSION['signup_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge Admin Dashboard</title>
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
      --card-radius: 12px;
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
      justify-content: space-between;
      display:felx;
      flex-direction:column;
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
    .logo {
      width: 80px;
      height: 80px;
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
    }

    .main-content-expanded {
      margin-left: 80px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .toggle-sidebar {
      background: none;
      border: none;
      font-size: 1.25rem;
      color: var(--medium-text);
      cursor: pointer;
      transition: var(--transition);
    
    }

    .toggle-sidebar:hover {
      color: var(--primary-color);
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 0.75rem;
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
      font-weight: 600;
      font-size: 0.9rem;
    }

    .user-role {
      font-size: 0.75rem;
      color: var(--medium-text);
    }

    .summary-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }

    .summary-card {
      background: var(--white);
      border-radius: var(--card-radius);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
      border-left: 4px solid var(--primary-color);
    }

    .summary-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
    }

    .summary-card.users {
      border-left-color: var(--accent-color);
    }

    .summary-card.shops {
      border-left-color: var(--secondary-color);
    }

    .summary-card.orders {
      border-left-color: var(--warning-color);
    }

    .summary-card.deliveries {
      border-left-color: var(--info-color);
    }

    .card-title {
      font-size: 0.875rem;
      color: var(--medium-text);
      margin-bottom: 0.5rem;
      font-weight: 500;
    }

    .card-value {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--dark-text);
      margin-bottom: 0.5rem;
    }

    .card-change {
      font-size: 0.75rem;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .card-change.positive {
      color: var(--secondary-color);
    }

    .card-change.negative {
      color: var(--danger-color);
    }

    .card-icon {
      position: absolute;
      right: 1.5rem;
      top: 1.5rem;
      font-size: 1.5rem;
      color: rgba(37, 99, 235, 0.1);
    }

    .section-title {
      color: var(--dark-text);
      font-weight: 600;
      margin: 2rem 0 1.5rem;
      font-size: 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .section-title i {
      color: var(--primary-color);
    }

    .management-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .management-card {
      background: var(--white);
      border-radius: var(--card-radius);
      padding: 1.5rem;
      transition: var(--transition);
      box-shadow: var(--shadow-sm);
      position: relative;
      overflow: hidden;
    }

    .management-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
    }

    .management-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--primary-color);
    }

    .management-card.users::after {
      background: var(--accent-color);
    }

    .management-card.shops::after {
      background: var(--secondary-color);
    }

    .management-card.delivery::after {
      background: var(--info-color);
    }

    .management-card.inventory::after {
      background: var(--warning-color);
    }

    .management-card.orders::after {
      background: var(--danger-color);
    }

    .management-card.prescriptions::after {
      background: #8b5cf6;
    }

    .card-icon-bg {
      position: absolute;
      right: 1rem;
      top: 1rem;
      font-size: 4rem;
      opacity: 0.1;
      color: var(--primary-color);
    }

    .management-card.users .card-icon-bg {
      color: var(--accent-color);
    }

    .management-card.shops .card-icon-bg {
      color: var(--secondary-color);
    }

    .management-card.delivery .card-icon-bg {
      color: var(--info-color);
    }

    .management-card.inventory .card-icon-bg {
      color: var(--warning-color);
    }

    .management-card.orders .card-icon-bg {
      color: var(--danger-color);
    }

    .management-card.prescriptions .card-icon-bg {
      color: #8b5cf6;
    }

    .management-card h4 {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: var(--dark-text);
    }

    .management-card p {
      font-size: 0.875rem;
      color: var(--medium-text);
      margin-bottom: 1.5rem;
      min-height: 40px;
    }

    .btn {
      border-radius: 8px;
      font-weight: 500;
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
      transition: var(--transition);
    }

    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
    }

    .btn-success {
      background-color: var(--secondary-color);
      border-color: var(--secondary-color);
    }

    .btn-success:hover {
      background-color: #0d9f6e;
      border-color: #0d9f6e;
    }

    .btn-info {
      background-color: var(--info-color);
      border-color: var(--info-color);
    }

    .btn-info:hover {
      background-color: #0891b2;
      border-color: #0891b2;
    }

    .sidebar-divider {
      border-color: rgba(255, 255, 255, 0.1);
      margin: 1rem 0;
    }

    .notification-badge {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      background-color: var(--danger-color);
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.65rem;
      font-weight: 600;
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .toggle-sidebar-mobile {
        display: block !important;
      }
    }

    @media (max-width: 768px) {
      .management-grid {
        grid-template-columns: 1fr;
      }
      
      .summary-cards {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 576px) {
      .summary-cards {
        grid-template-columns: 1fr;
      }
    }

    .toggle-sidebar-mobile {
      display: none;
      background: none;
      border: none;
      font-size: 1.25rem;
      color: var(--dark-text);
      margin-right: 1rem;
    }

    .alert {
      border-radius: var(--card-radius);
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo">
      <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">
    </div>
    <div class="brand-name">MediBridge</div>
    <hr class="sidebar-divider">

    <div class="sidebar-header">Navigation</div>
    <nav class="nav flex-column">
      <a class="nav-link active" href="dashboard.php">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
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
    <!-- Header -->
    <div class="header">
      <div>
        <button class="toggle-sidebar-mobile" id="toggleSidebarMobile">
          <i class="fas fa-bars"></i>
        </button>
        <button class="toggle-sidebar" id="toggleSidebar">
          <i class="fas fa-chevron-left"></i>
        </button>
      </div>
      <div class="user-profile">
        <div class="user-avatar">AD</div>
        <div class="user-info">
          <span class="user-name">Admin User</span>
          <span class="user-role">System Administrator</span>
        </div>
      </div>
    </div>

    

    <!-- Quick Management Section -->
    <h2 class="section-title">
      <i class="fas fa-tachometer-alt"></i>
      Quick Management
    </h2>
    <div class="management-grid">
      <div class="management-card users">
        <i class="fas fa-users-cog card-icon-bg"></i>
        <h4>User Management</h4>
        <p>Manage all system users, roles, and permissions with detailed access controls.</p>
        <a href="User_Management.php" class="btn btn-primary">Manage Users</a>
      </div>
      <div class="management-card shops">
        <i class="fas fa-store card-icon-bg"></i>
        <h4>Shop Management</h4>
        <p>View and manage all registered pharmacies and their inventory details.</p>
        <a href="shop_management.php" class="btn btn-primary">Manage Shops</a>
      </div>
      <div class="management-card delivery">
        <i class="fas fa-truck card-icon-bg"></i>
        <h4>Delivery Personnel</h4>
        <p>Manage delivery staff, assignments, and track delivery status in real-time.</p>
        <a href="delivery_management.php" class="btn btn-primary">Manage Delivery</a>
      </div>
    </div>


    <!-- Quick Actions Section -->
    <h2 class="section-title">
      <i class="fas fa-bolt"></i>
      Quick Actions
    </h2>
    <div class="management-grid">
      <div class="management-card">
        <i class="fas fa-user-plus card-icon-bg"></i>
        <h4>Add New User</h4>
        <p>Register new administrators, shop owners, or delivery personnel.</p>
        <a href="registration.php" class="btn btn-success">Register User</a>
      </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle sidebar
    const toggleSidebar = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleSidebarMobile = document.getElementById('toggleSidebarMobile');

    toggleSidebar.addEventListener('click', () => {
      sidebar.classList.toggle('sidebar-collapsed');
      mainContent.classList.toggle('main-content-expanded');
      toggleSidebar.querySelector('i').classList.toggle('fa-chevron-left');
      toggleSidebar.querySelector('i').classList.toggle('fa-chevron-right');
    });

    toggleSidebarMobile.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 992 && !sidebar.contains(e.target) && e.target !== toggleSidebarMobile) {
        sidebar.classList.remove('active');
      }
    });

    // Show alerts
    function showAlert(type, message) {
      const alert = document.createElement('div');
      alert.className = `alert alert-${type} alert-dismissible fade show`;
      alert.role = 'alert';
      alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      document.querySelector('.header').after(alert);
      
      setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 150);
      }, 5000);
    }

    <?php if (isset($_SESSION['message'])): ?>
      showAlert('<?php echo $_SESSION['message_type']; ?>', '<?php echo $_SESSION['message']; ?>');
      <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
      ?>
    <?php endif; ?>
  </script>
</body>
</html>