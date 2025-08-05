<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user']) || !isset($_GET['id'])) {
    header("Location: announcements.php");
    exit;
}

$user = $_SESSION['user'];
$announcementId = (int) $_GET['id'];

// Sadece kendi duyurusunu silebilsin
$stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ? AND created_by = ?");
$stmt->execute([$announcementId, $user['id']]);

header("Location: announcements.php");
exit;
