<?php
include 'connect.php';

$doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;

try {
    // Fetch doctor's schedule
    $scheduleQuery = $db->prepare("
        SELECT monday_start, monday_end,
               tuesday_start, tuesday_end,
               wednesday_start, wednesday_end,
               thursday_start, thursday_end,
               friday_start, friday_end,
               saturday_start, saturday_end
        FROM doctors 
        WHERE id = :doctor_id
    ");
    $scheduleQuery->execute([':doctor_id' => $doctor_id]);
    $schedule = $scheduleQuery->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        die('<div class="alert alert-danger">Doctor schedule not found.</div>');
    }

    // Generate time slots for each day
    $days = [
        'Monday' => ['start' => $schedule['monday_start'], 'end' => $schedule['monday_end']],
        'Tuesday' => ['start' => $schedule['tuesday_start'], 'end' => $schedule['tuesday_end']],
        'Wednesday' => ['start' => $schedule['wednesday_start'], 'end' => $schedule['wednesday_end']],
        'Thursday' => ['start' => $schedule['thursday_start'], 'end' => $schedule['thursday_end']],
        'Friday' => ['start' => $schedule['friday_start'], 'end' => $schedule['friday_end']],
        'Saturday' => ['start' => $schedule['saturday_start'], 'end' => $schedule['saturday_end']]
    ];

    $output = '';
    
    foreach ($days as $day => $times) {
        if ($times['start'] && $times['end']) {
            $output .= '<div class="day-schedule">';
            $output .= '<div class="day-name">' . $day . '</div>';
            $output .= '<div class="time-slots">';
            
            $start = strtotime($times['start']);
            $end = strtotime($times['end']);
            
            // Generate 30-minute slots
            for ($time = $start; $time < $end; $time += 1800) { // 1800 seconds = 30 minutes
                $timeFormatted = date('h:i A', $time);
                $output .= '<div class="time-slot" data-time="' . date('H:i:s', $time) . '">' . $timeFormatted . '</div>';
            }
            
            $output .= '</div></div>';
        }
    }
    
    if (empty($output)) {
        $output = '<div class="alert alert-warning">No available slots found for this doctor.</div>';
    }
    
    echo $output;
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading schedule: ' . $e->getMessage() . '</div>';
}
?>