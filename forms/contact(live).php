<?php
header('Content-Type: application/json');

// Get form data
$fullName = strtoupper(trim($_POST['fullName'] ?? ''));
$email    = trim($_POST['email'] ?? '');
$subject  = trim($_POST['subject'] ?? '');
$message  = trim($_POST['message'] ?? '');

// Validate required fields
if (!$fullName || !$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Validate email to prevent header injection
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$conn = new mysqli('localhost', 'u970626337_root', 'FILm0q|V', 'u970626337_hidayatulquran');

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
    // Send email to the user
    $to = $email;
    $emailSubject = "Thank you for contacting us";
    $safeName    = htmlspecialchars($fullName,  ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject,   ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message,   ENT_QUOTES, 'UTF-8');

    $emailMessage = "
    <html>
    <head>
        <title>Thank you for contacting us</title>
    </head>
    <body>
        <p>DEAR $safeName,</p>
        <p>Thank you for reaching out to us. We have successfully received your message with the following details:</p>
        <p><strong>Subject:</strong> $safeSubject</p>
        <p><strong>Message:</strong> $safeMessage</p>
        <p>Our team is currently reviewing your message, and we will get back to you shortly. If you need any further assistance in the meantime, feel free to reply to this email.</p>
        <p>Best regards,</p>
        <p><strong>Hidayat Ul Quran</strong></p>
        <p><strong>Phone/Whatapp:</strong> +92 336 5398542</p>
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

    mail($to, $emailSubject, $emailMessage, $headers);

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
?>
