<?php
// POST /api/submit-custom.php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/smtp_mailer.php';

method('POST');

$data = body();

// ─── Validate ─────────────────────────────────────────────────────────────────
$name  = sanitize($data['name']  ?? '');
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitize($data['phone'] ?? '');
$msg   = sanitize($data['message'] ?? '');
$source = sanitize($data['source'] ?? 'custom-package');
$location = sanitize($data['location'] ?? '');

if ($location) {
    $msg = trim($msg ? $msg . "\nLocation: " . $location : "Location: " . $location);
}

if (!$name)  error('Name is required');
if (!$email) error('Valid email is required');

$pkg = $data['package'] ?? [];

$websiteTypeId = sanitize($pkg['websiteType']['id']    ?? '');
$websiteType   = sanitize($pkg['websiteType']['title'] ?? '');
$pagesId       = sanitize($pkg['pages']['id']          ?? '');
$pages         = sanitize($pkg['pages']['title']       ?? '');
$designId      = sanitize($pkg['design']['id']         ?? '');
$design        = sanitize($pkg['design']['title']      ?? '');
$features      = $pkg['features'] ?? [];
$total         = (float)($pkg['total'] ?? 0);

$featuresJson  = json_encode(array_map(fn($f) => [
    'id'    => sanitize($f['id']    ?? ''),
    'title' => sanitize($f['title'] ?? ''),
    'price' => (float)($f['price'] ?? 0),
], $features));

// ─── Insert ───────────────────────────────────────────────────────────────────
$db = getDB();
$stmt = $db->prepare("
    INSERT INTO custom_submissions
        (source, name, email, phone, message,
         website_type_id, website_type,
         pages_id, pages,
         design_id, design,
         features_json, estimated_total)
    VALUES
        (:source, :name, :email, :phone, :message,
         :wt_id, :wt,
         :pg_id, :pg,
         :des_id, :des,
         :features, :total)
");

$stmt->execute([
    ':source'  => $source,
    ':name'    => $name,
    ':email'   => $email,
    ':phone'   => $phone,
    ':message' => $msg,
    ':wt_id'   => $websiteTypeId,
    ':wt'      => $websiteType,
    ':pg_id'   => $pagesId,
    ':pg'      => $pages,
    ':des_id'  => $designId,
    ':des'     => $design,
    ':features'=> $featuresJson,
    ':total'   => $total,
]);

$leadId = $db->lastInsertId();
$packageLines = array_filter([
    $websiteType ? "Website Type: {$websiteType}" : '',
    $pages ? "Pages: {$pages}" : '',
    $design ? "Design: {$design}" : '',
    $total > 0 ? "Estimated Total: \${$total}" : '',
]);
$featureLines = array_map(fn($f) => '- ' . ($f['title'] ?? '') . ' ($' . (float)($f['price'] ?? 0) . ')', $features);

// ═══════════════════════════════════════════
// Admin notification email
// ═══════════════════════════════════════════
$adminEmail = defined('ADMIN_NOTIFICATION_EMAIL') ? ADMIN_NOTIFICATION_EMAIL : 'info@webstepsolutions.com';
$adminFallbackEmail = defined('ADMIN_FALLBACK_EMAIL') ? ADMIN_FALLBACK_EMAIL : 'kapilakshu848@gmail.com';

$adminText = implode("\n", array_filter([
    "New website lead #{$leadId}",
    "Source: {$source}",
    "Name: {$name}",
    "Email: {$email}",
    $phone ? "Phone: {$phone}" : '',
    $msg ? "Message: {$msg}" : '',
    $packageLines ? "\nPackage:\n" . implode("\n", $packageLines) : '',
    $featureLines ? "\nFeatures:\n" . implode("\n", $featureLines) : '',
]));
$adminHtml = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#0f172a">'
    . '<h2 style="color:#a855f7;margin-bottom:8px">New website lead</h2>'
    . '<p><strong>Source:</strong> ' . htmlspecialchars($source, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>'
    . ($phone ? '<p><strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</p>' : '')
    . ($msg ? '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')) . '</p>' : '')
    . ($packageLines ? '<p><strong>Package:</strong><br>' . nl2br(htmlspecialchars(implode("\n", $packageLines), ENT_QUOTES, 'UTF-8')) . '</p>' : '')
    . ($featureLines ? '<p><strong>Features:</strong><br>' . nl2br(htmlspecialchars(implode("\n", $featureLines), ENT_QUOTES, 'UTF-8')) . '</p>' : '')
    . '</div>';

try {
    sendSmtpMail($adminEmail, 'Webstep Solutions Admin', 'New website lead from ' . $source, $adminHtml, $adminText);
} catch (Throwable $e) {
    if (!empty($adminFallbackEmail) && $adminFallbackEmail !== $adminEmail) {
        try {
            sendSmtpMail($adminFallbackEmail, 'Webstep Admin', 'New website lead from ' . $source, $adminHtml, $adminText);
        } catch (Throwable $e2) {}
    }
}

// ═══════════════════════════════════════════
// User confirmation email
// ═══════════════════════════════════════════
$userText = implode("\n", array_filter([
    "Hi {$name},",
    "",
    "Thank you for reaching out to Webstep Solutions! We have received your request and our team will review your requirements and reply within one business day.",
    "",
    $packageLines ? "Your Package Summary:\n" . implode("\n", $packageLines) : '',
    $featureLines ? "\nSelected Features:\n" . implode("\n", $featureLines) : '',
    "",
    "If you have any questions in the meantime, just reply to this email or contact us at info@webstepsolutions.com.",
    "",
    "— Webstep Solutions Team",
]));

$userHtml = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#0f172a;max-width:560px;margin:0 auto">'
    . '<h2 style="color:#FF1F8E;margin-bottom:8px">Thanks for reaching out, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '!</h2>'
    . '<p>Thank you for reaching out to <strong>Webstep Solutions</strong>! We have received your request and our team will review your requirements and reply within one business day.</p>'
    . ($packageLines ? '<h3 style="color:#334155;margin-top:20px;margin-bottom:6px">Your Package Summary</h3><p>' . nl2br(htmlspecialchars(implode("\n", $packageLines), ENT_QUOTES, 'UTF-8')) . '</p>' : '')
    . ($featureLines ? '<h3 style="color:#334155;margin-top:20px;margin-bottom:6px">Selected Features</h3><p>' . nl2br(htmlspecialchars(implode("\n", $featureLines), ENT_QUOTES, 'UTF-8')) . '</p>' : '')
    . '<p style="margin-top:24px;color:#64748b;font-size:.9rem">If you have any questions in the meantime, reply to this email or contact us at <a href="mailto:info@webstepsolutions.com" style="color:#FF1F8E">info@webstepsolutions.com</a>.</p>'
    . '<p style="margin-top:24px">— <strong>Webstep Solutions Team</strong></p>'
    . '</div>';

try {
    sendSmtpMail($email, $name, 'We received your request — Webstep Solutions', $userHtml, $userText);
} catch (Throwable $e) {
    // Keep lead submission successful even if SMTP is unavailable.
}

success(null, 'Lead submitted successfully!');
