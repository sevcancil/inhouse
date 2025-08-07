<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'it'])) {
    die("Bu sayfaya erişim yetkiniz yok.");
}

// Onay veya reddetme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];

    if (in_array($action, ['approved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE leaves SET status = ?, approved_by = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$action, $_SESSION['user']['id'], $leave_id]);
    }
}

// Başvuruları çek
$stmt = $pdo->query("
    SELECT l.*, u.name AS user_name, lt.name AS type_name, a.name AS approver_name
    FROM leaves l
    JOIN users u ON l.user_id = u.id
    JOIN leave_types lt ON l.leave_type_id = lt.id
    LEFT JOIN users a ON l.approved_by = a.id
    ORDER BY l.created_at DESC
");
$leaves = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İzin Başvuruları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h4>📋 Tüm İzin Başvuruları</h4>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Personel</th>
                <th>İzin Türü</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                <th>Süre (gün)</th>
                <th>Açıklama</th>
                <th>Durum</th>
                <th>Onaylayan</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($leaves as $leave): ?>
                <?php
                    $total_days = $leave['total_days'];
                    if (!$total_days) {
                        $total_days = calculateTotalLeaveDays($leave['start_date'], $leave['end_date']);
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($leave['user_name']) ?></td>
                    <td><?= htmlspecialchars($leave['type_name']) ?></td>
                    <td><?= htmlspecialchars($leave['start_date']) ?></td>
                    <td><?= htmlspecialchars($leave['end_date']) ?></td>
                    <td><?= $total_days ?></td>
                    <td><?= nl2br(htmlspecialchars($leave['reason'])) ?></td>
                    <td>
                        <?php
                        if ($leave['status'] === 'approved') echo '<span class="badge bg-success">Onaylandı</span>';
                        elseif ($leave['status'] === 'rejected') echo '<span class="badge bg-danger">Reddedildi</span>';
                        else echo '<span class="badge bg-warning text-dark">Bekliyor</span>';
                        ?>
                    </td>
                    <td><?= htmlspecialchars($leave['approver_name'] ?? '-') ?></td>
                    <td>
                        <?php if ($leave['status'] === 'pending'): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                <button name="action" value="approved" class="btn btn-sm btn-success">Onayla</button>
                                <button name="action" value="rejected" class="btn btn-sm btn-danger">Reddet</button>
                            </form>
                        <?php else: ?>
                            <em>-</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
