<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// // Fake form data
// $fullName = 'Abu Hurrairah';
// $age = 22;
// $country = 'Pakistan';
// $email = 'AbuHurrairah@example.com';
// $guardianName = 'Khan';
// $whatsappnumber = '1234567890';
// $course = 'course 1';
// $classes = '3 days';
// $selecteddays = 'monday';
// $selectedtime = '01:00 am';
// $message = 'hello world';


$fullName = $_POST['fullName'] ?? 'Imran';
$age = $_POST['age'] ?? '22';
$country = $_POST['country'] ?? 'pak';
$guardianName = $_POST['guardianName'] ?? 'khan';
$whatsappnumber = $_POST['whatsappnumber'] ?? '0334343434';
$email = $_POST['email'] ?? 'hkjh@gmail.com';
$course = $_POST['course'] ?? 'course1';
$classes = $_POST['classes'] ?? '3days';
$selecteddays = $_POST['selecteddays'] ?? 'monday';
$selectedtime = $_POST['selectedtime'] ?? '12am';
$message = $_POST['message'] ?? 'hellow';

// Database connection
// $conn = new mysqli('localhost', 'u970626337_root', 'FILm0q|V', 'u970626337_hidayatulquran');
$conn = new mysqli('localhost', 'root', '', 'hidayatulquran');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Prepare and bind the SQL query
$stmt = $conn->prepare(
    "INSERT INTO registration (fullName, age, country, guardianName, whatsappnumber, email, course, classes, selecteddays, selectedtime, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Query preparation failed'
    ]);
    exit;
}

$stmt->bind_param("sisssssssss", $fullName, $age, $country, $guardianName, $whatsappnumber, $email, $course, $classes, $selecteddays, $selectedtime, $message);

// Execute the query
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

// Close the prepared statement and database connection
$stmt->close();
$conn->close();
?>
