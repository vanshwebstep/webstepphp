<?php
function smtpCommand($socket, string $command, array $expectedCodes): string {
    fwrite($socket, $command . "\r\n");
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') break;
    }

    $code = (int)substr($response, 0, 3);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('SMTP command failed');
    }

    return $response;
}

function smtpReadGreeting($socket): void {
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') break;
    }

    if ((int)substr($response, 0, 3) !== 220) {
        throw new RuntimeException('SMTP connection failed');
    }
}

function smtpEncodeHeader(string $value): string {
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function sendSmtpMail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): void {
    $host = 'smtp.gmail.com';
    $port = 465;
    $username = 'kapilakshu848@gmail.com';
    $password = 'zlgninioettnhazf';
    $fromEmail = $username;
    $fromName = 'Webstep Solutions';
    $timeout = 20;

    $socket = fsockopen('ssl://' . $host, $port, $errno, $errstr, $timeout);
    if (!$socket) {
        throw new RuntimeException('Unable to connect to SMTP server');
    }

    stream_set_timeout($socket, $timeout);

    try {
        smtpReadGreeting($socket);
        smtpCommand($socket, 'EHLO webstep.local', [250]);
        smtpCommand($socket, 'AUTH LOGIN', [334]);
        smtpCommand($socket, base64_encode($username), [334]);
        smtpCommand($socket, base64_encode($password), [235]);
        smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        smtpCommand($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtpCommand($socket, 'DATA', [354]);

        $boundary = '=_webstep_' . bin2hex(random_bytes(12));
        $headers = [
            'From: ' . smtpEncodeHeader($fromName) . ' <' . $fromEmail . '>',
            'To: ' . smtpEncodeHeader($toName ?: $toEmail) . ' <' . $toEmail . '>',
            'Subject: ' . smtpEncodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'Date: ' . date(DATE_RFC2822),
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n";
        $message .= '--' . $boundary . "\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $textBody . "\r\n\r\n";
        $message .= '--' . $boundary . "\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $htmlBody . "\r\n\r\n";
        $message .= '--' . $boundary . "--\r\n.";

        smtpCommand($socket, $message, [250]);
        smtpCommand($socket, 'QUIT', [221]);
    } finally {
        fclose($socket);
    }
}
