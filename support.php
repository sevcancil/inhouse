<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';
$user = $_SESSION['user'];
$user_id = $user['id'];

// Talep gönderme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $filename = null;

    // Dosya varsa yükle
    if (!empty($_FILES['attachment']['name'])) {
        $uploadDir = 'uploads/';
        $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('talep_', true) . '.' . $ext;
        move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $filename);
    }

    // Veritabanına ekle
    $stmt = $pdo->prepare("INSERT INTO it_requests (requested_by, title, description, attachment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $filename]);

    header("Location: support.php?success=1");
    exit;
}

// Kullanıcının geçmiş taleplerini al
$stmt = $pdo->prepare("SELECT * FROM it_requests WHERE requested_by = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Destek Taleplerim | Inhouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { border-radius: 1rem; }
        .badge { text-transform: capitalize; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>


<div class="container mt-4">
    <h3 class="mb-4">🆘 Destek Taleplerim</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Talebiniz başarıyla gönderildi.</div>
    <?php endif; ?>

    <div class="card p-4 mb-5 shadow-sm">
        <h5 class="mb-3">Yeni Talep Oluştur</h5>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Başlık</label>
                <input type="text" name="title" required class="form-control" placeholder="Sorun Nedir?">
            </div>
            <div class="mb-3">
                <label class="form-label">Açıklama</label>
                <textarea name="description" rows="4" required class="form-control" placeholder="Durum detayını buraya yazın..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Dosya Ekle (Opsiyonel)</label>
                <input type="file" name="attachment" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Talebi Gönder</button>
        </form>
    </div>

    <div class="card p-4 shadow-sm">
        <h5 class="mb-3">📄 Mevcut Talepleriniz</h5>
        <?php if (count($requests) === 0): ?>
            <p class="text-muted">Henüz bir talebiniz yok.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Başlık</th>
                            <th>Durum</th>
                            <th>Teknisyen</th>
                            <th>Yanıt</th>
                            <th>Dosya</th>
                            <th>Oluşturulma</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['title']) ?></td>
                                <td>
                                    <?php
                                    $color = match($r['status']) {
                                        'bekliyor' => 'secondary',
                                        'onaylandı' => 'info',
                                        'tamamlandı' => 'success',
                                        'reddedildi' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= $r['status'] ?></span>
                                </td>
                                <td>
                                    <?php
                                    if ($r['technician_id']) {
                                        $tech = $pdo->prepare("SELECT name FROM users WHERE id = ?");
                                        $tech->execute([$r['technician_id']]);
                                        $techName = $tech->fetchColumn();
                                        echo htmlspecialchars($techName);
                                    } else {
                                        echo "<span class='text-muted'>-</span>";
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($r['response_note'] ?? '-') ?></td>
                                <td>
                                    <?php if ($r['attachment']): ?>
                                        <a href="uploads/<?= $r['attachment'] ?>" target="_blank">Dosya</a>
                                    <?php else: ?>
                                        <span class="text-muted">Yok</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
