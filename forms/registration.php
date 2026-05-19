<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Form data
$fullName = $_POST['fullName'] ?? '';
$age = $_POST['age'] ?? '';
$country = $_POST['country'] ?? '';
$guardianName = $_POST['guardianName'] ?? '';
$whatsappnumber = $_POST['whatsappnumber'] ?? '';
$email = $_POST['email'] ?? '';
$course = $_POST['course'] ?? '';
$classes = $_POST['classes'] ?? '';
$selecteddays = $_POST['selecteddays'] ?? [];  // Get the selected days as an array
$selectedtime = $_POST['selectedtime'] ?? '';
$message = $_POST['message'] ?? '';

// Convert the array to a comma-separated string
$selecteddaysString = implode(', ', $selecteddays);

// Database connection
$conn = new mysqli('localhost', 'u970626337_root', 'FILm0q|V', 'u970626337_hidayatulquran');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Check if the email already exists in the database
$emailCheck = $conn->prepare("SELECT COUNT(*) FROM registration WHERE email = ?");
$emailCheck->bind_param("s", $email);
$emailCheck->execute();
$emailCheck->bind_result($emailCount);
$emailCheck->fetch();
$emailCheck->close();

if ($emailCount > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'The email address is already in use. Please use a different email.'
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

$stmt->bind_param("sisssssssss", $fullName, $age, $country, $guardianName, $whatsappnumber, $email, $course, $classes, $selecteddaysString, $selectedtime, $message);

// Execute the query
if ($stmt->execute()) {
    // Send email to the user
    $to = $email;
    $emailSubject = "Thank you for registering with us";
    $emailMessage = "
    <html>
    <head>
        <title>Thank you for registering</title>
    </head>
    <body>
        <p>DEAR $fullName,</p>
        <p>Thank you for registering with us. We have successfully received your registration details:</p>
        <p><strong>Course:</strong> $course</p>
        <p><strong>Classes:</strong> $classes</p>
        <p><strong>Selected Days:</strong> $selecteddaysString</p>
        <p><strong>Selected Time:</strong> $selectedtime</p>
        <p>If you have any questions, feel free to reach out to us.</p>
        <p>Best regards,</p>
        <p><strong>Hidayat Ul Quran</strong></p>
        <p><strong>Phone/WhatsApp:</strong> +92 336 5398542</p>
        <p>Follow us on social media:</p>
        <p>
            <a href='https://www.youtube.com/channel/UCDiUcDx4tqPCqvENApO9Gvg'>
                <img src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSQ1oa7xh5fZwwGuTY3aq7iqWoOQwzfbXqysA&s' alt='YouTube' style='width:32px;height:32px;margin-right:10px;' />
            </a>
            <a href='https://www.facebook.com/profile.php?id=61586928376019'>
                <img src='https://img.freepik.com/premium-vector/facebook-logo-vector-facebook-official-logo-vector-facebook-logo-illustrator_1002350-1803.jpg?semt=ais_hybrid&w=740&q=80' alt='Facebook' style='width:32px;height:32px;margin-right:10px;' />
            </a>
            <a href='https://www.instagram.com/hidayatulquranofficiall/'>
                <img src='https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png' alt='Instagram' style='width:32px;height:32px;' />
            </a>
        </p>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: info@hidayatulquran.com\r\n";
    $headers .= "Reply-To: info@hidayatulquran.com\r\n";

    // Check if the email was sent successfully
    if (mail($to, $emailSubject, $emailMessage, $headers)) {
        echo json_encode([
            'success' => true,
            'message' => 'Your message has been sent. Thank you!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send email.'
        ]);
    }
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
