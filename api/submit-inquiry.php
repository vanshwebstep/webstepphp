<?php
// POST /api/submit-inquiry.php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/smtp_mailer.php';

method('POST');

$data = body();

$name  = sanitize($data['name']  ?? '');
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitize($data['phone']  ?? '');
$msg   = sanitize($data['message'] ?? '');
$pkgId = (int)($data['package_id'] ?? 0);

if (!$name)  error('Name is required');
if (!$email) error('Valid email is required');

$db = getDB();

// Verify package exists if provided
if ($pkgId) {
    $check = $db->prepare("SELECT id FROM packages WHERE id=? AND is_active=1");
    $check->execute([$pkgId]);
    if (!$check->fetch()) $pkgId = 0;
}

$stmt = $db->prepare("
    INSERT INTO package_inquiries (package_id, name, email, phone, message)
    VALUES (:pkg, :name, :email, :phone, :msg)
");
$stmt->execute([
    ':pkg'   => $pkgId ?: null,
    ':name'  => $name,
    ':email' => $email,
    ':phone' => $phone,
    ':msg'   => $msg,
]);

$leadId = $db->lastInsertId();
$adminEmail = defined('ADMIN_NOTIFICATION_EMAIL') ? ADMIN_NOTIFICATION_EMAIL : 'info@webstepsolutions.com';
$adminFallbackEmail = defined('ADMIN_FALLBACK_EMAIL') ? ADMIN_FALLBACK_EMAIL : 'kapilakshu848@gmail.com';

$adminText = implode("\n", array_filter([
    "New plan inquiry #{$leadId}",
    $pkgId ? "Package ID: {$pkgId}" : '',
    "Name: {$name}",
    "Email: {$email}",
    $phone ? "Phone: {$phone}" : '',
    $msg ? "Message: {$msg}" : '',
]));
$adminHtml = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#0f172a">'
    . '<h2 style="color:#a855f7;margin-bottom:8px">New plan inquiry</h2>'
    . ($pkgId ? '<p><strong>Package ID:</strong> ' . (int)$pkgId . '</p>' : '')
    . '<p><strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>'
    . ($phone ? '<p><strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</p>' : '')
    . ($msg ? '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')) . '</p>' : '')
    . '</div>';

try {
    sendSmtpMail($adminEmail, 'Webstep Solutions Admin', 'New plan inquiry', $adminHtml, $adminText);
} catch (Throwable $e) {
    if (!empty($adminFallbackEmail) && $adminFallbackEmail !== $adminEmail) {
        try {
            sendSmtpMail($adminFallbackEmail, 'Webstep Admin', 'New plan inquiry', $adminHtml, $adminText);
        } catch (Throwable $e2) {}
    }
}

success(null, 'Inquiry submitted! We will contact you within one business day.');
