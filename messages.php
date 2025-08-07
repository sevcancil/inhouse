<?php
$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? OR receiver_id = ?) ORDER BY created_at DESC");
$stmt->execute([$user_id, $user_id]);
$messages = $stmt->fetchAll();

foreach ($messages as $msg) {
    $plain = decryptMessage($msg['message'], $msg['iv']);
    echo "<p><strong>{$msg['sender_id']}:</strong> " . htmlspecialchars($plain) . "</p>";
}
?>