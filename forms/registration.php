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

// Form data
$fullName       = trim($_POST['fullName']       ?? '');
$age            = trim($_POST['age']            ?? '');
$country        = trim($_POST['country']        ?? '');
$guardianName   = trim($_POST['guardianName']   ?? '');
$whatsappnumber = trim($_POST['whatsappnumber'] ?? '');
$email          = trim($_POST['email']          ?? '');
$course         = trim($_POST['course']         ?? '');
$classes        = trim($_POST['classes']        ?? '');
$selecteddays   = $_POST['selecteddays']        ?? [];
$selectedtime   = trim($_POST['selectedtime']   ?? '');
$message        = trim($_POST['message']        ?? '');

// Validate required fields
if (!$fullName || !$age || !$country || !$whatsappnumber || !$email || !$course || !$classes || !$selectedtime) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$selecteddaysString = implode(', ', $selecteddays);

$conn = new mysqli('localhost', 'u970626337_root', 'FILm0q|V', 'u970626337_hidayatulquran');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Prevent duplicate registrations
$emailCheck = $conn->prepare("SELECT COUNT(*) FROM registration WHERE email = ?");
$emailCheck->bind_param("s", $email);
$emailCheck->execute();
$emailCheck->bind_result($emailCount);
$emailCheck->fetch();
$emailCheck->close();

if ($emailCount > 0) {
    echo json_encode(['success' => false, 'message' => 'This email is already registered. Please use a different email.']);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO registration (fullName, age, country, guardianName, whatsappnumber, email, course, classes, selecteddays, selectedtime, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
    exit;
}

$stmt->bind_param("sisssssssss", $fullName, $age, $country, $guardianName, $whatsappnumber, $email, $course, $classes, $selecteddaysString, $selectedtime, $message);

if ($stmt->execute()) {
    $safeName    = htmlspecialchars($fullName,           ENT_QUOTES, 'UTF-8');
    $safeCourse  = htmlspecialchars($course,             ENT_QUOTES, 'UTF-8');
    $safeClasses = htmlspecialchars($classes,            ENT_QUOTES, 'UTF-8');
    $safeDays    = htmlspecialchars($selecteddaysString, ENT_QUOTES, 'UTF-8');
    $safeTime    = htmlspecialchars($selectedtime,       ENT_QUOTES, 'UTF-8');

    $emailBody = "
    <html><head><title>Thank you for registering</title></head>
    <body style='font-family:Arial,sans-serif; color:#333;'>
        <p>Dear $safeName,</p>
        <p>Thank you for registering with us. We have successfully received your registration details:</p>
        <table style='border-collapse:collapse; width:100%; max-width:500px;'>
            <tr><td style='padding:8px; border:1px solid #ddd; background:#f9f9f9;'><strong>Course</strong></td><td style='padding:8px; border:1px solid #ddd;'>$safeCourse</td></tr>
            <tr><td style='padding:8px; border:1px solid #ddd; background:#f9f9f9;'><strong>Classes</strong></td><td style='padding:8px; border:1px solid #ddd;'>$safeClasses</td></tr>
            <tr><td style='padding:8px; border:1px solid #ddd; background:#f9f9f9;'><strong>Selected Days</strong></td><td style='padding:8px; border:1px solid #ddd;'>$safeDays</td></tr>
            <tr><td style='padding:8px; border:1px solid #ddd; background:#f9f9f9;'><strong>Preferred Time</strong></td><td style='padding:8px; border:1px solid #ddd;'>$safeTime</td></tr>
        </table>
        <p>If you have any questions, feel free to reach out to us.</p>
        <p>Best regards,<br><strong>Hidayatul Quran</strong><br>
        Phone/WhatsApp: +92 336 5398542</p>
        <p>
            <a href='https://www.youtube.com/channel/UCDiUcDx4tqPCqvENApO9Gvg'>YouTube</a> &nbsp;|&nbsp;
            <a href='https://www.facebook.com/profile.php?id=61586928376019'>Facebook</a> &nbsp;|&nbsp;
            <a href='https://www.instagram.com/hidayatulquranofficiall/'>Instagram</a>
        </p>
    </body></html>";

    sendMail($email, $fullName, 'Thank you for registering with Hidayatul Quran', $emailBody);

    echo json_encode(['success' => true, 'message' => 'Your registration has been submitted. Thank you!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save registration. Please try again.']);
}

$stmt->close();
$conn->close();
?>
