<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/smtp_mailer.php';

method('POST');

$data = body();
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    error('Please enter a valid email address');
}

$db = getDB();

$db->prepare("
    INSERT INTO newsletter_subscribers (email, status, source)
    VALUES (:email, 'active', :source)
    ON DUPLICATE KEY UPDATE
        status = 'active',
        source = VALUES(source),
        updated_at = CURRENT_TIMESTAMP
")->execute([
    ':email' => strtolower($email),
    ':source' => sanitize($data['source'] ?? 'footer'),
]);

$subscriberSubject = 'Welcome to Webstep Solutions updates';
$subscriberText = "Thanks for subscribing to Webstep Solutions updates.\n\nYou will receive useful web development, design, and technology updates from us.";
$subscriberHtml = '
    <div style="font-family:Arial,sans-serif;line-height:1.6;color:#0f172a">
        <h2 style="color:#FF1F8E;margin-bottom:8px">Thanks for subscribing</h2>
        <p>You are now subscribed to <strong>Webstep Solutions</strong> updates.</p>
        <p>We will send useful web development, design, and technology updates. No spam.</p>
        <p style="margin-top:20px">— <strong>Webstep Solutions Team</strong></p>
    </div>
';

$adminEmail = defined('ADMIN_NOTIFICATION_EMAIL') ? ADMIN_NOTIFICATION_EMAIL : 'info@webstepsolutions.com';
$adminFallbackEmail = defined('ADMIN_FALLBACK_EMAIL') ? ADMIN_FALLBACK_EMAIL : 'kapilakshu848@gmail.com';

$adminSubject = 'New newsletter subscriber';
$adminText = "New newsletter subscriber: {$email}";
$adminHtml = '
    <div style="font-family:Arial,sans-serif;line-height:1.6;color:#0f172a">
        <h2 style="color:#FF1F8E;margin-bottom:8px">New newsletter subscriber</h2>
        <p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Source:</strong> Footer newsletter</p>
    </div>
';

try {
    sendSmtpMail($email, $email, $subscriberSubject, $subscriberHtml, $subscriberText);
    try {
        sendSmtpMail($adminEmail, 'Webstep Solutions Admin', $adminSubject, $adminHtml, $adminText);
    } catch (Throwable $eAdmin) {
        if (!empty($adminFallbackEmail) && $adminFallbackEmail !== $adminEmail) {
            try {
                sendSmtpMail($adminFallbackEmail, 'Webstep Admin', $adminSubject, $adminHtml, $adminText);
            } catch (Throwable $eFallback) {}
        }
    }
} catch (Throwable $e) {
    // If client SMTP isn't connected, subscription is still saved in DB
}

success(null, 'Subscribed successfully. Thank you!');
