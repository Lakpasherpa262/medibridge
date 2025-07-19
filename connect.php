<?php
// Database connection settings
$host = 'localhost';
$dbname = 'medibridge';
$username = 'root';
$password = '';

try {
    // Create a PDO instance with error handling and persistent connection
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions for errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch results as associative arrays
        PDO::ATTR_PERSISTENT => true, // Use persistent connections for better performance
        PDO::ATTR_TIMEOUT => 5, // Set connection timeout to 5 seconds
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // Set character set to utf8mb4
    ];

    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // Log the error instead of displaying it to the user
    error_log("Database connection failed: " . $e->getMessage());

    // Display a generic error message to the user
    die("Unable to connect to the database. Please try again later.");
}
?>