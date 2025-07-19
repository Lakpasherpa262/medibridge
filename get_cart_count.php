<?php
// get_cart_count.php
require_once 'cart_functions.php';

session_start();
header('Content-Type: application/json');

echo json_encode(['count' => countCartItems()]);
?>