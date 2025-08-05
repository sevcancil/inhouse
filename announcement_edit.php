<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user']) || !isset($_GET['id'])) {
    header("Location: announcements.php");
    exit;
}

$user = $_SESSION['user'];
$announcementId = (int) $_GET['id'];

// Duyuru verisini al
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ? AND created_by = ?");
$stmt->execute([$announcementId, $user['id']]);
$announcement = $stmt->fetch();

if (!$announcement) {
    die("Bu duyuruya erişiminiz yok.");
}

// Güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $priority = $_POST['priority'] ?? null;
    $target_roles = $_POST['target_roles'] ?? [];

    $targetRolesString = implode(',', $target_roles);

    $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, priority = ?, target_roles = ? WHERE id = ? AND created_by = ?");
    $stmt->execute([$title, $content, $priority, $targetRolesString, $announcementId, $user['id']]);

    header("Location: announcements.php");
    exit;
}

// Mevcut değerleri parçala
$currentRoles = explode(',', $announcement['target_roles'] ?? '');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Duyuru Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h3 class="mb-4">📢 Duyuru Düzenle</h3>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">Başlık</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($announcement['title']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">İçerik</label>
                <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($announcement['content']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Önem Derecesi</label>
                <select name="priority" class="form-select">
                    <option value="">Seçiniz</option>
                    <option value="düşük" <?= $announcement['priority'] === 'düşük' ? 'selected' : '' ?>>Düşük</option>
                    <option value="orta" <?= $announcement['priority'] === 'orta' ? 'selected' : '' ?>>Orta</option>
                    <option value="yüksek" <?= $announcement['priority'] === 'yüksek' ? 'selected' : '' ?>>Yüksek</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Hedef Roller</label><br>
                <?php foreach (['employee' => 'Personel', 'manager' => 'Yönetici', 'it' => 'IT'] as $role => $label): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="target_roles[]" value="<?= $role ?>" <?= in_array($role, $currentRoles) ? 'checked' : '' ?>>
                        <label class="form-check-label"><?= $label ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-success">Kaydet</button>
            <a href="announcements.php" class="btn btn-secondary">İptal</a>
        </form>
    </div>
</body>
</html>
