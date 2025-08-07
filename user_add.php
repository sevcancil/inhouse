<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'it'])) {
    die("Bu sayfaya erişim yetkiniz yok.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);
    $department = trim($_POST['department']);
    $role       = $_POST['role'] ?? 'personel';
    $birthdate  = $_POST['birthdate'] ?? null;
    $internal_phone = trim($_POST['internal_phone']);
    $mobile_phone   = trim($_POST['mobile_phone']);
    $hire_date  = $_POST['hire_date'] ?? null;
    $anydesk    = trim($_POST['anydesk']);

    if ($name && $email && $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, department, role, birthdate, internal_phone, mobile_phone, hire_date, anydesk) VALUES (?, ?, ?, ?, ?, ?, ? ,? , ? , ?)");
        try {
            $stmt->execute([$name, $email, $hashedPassword, $department, $role, $birthdate, $internal_phone, $mobile_phone, $hire_date, $anydesk]);
            $success = "Kullanıcı başarıyla eklendi.";
        } catch (PDOException $e) {
            $error = "Hata oluştu: " . $e->getMessage();
        }
    } else {
        $error = "Ad, e-posta ve şifre alanları zorunludur.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Kullanıcı Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h3>➕ Yeni Kullanıcı Ekle</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Şifre</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Departman</label>
            <input type="text" name="department" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Rol</label>
            <select name="role" class="form-select">
                <option value="personel">Personel</option>
                <option value="it">IT</option>
                <option value="manager">Manager</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Doğum Tarihi</label>
            <input type="date" name="birthdate" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Dahili Numara</label>
            <input type="text" name="internal_phone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Cep Telefonu</label>
            <input type="text" name="mobile_phone" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">İşe Giriş Tarihi</label>
            <input type="date" name="hire_date" class="form-control">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Anydesk ID</label>
            <input type="text" name="anydesk" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Kaydet</button>
        <a href="user_management.php" class="btn btn-secondary">İptal</a>
    </form>
</div>
</body>
</html>
