<?php
require_once __DIR__ . '/../includes/helpers.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Invalid request', 400);
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) error('No file uploaded');

$allowedFolders = ['portfolio', 'blog', 'case-studies'];
$folder = $_POST['folder'] ?? 'portfolio';
if (!in_array($folder, $allowedFolders, true)) $folder = 'portfolio';

$file = $_FILES['file'];
$allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!isset($allowed[$ext])) error('Only jpg, png, webp allowed');
if ($file['size'] > 5 * 1024 * 1024) error('Max file size is 5MB');

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, $allowed, true)) error('Invalid file type');

$uploadDir = __DIR__ . '/../uploads/' . $folder . '/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename = bin2hex(random_bytes(8)) . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) error('Upload failed', 500);

$baseUrl = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$url = $baseUrl . '/uploads/' . $folder . '/' . $filename;
success(['url' => $url], 'Image uploaded');