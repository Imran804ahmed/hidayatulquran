<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Get form data
$fullName = strtoupper($_POST['fullName'] ?? ''); // Convert fullName to uppercase
$email    = $_POST['email'] ?? '';
$subject  = $_POST['subject'] ?? '';
$message  = $_POST['message'] ?? '';

$conn = new mysqli('localhost', 'u970626337_root', 'FILm0q|V', 'u970626337_hidayatulquran');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Check if the email already exists in the database
$emailCheck = $conn->prepare("SELECT COUNT(*) FROM contactus WHERE email = ?");
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
    // Send email to the user
    $to = $email;
    $emailSubject = "Thank you for contacting us";
    $emailMessage = "
    <html>
    <head>
        <title>Thank you for contacting us</title>
    </head>
    <body>
        <p>DEAR $fullName,</p>
        <p>Thank you for reaching out to us. We have successfully received your message with the following details:</p>
        <p><strong>Subject:</strong> $subject</p>
        <p><strong>Message:</strong> $message</p>
        <p>Our team is currently reviewing your message, and we will get back to you shortly. If you need any further assistance in the meantime, feel free to reply to this email.</p>
        <p>Best regards,</p>
        <p><strong>Hidayat Ul Quran</strong></p>
        <p><strong>Phone/Whatapp:</strong> +92 3342730589</p>
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

$stmt->close();
$conn->close();
?>
