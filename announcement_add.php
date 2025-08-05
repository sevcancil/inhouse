<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['it', 'manager'])) {
    die("Bu sayfaya erişim yetkiniz yok.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $created_by = $_SESSION['user']['id'];
    $priority = $_POST['priority'] ?? null;
    $targetRoles = isset($_POST['target_roles']) ? implode(',', $_POST['target_roles']) : null;


    if ($title && $content) {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, created_by, priority, target_roles) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $created_by, $priority, $targetRoles]);
        $success = "Duyuru başarıyla eklendi.";
    } else {
        $error = "Başlık ve içerik boş bırakılamaz.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Duyuru Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h3>📢 Yeni Duyuru Ekle</h3>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Başlık</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">İçerik</label>
            <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
        </div>

        <!-- Önem Derecesi -->
        <div class="mb-3">
            <label for="priority" class="form-label">Önem Derecesi</label>
            <select name="priority" id="priority" class="form-select">
                <option value="">Seçiniz</option>
                <option value="düşük">Düşük</option>
                <option value="orta">Orta</option>
                <option value="yüksek">Yüksek</option>
            </select>
        </div>

        <!-- Hedef Roller -->
        <div class="mb-3">
            <label for="target_roles" class="form-label">Hedef Roller</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="target_roles[]" value="employee" id="roleEmployee">
                <label class="form-check-label" for="roleEmployee">Personel</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="target_roles[]" value="manager" id="roleManager">
                <label class="form-check-label" for="roleManager">Yönetici</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="target_roles[]" value="it" id="roleIT">
                <label class="form-check-label" for="roleIT">IT</label>
            </div>
        </div>


        <button type="submit" class="btn btn-primary">Duyuruyu Yayınla</button>
    </form>
</div>
</body>
</html>
