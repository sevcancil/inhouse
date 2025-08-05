<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';
$user = $_SESSION['user'];

// Son 5 duyuruyu al
$announcements = $pdo->query("
    SELECT a.title, a.content, a.created_at, a.priority, a.target_roles, u.name as creator
    FROM announcements a
    JOIN users u ON a.created_by = u.id
    ORDER BY a.created_at DESC
    LIMIT 5
")->fetchAll();

// Bugün doğanları al
$today = date('m-d');
$birthdays = $pdo->prepare("SELECT name, department FROM users WHERE DATE_FORMAT(birthdate, '%m-%d') = ?");
$birthdays->execute([$today]);
$birthdays = $birthdays->fetchAll();
?>

<?php
$stmt = $pdo->prepare("SELECT title, status FROM tasks WHERE assigned_to = ? ORDER BY created_at DESC LIMIT 3");
$stmt->execute([$user['id']]);
$recentTasks = $stmt->fetchAll();
?>



<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Inhouse | Anasayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f9f9fb; }
        .card { border-radius: 1rem; }
        .welcome { font-size: 1.2rem; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>


<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        📌 Son Görevleriniz
    </div>
    <ul class="list-group list-group-flush">
        <?php if (count($recentTasks) === 0): ?>
            <li class="list-group-item">Henüz görev atanmadı.</li>
        <?php else: ?>
            <?php foreach ($recentTasks as $task): ?>
                <li class="list-group-item d-flex justify-content-between">
                    <?= htmlspecialchars($task['title']) ?>
                    <span class="badge bg-<?= match($task['status']) {
                        'bekliyor' => 'secondary',
                        'devam ediyor' => 'info',
                        'tamamlandı' => 'success',
                        default => 'light'
                    } ?>"><?= $task['status'] ?></span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <div class="card-footer text-end">
        <a href="tasks.php" class="btn btn-sm btn-outline-primary">Tüm Görevler</a>
    </div>
</div>

<?php if (in_array($user['role'], ['manager', 'it'])): ?>
    <div class="mb-3">
        <a href="tasks.php" class="btn btn-primary">➕ Yeni Görev Ata</a>
    </div>
<?php endif; ?>

<div class="container mt-4">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm p-4 welcome bg-light">
    <p class="mb-2">
        👋 Merhaba <strong><?= htmlspecialchars($user['name']) ?></strong>, Hoş Geldin!
    </p>
    <div class="d-flex flex-wrap gap-3">
        <div>Departman: <strong><?= htmlspecialchars($user['department']) ?></strong></div>
        <div>Rol: <strong><?= htmlspecialchars($user['role']) ?></strong></div>
    </div>
</div>

        </div>
    </div>

    <div class="row g-4">
        <!-- Duyurular -->
        <div class="col-md-8">
            <div class="card shadow-sm p-4">
                <h4 class="mb-3">📢 Duyurular</h4>
                <?php if (in_array($user['role'], ['manager', 'it'])): ?>
                    <a href="announcement_add.php" class="btn btn-sm btn-outline-success mb-3">➕ Yeni Duyuru Ekle</a>
                <?php endif; ?>

                <?php if (count($announcements) === 0): ?>
                    <p class="text-muted">Henüz duyuru yok.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $a): ?>
    <div class="mb-3">
        <h5 class="mb-1">
            <?= htmlspecialchars($a['title']) ?>
            <?php if (!empty($a['priority'])): ?>
                <span class="badge 
                    <?= match($a['priority']) {
                        'yüksek' => 'bg-danger',
                        'orta' => 'bg-warning text-dark',
                        'düşük' => 'bg-secondary',
                        default => 'bg-light text-dark'
                    } ?>">
                    <?= ucfirst($a['priority']) ?>
                </span>
            <?php endif; ?>
        </h5>

        <small class="text-muted">
            <?= date('d.m.Y H:i', strtotime($a['created_at'])) ?> |
            <?= htmlspecialchars($a['creator']) ?>
        </small>

        <?php if (!empty($a['target_roles'])): ?>
            <div class="mt-1 mb-2">
                <small class="text-muted">🎯 Hedef: <?= str_replace(
                    ['employee', 'manager', 'it'],
                    ['Personel', 'Yönetici', 'IT'],
                    htmlspecialchars($a['target_roles'])
                ) ?></small>
            </div>
        <?php endif; ?>

        <p><?= nl2br(htmlspecialchars($a['content'])) ?></p>
        <hr>
    </div>
<?php endforeach; ?>

                <?php endif; ?>
            </div>
        </div>

        <!-- Doğum Günleri -->
        <div class="col-md-4">
            <div class="card shadow-sm p-4">
                <h4 class="mb-3">🎂 Bugün Doğanlar</h4>
                <?php if (count($birthdays) === 0): ?>
                    <p class="text-muted">Bugün doğan yok.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($birthdays as $b): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($b['name']) ?></strong><br>
                                <small><?= htmlspecialchars($b['department']) ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
