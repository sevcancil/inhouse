<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'it'])) {
    die("Bu sayfaya erişim yetkiniz yok.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz kullanıcı ID.");
}

$userId = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("Kullanıcı bulunamadı.");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $role       = $_POST['role'] ?? 'personel';
    $birthdate  = $_POST['birthdate'] ?? null;
    $internal_phone = trim($_POST['internal_phone']);
    $mobile_phone   = trim($_POST['mobile_phone']);
    $anydesk    = trim($_POST['anydesk']);
    $password   = $_POST['password'] ?? '';
    $hire_date  = $_POST['hire_date'] ?? null;

    if ($name && $email) {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, department=?, role=?, birthdate=?, internal_phone=?, mobile_phone=?,anydesk=? , hire_date=? WHERE id=?");
            $updateStmt->execute([$name, $email, $hashedPassword, $department, $role, $birthdate, $internal_phone, $mobile_phone, $anydesk, $hire_date, $userId]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET name=?, email=?, department=?, role=?, birthdate=?, internal_phone=?, mobile_phone=?, anydesk=?, hire_date=? WHERE id=?");
            $updateStmt->execute([$name, $email, $department, $role, $birthdate, $internal_phone, $mobile_phone, $anydesk, $hire_date, $userId]);
        }
        $success = "Kullanıcı bilgileri güncellendi.";
    } else {
        $error = "Ad ve e-posta alanları zorunludur.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcıyı Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h3>✏️ Kullanıcıyı Düzenle</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Şifre (boş bırakırsanız değişmez)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Departman</label>
            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Rol</label>
            <select name="role" class="form-select">
                <option value="personel" <?= $user['role'] === 'personel' ? 'selected' : '' ?>>Personel</option>
                <option value="it" <?= $user['role'] === 'it' ? 'selected' : '' ?>>IT</option>
                <option value="manager" <?= $user['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Doğum Tarihi</label>
            <input type="date" name="birthdate" class="form-control" value="<?= $user['birthdate'] ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Dahili Numara</label>
            <input type="text" name="internal_phone" class="form-control" value="<?= htmlspecialchars($user['internal_phone']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Cep Telefonu</label>
            <input type="text" name="mobile_phone" class="form-control" value="<?= htmlspecialchars($user['mobile_phone']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">İşe Giriş Tarihi</label>
            <input type="date" name="hire_date" class="form-control" value="<?= $user['hire_date'] ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Anydesk ID</label>
            <input type="text" name="anydesk" class="form-control" value="<?= htmlspecialchars($user['anydesk']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="user_management.php" class="btn btn-secondary">Geri Dön</a>
    </form>
</div>
</body>
</html>
