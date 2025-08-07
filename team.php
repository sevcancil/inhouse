<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Tüm kullanıcıları veritabanından çekiyoruz
$stmt = $pdo->query("SELECT id, name, department, internal_phone, mobile_phone FROM users ORDER BY name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ekibimiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">👥 Ekibimiz</h2>

    <div class="row">
        <?php foreach ($users as $user): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($user['name']) ?></h5>
                        <p class="card-text">
                            <strong>Departman:</strong> <?= htmlspecialchars($user['department']) ?><br>
                            <strong>Dahili No:</strong> <?= htmlspecialchars($user['internal_phone'] ?? '—') ?><br>
                            <strong>Cep Telefonu:</strong> <?= htmlspecialchars($user['mobile_phone'] ?? '—') ?>
                        </p>
                        <a href="send_message.php?to=<?php echo $user['id']; ?>" class="btn btn-outline-primary">Mesaj Gönder</a>



                        <!-- Bu buton ileride aktif edilecek -->
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
