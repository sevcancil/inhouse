<?php
// leave_request.php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$types = $pdo->query("SELECT id, name FROM leave_types")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user']['id'];
    $type_id = $_POST['leave_type_id'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $reason = $_POST['reason'];

    $total_days = calculateTotalLeaveDays($start, $end);

    $stmt = $pdo->prepare("INSERT INTO leaves (user_id, leave_type_id, start_date, end_date, total_days, reason, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([$user_id, $type_id, $start, $end, $total_days, $reason]);

    header("Location: leave_request.php?success=1");
    exit;
}
?>

<!-- HTML kısmı -->
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İzin Başvurusu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container mt-4">
    <h4>📝 İzin Başvuru Formu</h4>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">İzin başvurusu başarıyla kaydedildi.</div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="leave_type_id" class="form-label">İzin Türü</label>
            <select name="leave_type_id" id="leave_type_id" class="form-select" required>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="start_date" class="form-label">Başlangıç Tarihi</label>
            <input type="date" name="start_date" id="start_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="end_date" class="form-label">Bitiş Tarihi</label>
            <input type="date" name="end_date" id="end_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="reason" class="form-label">Açıklama</label>
            <textarea name="reason" id="reason" rows="3" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Başvuru Gönder</button>
    </form>
</div>
</body>
</html>
