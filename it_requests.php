<?php
session_start();
require_once 'includes/db.php';

$user = $_SESSION['user'] ?? null;

if (!$user || $user['role'] !== 'it') {
    header("Location: dashboard.php");
    exit;
}

// Talepler
$stmt = $pdo->query("
    SELECT ir.*, u.name AS requester_name
    FROM it_requests ir
    JOIN users u ON ir.requested_by = u.id
    ORDER BY ir.created_at DESC
");
$requests = $stmt->fetchAll();

// Talepleri güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['request_id'];
    $status = $_POST['status'];
    $note = $_POST['response_note'] ?? null;

    // Güncelle
    $stmt = $pdo->prepare("UPDATE it_requests SET status = ?, response_note = ?, technician_id = ? WHERE id = ?");
    $stmt->execute([$status, $note, $user['id'], $id]);

    header("Location: it_requests.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>IT Destek Talepleri | Inhouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { border-radius: 1rem; }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>


<div class="container mt-4">
    <h3 class="mb-4">📋 Tüm Destek Talepleri</h3>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Talep güncellendi.</div>
    <?php endif; ?>

    <?php if (count($requests) === 0): ?>
        <div class="alert alert-info">Henüz destek talebi yok.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Talep Eden</th>
                        <th>Başlık</th>
                        <th>Durum</th>
                        <th>Dosya</th>
                        <th>Not</th>
                        <th>Güncelle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($r['requester_name']) ?></strong><br>
                                <small><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['title']) ?></strong><br>
                                <?= nl2br(htmlspecialchars($r['description'])) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= match($r['status']) {
                                    'bekliyor' => 'secondary',
                                    'onaylandı' => 'info',
                                    'tamamlandı' => 'success',
                                    'reddedildi' => 'danger',
                                    default => 'light'
                                } ?>">
                                    <?= $r['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($r['attachment']): ?>
                                    <a href="uploads/<?= $r['attachment'] ?>" target="_blank">İndir</a>
                                <?php else: ?>
                                    <span class="text-muted">Yok</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($r['response_note'] ?? '-') ?></td>
                            <td>
                                <form method="POST" class="d-flex flex-column gap-2">
                                    <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="bekliyor" <?= $r['status'] === 'bekliyor' ? 'selected' : '' ?>>Bekliyor</option>
                                        <option value="onaylandı" <?= $r['status'] === 'onaylandı' ? 'selected' : '' ?>>Onaylandı</option>
                                        <option value="tamamlandı" <?= $r['status'] === 'tamamlandı' ? 'selected' : '' ?>>Tamamlandı</option>
                                        <option value="reddedildi" <?= $r['status'] === 'reddedildi' ? 'selected' : '' ?>>Reddedildi</option>
                                    </select>
                                    <textarea name="response_note" rows="2" class="form-control form-control-sm" placeholder="Yanıt yaz (opsiyonel)"><?= htmlspecialchars($r['response_note'] ?? '') ?></textarea>
                                    <button type="submit" class="btn btn-sm btn-primary">Güncelle</button>
                                </form>
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
