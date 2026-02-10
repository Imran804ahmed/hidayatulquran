<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

header('Content-Type: application/json');

// // Fake form data
// $fullName = 'Ibraheem';
// // $lastName = 'khan';
// $subject = 'male';
// $email = 'john.doefrrrty67rf@example.com';
// $message = 'message123';
// $number = '1234567890';

$fullName = $_POST['fullName'] ?? 'Ibra';
$email    = $_POST['email'] ?? 'fgjd@gmail.com';
$subject  = $_POST['subject'] ?? 'Hello';
$message  = $_POST['message'] ?? 'World';

// $conn = new mysqli('localhost', 'u970626337_root', 'FILm0q|V', 'u970626337_hidayatulquran');
$conn = new mysqli('localhost', 'root', '', 'hidayatulquran');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO contactus (fullName, subject, email, message) VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Query preparation failed'
    ]);
    exit;
}

$stmt->bind_param("ssss", $fullName, $subject, $email, $message);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent. Thank you!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send message'
    ]);
}

$stmt->close();
$conn->close();
