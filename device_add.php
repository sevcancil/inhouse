<?php
session_start();
require_once 'includes/db.php';

// Sadece IT kullanıcıları erişebilsin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'it') {
    die("Bu sayfaya erişim yetkiniz yok.");
}

$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $device_type = $_POST['device_type'];
    $brand_model = $_POST['brand_model'];
    $serial_number = $_POST['serial_number'];
    $delivery_date = $_POST['delivery_date'];

    if ($user_id && $device_type && $brand_model && $serial_number && $delivery_date) {
        $stmt = $pdo->prepare("INSERT INTO devices (user_id, device_type, brand_model, serial_number, delivery_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $device_type, $brand_model, $serial_number, $delivery_date]);
        $success = "Cihaz başarıyla eklendi.";
    } else {
        $error = "Tüm alanlar doldurulmalıdır.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Cihaz Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h3>🖥️ Yeni Cihaz Ekle</h3>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="user_id" class="form-label">Kullanıcı</label>
            <select name="user_id" id="user_id" class="form-select" required>
                <option value="">Seçiniz</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="device_type" class="form-label">Cihaz Türü</label>
            <input type="text" name="device_type" id="device_type" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="brand_model" class="form-label">Marka / Model</label>
            <input type="text" name="brand_model" id="brand_model" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="serial_number" class="form-label">Seri Numarası</label>
            <input type="text" name="serial_number" id="serial_number" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="delivery_date" class="form-label">Teslim Tarihi</label>
            <input type="date" name="delivery_date" id="delivery_date" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Cihazı Kaydet</button>
    </form>
</div>
</body>
</html>
