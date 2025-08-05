<?php
session_start();
require_once 'includes/db.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header("Location: login.php");
    exit;
}

// Görevleri çek
$stmt = $pdo->prepare("
    SELECT t.*, 
           u1.name AS assigned_to_name, 
           u2.name AS assigned_by_name 
    FROM tasks t
    JOIN users u1 ON t.assigned_to = u1.id
    JOIN users u2 ON t.assigned_by = u2.id
    WHERE t.assigned_to = :uid OR :is_manager = 1
    ORDER BY t.created_at DESC
");

$stmt->execute([
    'uid' => $user['id'],
    'is_manager' => ($user['role'] === 'manager' || $user['role'] === 'it') ? 1 : 0
]);

$tasks = $stmt->fetchAll();

// Görev atama işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_task'])) {
    $assigned_to = $_POST['assigned_to'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if ($assigned_to && $title && $description) {
        $insert = $pdo->prepare("INSERT INTO tasks (assigned_to, assigned_by, title, description) VALUES (?, ?, ?, ?)");
        $insert->execute([$assigned_to, $user['id'], $title, $description]);
        header("Location: tasks.php?assigned=1");
        exit;
    }
}


// Görev durumu güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];

    $update = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
    $update->execute([$status, $task_id, $user['id']]);

    header("Location: tasks.php?updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Görevlerim | Inhouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <?php if (in_array($user['role'], ['manager', 'it'])): ?>
        <?php if (isset($_GET['assigned'])): ?>
    <div class="alert alert-success">✅ Görev başarıyla atandı.</div>
<?php endif; ?>

    <?php
    // Kullanıcı listesi
    $userStmt = $pdo->prepare("SELECT id, name FROM users WHERE id != ?");
    $userStmt->execute([$user['id']]);
    $usersList = $userStmt->fetchAll();
    ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            ✍️ Görev Ata
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="assigned_to" class="form-label">Kime Atanacak?</label>
                    <select name="assigned_to" id="assigned_to" class="form-select" required>
                        <option value="">Seçiniz</option>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label">Görev Başlığı</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Açıklama</label>
                    <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" name="assign_task" class="btn btn-primary">➕ Görev Ata</button>
            </form>
        </div>
    </div>
<?php endif; ?>

    <h3 class="mb-3">🗂️ Görevler</h3>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Görev durumu güncellendi.</div>
    <?php endif; ?>

    <?php if (count($tasks) === 0): ?>
        <div class="alert alert-info">Henüz görev atanmadı.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Başlık</th>
                        <th>Açıklama</th>
                        <th>Atayan</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t['title']) ?></strong></td>
                            <td><?= nl2br(htmlspecialchars($t['description'])) ?></td>
                            <td><?= htmlspecialchars($t['assigned_by_name']) ?></td>
                            <td>
                                <span class="badge bg-<?= match($t['status']) {
                                    'bekliyor' => 'secondary',
                                    'devam ediyor' => 'info',
                                    'tamamlandı' => 'success',
                                    default => 'light'
                                } ?>">
                                    <?= $t['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($t['assigned_to'] == $user['id']): ?>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="task_id" value="<?= $t['id'] ?>">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="bekliyor" <?= $t['status'] === 'bekliyor' ? 'selected' : '' ?>>Bekliyor</option>
                                            <option value="devam ediyor" <?= $t['status'] === 'devam ediyor' ? 'selected' : '' ?>>Devam Ediyor</option>
                                            <option value="tamamlandı" <?= $t['status'] === 'tamamlandı' ? 'selected' : '' ?>>Tamamlandı</option>
                                        </select>
                                        <button type="submit" name="update_task" class="btn btn-sm btn-primary">Kaydet</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
