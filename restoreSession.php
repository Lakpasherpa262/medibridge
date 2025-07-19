<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is not logged in but has a "remember me" cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user_id'])) {
    include 'scripts/connect.php'; // adjust path if needed

    $user_id = $_COOKIE['remember_user_id'];

    // Optionally verify the user exists in the database
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Set essential session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username']; // or any other user field
        $_SESSION['role'] = $user['role'];         // optional
    }
}
?>
