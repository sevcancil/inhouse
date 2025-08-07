<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['user']['id'];

// Hem gelen hem giden mesajlar
$stmt = $pdo->prepare("
    SELECT m.*, 
           u1.name AS sender_name,
           u2.name AS receiver_name
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.id
    JOIN users u2 ON m.receiver_id = u2.id
    WHERE m.sender_id = :id OR m.receiver_id = :id
    ORDER BY m.created_at DESC
");
$stmt->execute(['id' => $current_user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Gelen Mesajlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
    <h2>📥 Mesaj Kutusu</h2>
    <?php if (empty($messages)): ?>
        <div class="alert alert-info">Henüz mesajınız yok.</div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($messages as $msg): ?>
                <?php
                    $iv = $msg['iv']; // artık base64 olarak saklandığı için decode edilecek
                    $message = decryptMessage($msg['message'], $iv);

                ?>
                <li class="list-group-item">
                    <strong>
                        <?php if ($msg['sender_id'] == $current_user_id): ?>
                            Siz → <?= htmlspecialchars($msg['receiver_name']) ?>
                        <?php else: ?>
                            <?= htmlspecialchars($msg['sender_name']) ?> → Siz
                        <?php endif; ?>
                    </strong><br>

                    <?= nl2br(htmlspecialchars($message)) ?><br>
                    <small class="text-muted"><?= $msg['created_at'] ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>
