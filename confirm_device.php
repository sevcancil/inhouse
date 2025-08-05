<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user']) || !isset($_POST['device_id'])) {
    header("Location: profile.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$deviceId = $_POST['device_id'];

// Cihaz gerçekten bu kullanıcıya mı ait kontrol et
$stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ? AND user_id = ?");
$stmt->execute([$deviceId, $userId]);
$device = $stmt->fetch();

if ($device && !$device['confirmation']) {
    $update = $pdo->prepare("UPDATE devices SET confirmation = 1, delivery_date = CURDATE() WHERE id = ?");
    $update->execute([$deviceId]);
}

header("Location: profile.php?confirmed=1");
exit;
