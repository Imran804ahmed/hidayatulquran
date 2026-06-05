<?php
// ─── SMTP credentials ─────────────────────────────────────────────────────────
define('SMTP_HOST',     'smtp.hostinger.com');
define('SMTP_PORT',     587);
define('SMTP_USERNAME', 'info@hidayatulquran.com');
define('SMTP_PASSWORD', 'hIDAYAqURAN@1234');
define('SMTP_FROM',     'info@hidayatulquran.com');
define('SMTP_FROM_NAME','Hidayatul Quran');
// ──────────────────────────────────────────────────────────────────────────────

function sendMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool
{
    // Open TCP connection to SMTP server
    $socket = @fsockopen('tcp://' . SMTP_HOST, SMTP_PORT, $errno, $errstr, 15);
    if (!$socket) return false;

    stream_set_timeout($socket, 15);

    // Read one full SMTP response (handles multi-line replies like EHLO)
    $read = function () use ($socket): string {
        $data = '';
        while ($line = fgets($socket, 512)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };

    // Send a command and return the server response
    $cmd = function (string $command) use ($socket, $read): string {
        fwrite($socket, $command . "\r\n");
        return $read();
    };

    // Check if response starts with expected SMTP code
    $ok = fn(string $resp, string $code): bool => strncmp(ltrim($resp), $code, 3) === 0;

    // 1. Read greeting
    if (!$ok($read(), '220')) { fclose($socket); return false; }

    // 2. EHLO
    $cmd('EHLO smtp.hostinger.com');

    // 3. Start TLS encryption
    if (!$ok($cmd('STARTTLS'), '220')) { fclose($socket); return false; }
    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket); return false;
    }

    // 4. EHLO again after TLS handshake
    $cmd('EHLO smtp.hostinger.com');

    // 5. Authenticate
    $cmd('AUTH LOGIN');
    $cmd(base64_encode(SMTP_USERNAME));
    if (!$ok($cmd(base64_encode(SMTP_PASSWORD)), '235')) { fclose($socket); return false; }

    // 6. Envelope
    if (!$ok($cmd('MAIL FROM:<' . SMTP_FROM . '>'), '250')) { fclose($socket); return false; }
    if (!$ok($cmd('RCPT TO:<'  . $toEmail      . '>'), '250')) { fclose($socket); return false; }

    // 7. Start message body
    if (!$ok($cmd('DATA'), '354')) { fclose($socket); return false; }

    // 8. Build RFC-compliant email headers + base64 body
    $encSubject  = '=?UTF-8?B?' . base64_encode($subject)          . '?=';
    $encFromName = '=?UTF-8?B?' . base64_encode(SMTP_FROM_NAME)    . '?=';
    $encToName   = $toName ? ('=?UTF-8?B?' . base64_encode($toName) . '?= <' . $toEmail . '>') : $toEmail;

    $message  = "From: {$encFromName} <" . SMTP_FROM . ">\r\n";
    $message .= "To: {$encToName}\r\n";
    $message .= "Subject: {$encSubject}\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "\r\n";
    $message .= chunk_split(base64_encode($htmlBody));
    $message .= "\r\n.\r\n";   // SMTP end-of-data marker

    fwrite($socket, $message);
    if (!$ok($read(), '250')) { fclose($socket); return false; }

    // 9. Close connection
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    return true;
}
