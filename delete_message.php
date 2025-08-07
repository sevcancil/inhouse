<?php
session_start();
require_once 'includes/db.php';

$user_id = $_SESSION['user']['id'];
$msg_id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
$stmt->execute([$msg_id, $user_id]);

header("Location: messages.php");
exit;
?>