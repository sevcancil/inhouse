<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['it', 'manager'])) {
    header("Location: dashboard.php");
    exit;
}

$deviceId = $_POST['device_id'] ?? null;

if ($deviceId) {
    $stmt = $pdo->prepare("UPDATE devices SET return_date = CURDATE() WHERE id = ? AND return_date IS NULL");
    $stmt->execute([$deviceId]);
}

$returnNote = $_POST['return_note'] ?? '';
$stmt = $pdo->prepare("UPDATE devices SET return_date = NOW(), return_note = ?, confirmation = 1 WHERE id = ?");
$stmt->execute([$returnNote, $deviceId]);


header("Location: device_list.php?filter=active");
exit;
