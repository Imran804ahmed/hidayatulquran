<?php
ob_start();
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $err['message']]);
    }
});

header('Content-Type: application/json');

require_once __DIR__ . '/mailer.php';

$fullName = strtoupper(trim($_POST['fullName'] ?? ''));
$email    = trim($_POST['email']   ?? '');
$subject  = trim($_POST['subject'] ?? '');
$message  = trim($_POST['message'] ?? '');

if (!$fullName || !$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$conn = new mysqli('localhost', 'u970626337_root', 'FILm0q|V', 'u970626337_hidayatulquran');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO contactus (fullName, subject, email, message) VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
    exit;
}

$stmt->bind_param("ssss", $fullName, $subject, $email, $message);

if ($stmt->execute()) {
    $safeName    = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject,  ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message,  ENT_QUOTES, 'UTF-8');

    $emailBody = "
    <html><head><title>Thank you for contacting us</title></head>
    <body style='font-family:Arial,sans-serif; color:#333;'>
        <p>Dear $safeName,</p>
        <p>Thank you for reaching out. We have received your message:</p>
        <table style='border-collapse:collapse; width:100%; max-width:500px;'>
            <tr><td style='padding:8px; border:1px solid #ddd; background:#f9f9f9;'><strong>Subject</strong></td><td style='padding:8px; border:1px solid #ddd;'>$safeSubject</td></tr>
            <tr><td style='padding:8px; border:1px solid #ddd; background:#f9f9f9;'><strong>Message</strong></td><td style='padding:8px; border:1px solid #ddd;'>$safeMessage</td></tr>
        </table>
        <p>Our team will get back to you shortly.</p>
        <p>Best regards,<br><strong>Hidayatul Quran</strong><br>
        Phone/WhatsApp: +92 336 5398542</p>
        <p>
            <a href='https://www.youtube.com/channel/UCDiUcDx4tqPCqvENApO9Gvg'>YouTube</a> &nbsp;|&nbsp;
            <a href='https://www.facebook.com/profile.php?id=61586928376019'>Facebook</a> &nbsp;|&nbsp;
            <a href='https://www.instagram.com/hidayatulquranofficiall/'>Instagram</a>
        </p>
    </body></html>";

    sendMail($email, $fullName, 'Thank you for contacting Hidayatul Quran', $emailBody);

    echo json_encode(['success' => true, 'message' => 'Your message has been sent. Thank you!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}

$stmt->close();
$conn->close();
?>
