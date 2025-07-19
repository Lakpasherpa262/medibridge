<?php
session_start();
include 'scripts/connect.php';

// Initialize doctor array with default values
$doctor = [
    'name' => 'Doctor',
    'email' => 'Not available',
    'image_path' => 'images/doctor-avatar.png',
    'specialization' => 'General Practitioner'
];
$error = '';
$unreadCount = 0;
$doctor_Id = null;

if (isset($_SESSION['id'])) {
    try {
        // First query: Get doctor_id from users table
        $stmt = $db->prepare("SELECT doctor_id FROM users WHERE id = ? AND role = '3'");
        $stmt->execute([$_SESSION['id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData && !empty($userData['doctor_id'])) {
            $doctor_Id = $userData['doctor_id'];
            
            // Second query: Get doctor details from doctors table
            $stmt = $db->prepare("SELECT name, email, image_path, specialization FROM doctors WHERE doctor_id = ?");
            $stmt->execute([$doctor_Id]);
            $doctorData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($doctorData) {
                // Update doctor array with fetched data
                $doctor['name'] = $doctorData['name'];
                $doctor['email'] = $doctorData['email'] ?? 'Not available';
                $doctor['specialization'] = $doctorData['specialization'];
                
                // Only update image_path if it exists in database
                if (!empty($doctorData['image_path'])) {
                    $doctor['image_path'] = $doctorData['image_path'];
                }
            } else {
                $error = "Doctor profile not found in database.";
            }
        } else {
            $error = "User is not registered as a doctor or doctor_id is missing.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
} else {
    $error = "User not logged in.";
}

if (!empty($error)) {
    error_log($error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dr. <?php echo htmlspecialchars($doctor['name']); ?> | MediBridge</title>
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
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: var(--white);
      margin: 0 auto 1.5rem;
      padding: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .logo-img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
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

    .main-content-expanded {
      margin-left: 80px;
      width: calc(100% - 80px);
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
      background: var(--warning-color);
    }

    .card-icon-bg {
      position: absolute;
      right: 1rem;
      top: 1rem;
      font-size: 4rem;
      opacity: 0.1;
      color: var(--warning-color);
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

    .sidebar-divider {
      border-color: rgba(255, 255, 255, 0.1);
      margin: 1rem 0;
    }

    .badge {
      font-size: 0.65rem;
      font-weight: 600;
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        width: 280px;
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
        width: 100%;
      }
      
      .toggle-sidebar-mobile {
        display: block !important;
      }
    }

    @media (max-width: 768px) {
      .management-grid {
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
    <!-- Doctor Profile Image -->
    <div class="logo">
      <img src="<?php echo htmlspecialchars($doctor['image_path']); ?>" alt="Doctor Profile" class="logo-img">
    </div>
    
    <!-- Doctor Name -->
    <div class="brand-name">Dr. <?php echo htmlspecialchars($doctor['name']); ?></div>
    
    <hr class="sidebar-divider">
    
    <!-- Navigation Menu -->
    <div class="sidebar-header">Navigation</div>
    <nav class="nav flex-column">
      <a class="nav-link active" href="doctor_dashboard.php">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </a>
      <a class="nav-link" href="appointment.php">
        <i class="fas fa-calendar-check"></i>
        <span>Appointments</span>
      </a>
    </nav>

    <!-- Logout Button -->
    <div class="mt-auto pt-3">
      <form action="includes/logout.php" method="post">
        <button type="submit" class="btn btn-outline-light w-100">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </button>
      </form>
    </div>
  </div>

  <!-- Main Content Area -->
  <div class="main-content" id="mainContent">
    <!-- Page Header -->
    <div class="header">
      <div class="d-flex align-items-center">
        <!-- Mobile Toggle Button -->
        <button class="toggle-sidebar-mobile d-lg-none" id="toggleSidebarMobile">
          <i class="fas fa-bars"></i>
        </button>
        
        <!-- Desktop Toggle Button -->
        <button class="toggle-sidebar d-none d-lg-block" id="toggleSidebar">
          <i class="fas fa-chevron-left"></i>
        </button>
        
        <!-- Page Title -->
        <h3 class="ms-3 mb-0">
          <i class="fas fa-user-md me-2"></i>Doctor Panel
        </h3>
      </div>
      
      <!-- User Profile Section -->
      <div class="user-profile">
        <div class="user-avatar"><?php echo substr($doctor['name'], 0, 1); ?></div>
        <div class="user-info">
          <span class="user-name">Dr. <?php echo htmlspecialchars($doctor['name']); ?></span>
        </div>
      </div>
    </div>

    <!-- Appointments Management Section -->
    <h2 class="section-title">
      <i class="fas fa-calendar-check"></i>
      Appointments Management
    </h2>
    <div class="management-grid">
      <div class="management-card">
        <i class="fas fa-calendar-check card-icon-bg"></i>
        <h4>Manage Appointments</h4>
        <p>View, schedule, and manage all your patient appointments in one place.</p>
        <a href="appointment.php" class="btn btn-primary">Manage Appointments</a>
      </div>
    </div>

  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Custom JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Sidebar Toggle Functionality
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');
      const toggleSidebar = document.getElementById('toggleSidebar');
      const toggleSidebarMobile = document.getElementById('toggleSidebarMobile');
      const icon = toggleSidebar.querySelector('i');
      
      // Desktop Toggle
      toggleSidebar.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-collapsed');
        mainContent.classList.toggle('main-content-expanded');
        
        if (sidebar.classList.contains('sidebar-collapsed')) {
          icon.classList.remove('fa-chevron-left');
          icon.classList.add('fa-chevron-right');
        } else {
          icon.classList.remove('fa-chevron-right');
          icon.classList.add('fa-chevron-left');
        }
      });

      // Mobile Toggle
      toggleSidebarMobile.addEventListener('click', function() {
        sidebar.classList.toggle('active');
      });

      // Close sidebar when clicking outside (mobile)
      document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && 
            !sidebar.contains(e.target) && 
            e.target !== toggleSidebarMobile) {
          sidebar.classList.remove('active');
        }
      });

      // Alert Notification Function
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

      // Display session messages if any
      <?php if (isset($_SESSION['message'])): ?>
        showAlert('<?php echo $_SESSION['message_type']; ?>', '<?php echo $_SESSION['message']; ?>');
        <?php 
          unset($_SESSION['message']);
          unset($_SESSION['message_type']);
        ?>
      <?php endif; ?>
    });
  </script>
</body>
</html>