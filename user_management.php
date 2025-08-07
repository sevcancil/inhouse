<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';


if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['manager', 'it'])) {
    die("Bu sayfaya erişim yetkiniz yok.");
}

// Tüm kullanıcıları al
$stmt = $pdo->query("SELECT id, name, email, department, role, birthdate, anydesk, internal_phone, mobile_phone, hire_date, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Kullanıcı başarıyla silindi.</div>
<?php endif; ?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h3>👤 Kullanıcı Yönetimi</h3>

    <div class="mb-3 text-end">
        <a href="user_add.php" class="btn btn-success">➕ Yeni Kullanıcı Ekle</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Ad Soyad</th>
                <th>Email</th>
                <th>Departman</th>
                <th>Rol</th>
                <th>Doğum Tarihi</th>
                <th>Dahili</th>
                <th>Cep No</th>
                <th>Anydesk</th>
                <th>İşe Giriş Tarihi</th>
                <th>Kalan İzin</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['department']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['birthdate']) ?></td>
                    <td><?= htmlspecialchars($user['internal_phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($user['mobile_phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($user['anydesk']) ?></td>
                    <td><?= htmlspecialchars($user['hire_date']) ?></td>
                    <td>
                        <?php
                        echo getCurrentLeaveBalance($pdo, $user['id'], $user['hire_date']) . ' gün';
                        ?>
                    </td>

                    <td>
                        <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
                        <a href="user_delete.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
