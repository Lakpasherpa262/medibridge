<?php
include 'session.php';
include 'scripts/connect.php';

$shopOwner_id = $_SESSION['id'];

$stmt1 = $db->prepare("SELECT id, shop_name, shop_image FROM shopdetails WHERE shopOwner_id = :shopOwner_id");
$stmt1->bindValue(':shopOwner_id', $shopOwner_id, PDO::PARAM_STR);
$stmt1->execute();
$count = $stmt1->rowCount();
$rows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge - My Shops</title>
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

    /* Sidebar Styles */
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

    .logo {
      width: 60px;
      height: 60px;
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

    /* Main Content */
    .main-content {
      margin-left: 280px;
      padding: 2rem;
      flex-grow: 1;
      transition: var(--transition);
    }

    .main-content-expanded {
      margin-left: 80px;
    }

    /* Header Styles */
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

    /* Page Header */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .page-title {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--dark-text);
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .page-title i {
      color: var(--primary-color);
    }
    
    .add-shop-btn {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: var(--card-radius);
      font-weight: 500;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: var(--transition);
      text-decoration: none;
    }

    /* Shop Grid */
    .shop-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .shop-card {
      background: var(--white);
      border-radius: var(--card-radius);
      padding: 1.5rem;
      transition: var(--transition);
      box-shadow: var(--shadow-sm);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }

    .shop-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
    }

    .shop-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: var(--primary-color);
    }

    .shop-image-container {
      width: 100%;
      height: 180px;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f5f7fa;
    }

    .shop-image {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    .shop-name {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark-text);
    }

    .shop-actions {
      width: 100%;
      display: flex;
      gap: 0.75rem;
      margin-top: 1.5rem;
    }

    .btn {
      border-radius: 8px;
      font-weight: 500;
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
      transition: var(--transition);
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
    }

    .btn-outline-primary {
      color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-outline-primary:hover {
      background-color: var(--primary-color);
      color: white;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem;
      background: white;
      border-radius: var(--card-radius);
      box-shadow: var(--shadow-sm);
      margin-top: 2rem;
    }

    .empty-icon {
      font-size: 4rem;
      color: var(--light-text);
      margin-bottom: 1.5rem;
      opacity: 0.5;
    }

    .empty-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
      color: var(--dark-text);
    }

    .empty-text {
      font-size: 1rem;
      color: var(--medium-text);
      margin-bottom: 1.5rem;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }

    /* Responsive Design */
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
      .shop-container {
        grid-template-columns: 1fr 1fr;
      }
      
      .shop-actions {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
      }
    }

    @media (max-width: 576px) {
      .shop-container {
        grid-template-columns: 1fr;
      }
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
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
      <a class="nav-link active" href="shops.php">
        <i class="fas fa-store"></i>
        <span>My Shops</span>
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
      </div>>
    </div>

      <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">
        <i class="fas fa-store"></i> My Shops
      </h1>
      <a href="shop_registration.php" class="add-shop-btn">
        <i class="fas fa-plus"></i> Add New Shop
      </a>
    </div>

    <?php if ($count == 0) : ?>
      <!-- Empty State -->
      <div class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-store-slash"></i>
        </div>
        <h3 class="empty-title">No Shops Found</h3>
        <p class="empty-text">You haven't registered any shops yet. Click the button above to add your first shop and start managing your pharmacy business.</p>
        <a href="shop_registration.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> Register Your First Shop
        </a>
      </div>
    <?php else : ?>
      <!-- Shops Grid -->
      <div class="shop-container">
        <?php foreach ($rows as $row): ?>
          <div class="shop-card">
            <div class="shop-image-container">
              <?php if (!empty($row['shop_image'])): ?>
                <img src="includes/uploads/<?php echo htmlspecialchars($row['shop_image']); ?>" 
                     alt="<?php echo htmlspecialchars($row['shop_name']); ?>" 
                     class="shop-image">
              <?php else: ?>
                <i class="fas fa-store shop-image" style="font-size: 3rem; color: var(--light-text); opacity: 0.5;"></i>
              <?php endif; ?>
            </div>
            <h3 class="shop-name"><?php echo htmlspecialchars($row['shop_name']); ?></h3>
            <div class="shop-actions">
              <a href="shop.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                <i class="fas fa-eye"></i> View Shop
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
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

    // Animation for shop cards
    document.addEventListener('DOMContentLoaded', function() {
      const shopCards = document.querySelectorAll('.shop-card');
      shopCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, index * 100);
      });
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