<?php
include 'scripts/connect.php';
include 'session.php';
// Initialize user variable
$user = null;

// Get user details if logged in
if (isset($_SESSION['id'])) {
    $userStmt = $db->prepare("SELECT first_name FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
}
try {
    // Pagination setup
    $doctorsPerPage = 6; // 3 columns x 2 rows
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $doctorsPerPage;
    
    // Get total count of doctors
    $countQuery = $db->query("SELECT COUNT(*) as total FROM doctors");
    $totalDoctors = $countQuery->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalDoctors / $doctorsPerPage);

    // Fetch doctors with pagination
    $doctorsQuery = $db->query("
        SELECT d.doctor_id, d.name, d.email, d.phone, d.specialization, d.degree, d.license_no, 
               d.image_path, d.fee, d.shop_id,
               s.shop_name,
               sp.specialization_name,
               d.monday_start, d.monday_end,
               d.tuesday_start, d.tuesday_end,
               d.wednesday_start, d.wednesday_end,
               d.thursday_start, d.thursday_end,
               d.friday_start, d.friday_end,
               d.saturday_start, d.saturday_end,
               d.sunday_start, d.sunday_end
        FROM doctors d
        LEFT JOIN shopdetails s ON d.shop_id = s.id
        LEFT JOIN specializations sp ON d.specialization = sp.id
        GROUP BY d.doctor_id
        LIMIT $offset, $doctorsPerPage
    ");
    $doctors = $doctorsQuery->fetchAll(PDO::FETCH_ASSOC);

    if (!$doctors) {
        die("No doctors found in the database");
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consult Doctors Online | MediBridge</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

     <style>
    :root {
      --primary-color: #2563eb;
      --primary-light: #3b82f6;
      --primary-dark: #1d4ed8;
      --secondary-color: #10b981;
      --accent-color: #f59e0b;
      --dark-color: #1e293b;
      --light-bg: #f8fafc;
      --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    body {
      background-color: var(--light-bg);
      font-family: 'Poppins', sans-serif;
      color: var(--dark-color);
      line-height: 1.6;
    }
    
    .page-header {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      padding: 2.5rem;
      margin-bottom: 2.5rem;
      border-radius: 0.75rem;
      box-shadow: var(--card-shadow);
    }
    
    .page-header h1 {
      font-weight: 600;
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    
    .page-header p {
      opacity: 0.9;
      font-weight: 300;
      max-width: 700px;
    }
    
    .doctors-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }
    
    @media (max-width: 768px) {
      .doctors-container {
        grid-template-columns: 1fr;
      }
    }
    
    .doctor-card {
      background-color: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      box-shadow: var(--card-shadow);
      transition: all 0.3s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
      border: 1px solid #e2e8f0;
    }
    
    .doctor-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--card-hover-shadow);
      border-color: var(--primary-light);
    }
    
    .doctor-info {
      display: flex;
      margin-bottom: 1.25rem;
      align-items: flex-start;
    }
    
    .doctor-image-container {
      width: 5rem;
      height: 5rem;
      border-radius: 50%;
      overflow: hidden;
      margin-right: 1.25rem;
      border: 3px solid #e2e8f0;
      flex-shrink: 0;
    }
    
    .doctor-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .doctor-details {
      flex: 1;
    }
    
    .doctor-name {
      font-size: 1.125rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
      color: var(--dark-color);
    }
    
    .doctor-specialization {
      font-size: 0.875rem;
      color: var(--primary-color);
      font-weight: 500;
      margin-bottom: 0.25rem;
    }
    
    .doctor-qualification {
      font-size: 0.8125rem;
      color: #64748b;
      margin-bottom: 0;
    }
    
    .doctor-affiliation {
      font-size: 0.875rem;
      color: var(--dark-color);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
    }
    
    .doctor-affiliation i {
      margin-right: 0.5rem;
      color: var(--primary-color);
    }
    
    .doctor-footer {
      margin-top: auto;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      padding-top: 1rem;
      border-top: 1px solid #e2e8f0;
    }
    
    .consultation-fee {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--secondary-color);
      margin-bottom: 0.25rem;
    }
    
    .availability-status {
      display: flex;
      align-items: center;
      font-size: 0.8125rem;
    }
    
    .available-today {
      color: var(--secondary-color);
      font-weight: 500;
    }
    
    .not-available {
      color: #ef4444;
    }
    
    .btn-book {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 0.5rem 1.25rem;
      border-radius: 0.5rem;
      font-weight: 500;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
    }
    
    .btn-book:hover {
      background-color: var(--primary-dark);
      transform: translateY(-1px);
    }
    
    .btn-book i {
      margin-right: 0.5rem;
    }
    
    .pagination-container {
      display: flex;
      justify-content: center;
      margin-top: 2.5rem;
    }
    
    .page-item.active .page-link {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .page-link {
      color: var(--primary-color);
      padding: 0.5rem 0.75rem;
      border-radius: 0.375rem;
      margin: 0 0.25rem;
    }
    
    .page-link:hover {
      color: var(--primary-dark);
    }
    
    /* Modal Styles */
    .booking-modal .modal-header {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      border-bottom: none;
      padding: 1.5rem;
    }
    
    .booking-modal .modal-title {
      font-weight: 600;
    }
    
    .booking-modal .modal-body {
      padding: 2rem;
    }
    
    .modal-day-container {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
    }
    
    .modal-day {
      padding: 0.75rem 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: all 0.2s;
      text-align: center;
      border: 1px solid #e2e8f0;
      min-width: 6rem;
    }
    
    .modal-day:hover {
      background-color: #f1f5f9;
    }
    
    .modal-day.active {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }
    
    .modal-day small {
      display: block;
      font-size: 0.75rem;
      opacity: 0.8;
    }
    
    .time-slots-container {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
    }
    
    .time-slot {
      padding: 0.5rem 1rem;
      border: 1px solid #e2e8f0;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: all 0.2s;
      font-size: 0.875rem;
    }
    
    .time-slot:hover {
      border-color: var(--primary-color);
      color: var(--primary-color);
    }
    
    .time-slot.selected {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }
    
    .doctor-profile {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    
    .doctor-profile img {
      width: 8rem;
      height: 8rem;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid #e2e8f0;
      margin-bottom: 1rem;
    }
    
    .doctor-profile h5 {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    
    .doctor-profile p {
      color: #64748b;
      font-size: 0.875rem;
    }
    
    .fee-card {
      background-color: #f8fafc;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1.5rem;
      border: 1px solid #e2e8f0;
    }
    
    .fee-card h6 {
      font-size: 0.875rem;
      color: #64748b;
      margin-bottom: 0.5rem;
    }
    
    .fee-card h4 {
      color: var(--secondary-color);
      font-weight: 600;
    }
    
    .section-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--dark-color);
      position: relative;
      padding-bottom: 0.5rem;
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 3rem;
      height: 3px;
      background-color: var(--primary-color);
      border-radius: 3px;
    }
    
    .btn-confirm {
      background-color: var(--secondary-color);
      color: white;
      border: none;
      padding: 0.75rem;
      border-radius: 0.5rem;
      font-weight: 500;
      width: 100%;
      transition: all 0.2s;
    }
    
    .btn-confirm:hover {
      background-color: #0d9c6e;
    }
    
    .btn-confirm:disabled {
      background-color: #cbd5e1;
      cursor: not-allowed;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
      .booking-modal .modal-dialog {
        max-width: 90%;
      }
    }
    
    @media (max-width: 576px) {
      .page-header {
        padding: 1.5rem;
      }
      
      .doctor-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }
      
      .doctor-image-container {
        margin-right: 0;
        margin-bottom: 1rem;
      }
      
      .booking-modal .modal-body {
        padding: 1.5rem;
      }
    }
    
    /* Animation for cards */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .doctor-card {
      animation: fadeInUp 0.5s ease forwards;
      opacity: 0;
    }
    
    .doctor-card:nth-child(1) { animation-delay: 0.1s; }
    .doctor-card:nth-child(2) { animation-delay: 0.2s; }
    .doctor-card:nth-child(3) { animation-delay: 0.3s; }
    .doctor-card:nth-child(4) { animation-delay: 0.4s; }
    .doctor-card:nth-child(5) { animation-delay: 0.5s; }
    .doctor-card:nth-child(6) { animation-delay: 0.6s; }
</style>
</head>
<body>

<?php include 'templates/header.php'; ?>
<main class="container my-5">
  <div class="page-header">
    <h1>Find Your Doctor</h1>
    <p>Book appointments with our highly qualified specialists. </p>
  </div>

  <div class="doctors-container">
    <?php foreach ($doctors as $doctor): 
        $imagePath = !empty($doctor['image_path']) ? 'uploads/doctors/' . basename($doctor['image_path']) : 'uploads/doctors/default.png';
        
        // Check availability today
        $today = strtolower(date('l'));
        $availableToday = !empty($doctor[$today.'_start']);
    ?>
    <div class="doctor-card">
      <div class="doctor-info">
        <div class="doctor-image-container">
          <img src="<?php echo htmlspecialchars($imagePath); ?>" 
               alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>"
               class="doctor-image"
               onerror="this.src='uploads/doctors/default.png'">
        </div>
        <div class="doctor-details">
          <h3 class="doctor-name">Dr. <?php echo htmlspecialchars($doctor['name']); ?></h3>
          <p class="doctor-specialization"><?php echo htmlspecialchars($doctor['specialization_name'] ?? 'General Physician'); ?></p>
          <p class="doctor-qualification"><?php echo htmlspecialchars($doctor['degree']); ?></p>
        </div>
      </div>
      
      <p class="doctor-affiliation">
        <i class="fas fa-hospital"></i> <strong>Affiliated with:</strong> <?php echo htmlspecialchars($doctor['shop_name'] ?? 'MediBridge Clinic'); ?>
      </p>
      
      <div class="doctor-footer">
        <div>
          <span class="consultation-fee">₹<?php echo number_format($doctor['fee'], 2); ?></span>
          <div class="availability-status">
            <?php if ($availableToday): ?>
              <i class="fas fa-check-circle available-today"></i> <span class="available-today">Available Today</span>
            <?php else: ?>
              <i class="fas fa-times-circle not-available"></i> <span class="not-available">Not Available Today</span>
            <?php endif; ?>
          </div>
        </div>
        <a href="#" class="btn-book" data-bs-toggle="modal" data-bs-target="#bookingModal<?php echo $doctor['doctor_id']; ?>">
          Book Now
        </a>
      </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal<?php echo $doctor['doctor_id']; ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Book Appointment with Dr. <?php echo htmlspecialchars($doctor['name']); ?></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4">
                <div class="text-center mb-4">
                  <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                       alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>"
                       class="img-fluid rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                  <h5>Dr. <?php echo htmlspecialchars($doctor['name']); ?></h5>
                  <p class="text-muted"><?php echo htmlspecialchars($doctor['specialization_name'] ?? 'General Physician'); ?></p>
                </div>
                <div class="card mb-3">
                  <div class="card-body">
                    <h6>Consultation Fee</h6>
                    <h4 class="text-success">₹<?php echo number_format($doctor['fee'], 2); ?></h4>
                  </div>
                </div>
              </div>
              <div class="col-md-8">
                <h5>Select Date & Time</h5>
                
                <div class="mb-4">
                  <h6>Available Days</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <?php
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $todayIndex = date('N') - 1;
                    
                    for ($i = 0; $i < 7; $i++) {
                        $dayIndex = ($todayIndex + $i) % 7;
                        $dayName = $days[$dayIndex];
                        $date = date('Y-m-d', strtotime("+$i days"));
                        $startTime = $doctor[strtolower($dayName).'_start'];
                        $endTime = $doctor[strtolower($dayName).'_end'];
                        
                        if (!empty($startTime)) {
                            $active = $i == 0 ? 'active' : '';
                            echo "<div class='modal-day $active' data-day='$dayName' data-date='$date'>
                                    $dayName<br><small>" . date('M j', strtotime($date)) . "</small>
                                  </div>";
                        }
                    }
                    ?>
                  </div>
                </div>
                
                <div class="mb-4">
                  <h6>Available Time Slots</h6>
                  <div class="time-slots-container">
                    <?php
                    // Generate time slots for the first available day
                    $firstDay = '';
                    foreach ($days as $day) {
                        if (!empty($doctor[strtolower($day).'_start'])) {
                            $firstDay = $day;
                            break;
                        }
                    }
                    
                    if ($firstDay) {
                        $start = strtotime($doctor[strtolower($firstDay).'_start']);
                        $end = strtotime($doctor[strtolower($firstDay).'_end']);
                        $interval = 30 * 60; // 30 minutes
                        
                        for ($time = $start; $time < $end; $time += $interval) {
                            echo "<div class='time-slot'>" . date('h:i A', $time) . "</div>";
                        }
                    }
                    ?>
                  </div>
                </div>
                
                <button class="btn btn-primary w-100" id="confirmBtn<?php echo $doctor['doctor_id']; ?>" disabled>
                  Confirm Appointment
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination-container">
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
          <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
  <?php endif; ?>
</main>

<?php include 'templates/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
  // Time slot selection
  $(document).on('click', '.time-slot', function() {
    $(this).siblings().removeClass('selected');
    $(this).addClass('selected');
    $(this).closest('.modal-content').find('button[id^="confirmBtn"]').prop('disabled', false);
  });
  
  // Day selection
  $(document).on('click', '.modal-day', function() {
    const day = $(this).data('day');
    const date = $(this).data('date');
    const modal = $(this).closest('.modal-content');
    
    // Update active day
    modal.find('.modal-day').removeClass('active');
    $(this).addClass('active');
    
    // Here you would typically fetch available slots for this day via AJAX
    // For demo, we'll just show some sample times
    const times = ['9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '2:00 PM', '2:30 PM', '3:00 PM'];
    let slotsHtml = '';
    times.forEach(time => {
      slotsHtml += `<div class="time-slot">${time}</div>`;
    });
    modal.find('.time-slots-container').html(slotsHtml);
    modal.find('button[id^="confirmBtn"]').prop('disabled', true);
  });
});
 // When confirm button is clicked
 $(document).on('click', 'button[id^="confirmBtn"]', function() {
    const modal = $(this).closest('.modal-content');
    const selectedDay = modal.find('.modal-day.active');
    const selectedTime = modal.find('.time-slot.selected');
    
    if (!selectedDay.length || !selectedTime.length) {
      alert('Please select a date and time');
      return;
    }
    
    const form = $('<form>').attr({
      method: 'post',
      action: 'payment.php'
    }).css('display', 'none');
    
    form.append($('<input>').attr({
      type: 'hidden',
      name: 'doctor_id',
      value: modal.closest('.modal').attr('id').replace('bookingModal', '')
    }));
    
    form.append($('<input>').attr({
      type: 'hidden',
      name: 'appointment_date',
      value: selectedDay.data('date')
    }));
    
    form.append($('<input>').attr({
      type: 'hidden',
      name: 'appointment_time',
      value: selectedTime.text()
    }));
    
    $('body').append(form);
    form.submit();
  });
  </script>
</body>
</html>
<?php
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>