<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

$user = $_SESSION['user'];

$stmt = $pdo->query("
    SELECT a.*, u.name AS creator_name 
    FROM announcements a
    JOIN users u ON a.created_by = u.id
    ORDER BY a.created_at DESC
");
$announcements = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Duyurular</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">📢 Duyurular</h2>

    <?php if (count($announcements) === 0): ?>
        <div class="alert alert-warning">Hiç duyuru bulunamadı.</div>
    <?php else: ?>
        <?php foreach ($announcements as $a): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($a['title']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        <?= date('d.m.Y H:i', strtotime($a['created_at'])) ?> | <?= htmlspecialchars($a['creator_name']) ?>
                    </h6>
                    <p class="card-text"><?= nl2br(htmlspecialchars($a['content'])) ?></p>

                    <?php if ($a['created_by'] === $user['id']): ?>
                        <a href="announcement_edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">🖊 Düzenle</a>
                        <a href="announcement_delete.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?')">🗑 Sil</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
