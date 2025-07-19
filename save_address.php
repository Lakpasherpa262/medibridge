<?php
session_start();
include 'scripts/connect.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$userId = $_SESSION['user_id'];

// Prepare address data
$addressData = [
    'first_name' => explode(' ', $_POST['name'])[0],
    'last_name' => isset(explode(' ', $_POST['name'])[1]) ? explode(' ', $_POST['name'])[1] : '',
    'phone' => $_POST['phone'],
    'email' => $_POST['email'],
    'pincode' => $_POST['pincode'],
    'address_type' => $_POST['address_type'],
    'address' => $_POST['house_no'] . ', ' . $_POST['street'] . ', ' . $_POST['landmark'],
    'state' => $_POST['state'],
    'district' => $_POST['district'],
    'landmark' => $_POST['landmark']
];

// Update user address
$stmt = $db->prepare("UPDATE users SET 
    first_name = :first_name,
    last_name = :last_name,
    phone = :phone,
    email = :email,
    pincode = :pincode,
    address = :address,
    state = :state,
    district = :district,
    landmark = :landmark,
    address_type = :address_type
    WHERE id = :id");

$stmt->execute(array_merge($addressData, ['id' => $userId]));

echo 'success';
?>