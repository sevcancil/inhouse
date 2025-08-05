<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['it', 'manager'])) {
    header("Location: dashboard.php");
    exit;
}

// Filtre seçimi
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT d.*, u.name, u.email FROM devices d 
        JOIN users u ON d.user_id = u.id";

if ($filter === 'active') {
    $sql .= " WHERE d.return_date IS NULL";
} elseif ($filter === 'returned') {
    $sql .= " WHERE d.return_date IS NOT NULL";
}

$sql .= " ORDER BY d.delivery_date DESC";

$stmt = $pdo->query($sql);
$devices = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Cihaz Takibi | Inhouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h3>🖥️ Cihaz Takibi</h3>

    <div class="mb-3">
        <a href="?filter=all" class="btn btn-secondary btn-sm <?= $filter === 'all' ? 'active' : '' ?>">Tümü</a>
        <a href="?filter=active" class="btn btn-secondary btn-sm <?= $filter === 'active' ? 'active' : '' ?>">Teslim Edilmiş</a>
        <a href="?filter=returned" class="btn btn-secondary btn-sm <?= $filter === 'returned' ? 'active' : '' ?>">İade Edilmiş</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kullanıcı</th>
                    <th>Email</th>
                    <th>Cihaz</th>
                    <th>Seri No</th>
                    <th>Teslim Tarihi</th>
                    <th>İade Tarihi</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['name']) ?></td>
                    <td><?= htmlspecialchars($d['email']) ?></td>
                    <td><?= htmlspecialchars($d['device_type'] . ' - ' . $d['brand_model']) ?></td>
                    <td><?= htmlspecialchars($d['serial_number']) ?></td>
                    <td><?= $d['delivery_date'] ? date('d.m.Y', strtotime($d['delivery_date'])) : '-' ?></td>
                    <td><?= $d['return_date'] ? date('d.m.Y', strtotime($d['return_date'])) : '-' ?></td>
                    <td>
                        <?php if ($d['return_date']): ?>
                            <span class="badge bg-secondary">İade Edildi</span>
                        <?php else: ?>
                            <span class="badge bg-success">Teslim Edildi</span>
                        <?php endif; ?>
                    </td>
                    <td>
    <?php if (!$d['return_date']): ?>
        <!-- İade butonu ve açıklama formu -->
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="showReturnForm(<?= $d['id'] ?>)">İade Al</button>

        <form action="return_device.php" method="POST" class="mt-2 d-none" id="return-form-<?= $d['id'] ?>">
            <input type="hidden" name="device_id" value="<?= $d['id'] ?>">
            <div class="mb-2">
                <textarea name="return_note" class="form-control form-control-sm" placeholder="İade açıklaması giriniz..." required></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-danger">Onayla</button>
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
</div>

<script>
function showReturnForm(id) {
    const form = document.getElementById('return-form-' + id);
    if (form.classList.contains('d-none')) {
        form.classList.remove('d-none');
    } else {
        form.classList.add('d-none');
    }
}
</script>

</body>
</html>
