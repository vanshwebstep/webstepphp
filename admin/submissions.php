<?php
// Admin Submissions
// GET  /admin/submissions.php?type=custom|inquiry&status=new    → list
// PUT  /admin/submissions.php?type=custom|inquiry&id=X          → update status

require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$type   = $_GET['type']   ?? 'custom';     // custom | inquiry
$id     = (int)($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

$table = ($type === 'inquiry') ? 'package_inquiries' : 'custom_submissions';

// ─── GET list ────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $where  = '1=1';
    $params = [];
    if ($status) {
        $where .= ' AND status = :status';
        $params[':status'] = $status;
    }
    $rows = $db->prepare("SELECT * FROM `$table` WHERE $where ORDER BY created_at DESC");
    $rows->execute($params);
    $data = $rows->fetchAll();

    // Parse features JSON for custom
    if ($type === 'custom') {
        $data = array_map(function($row) {
            $row['features'] = json_decode($row['features_json'] ?? '[]', true);
            return $row;
        }, $data);
    }

    success($data);
}

// ─── PUT update status ────────────────────────────────────────────────────────
if ($method === 'PUT') {
    if (!$id) error('ID required');
    $data = body();
    $newStatus = $data['status'] ?? '';

    $valid = ['new','viewed','contacted','closed'];
    if (!in_array($newStatus, $valid)) error('Invalid status');

    $db->prepare("UPDATE `$table` SET status=? WHERE id=?")->execute([$newStatus, $id]);
    success(null, 'Status updated');
}

// ─── DELETE ──────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!$id) error('ID required');
    $db->prepare("DELETE FROM `$table` WHERE id=?")->execute([$id]);
    success(null, 'Deleted');
}

error('Invalid request', 400);