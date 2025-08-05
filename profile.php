<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';
$user = $_SESSION['user'];

// Güncel kullanıcı bilgisi (anydesk dahil)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

// Kullanıcının cihazları
$stmt = $pdo->prepare("SELECT * FROM devices WHERE user_id = ?");
$stmt->execute([$user['id']]);
$devices = $stmt->fetchAll();
?>
<?php if (isset($_GET['confirmed'])): ?>
    <div class="alert alert-success">✅ Cihaz başarıyla teslim alındı olarak işaretlendi.</div>
<?php endif; ?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Profilim | Inhouse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { border-radius: 1rem; }
        .badge-pill { border-radius: 50rem; }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>


<div class="container mt-4">
    <h3 class="mb-4">👤 Profil Sayfası</h3>

    <div class="card p-4 mb-4 shadow-sm">
        <h5 class="mb-3">Kullanıcı Bilgileri</h5>
        <p><strong>Ad Soyad:</strong> <?= htmlspecialchars($userData['name']) ?></p>
        <p><strong>E-Posta:</strong> <?= htmlspecialchars($userData['email']) ?></p>
        <p><strong>Departman:</strong> <?= htmlspecialchars($userData['department']) ?></p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($userData['role']) ?></p>
        <p><strong>Doğum Günü:</strong> <?= date('d.m.Y', strtotime($userData['birthdate'])) ?></p>
        <p><strong>Anydesk:</strong> <?= htmlspecialchars($userData['anydesk'] ?? 'Belirtilmemiş') ?></p>
    </div>

    <div class="card p-4 shadow-sm">
        <h5 class="mb-3">🖥️ Teslim Alınan Cihazlar</h5>
        <?php if (count($devices) === 0): ?>
            <p class="text-muted">Kayıtlı cihaz bulunamadı.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Cihaz Tipi</th>
                            <th>Marka / Model</th>
                            <th>Seri No</th>
                            <th>Teslim Tarihi</th>
                            <th>Teslim Onayı</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['device_type']) ?></td>
                                <td><?= htmlspecialchars($d['brand_model']) ?></td>
                                <td><?= htmlspecialchars($d['serial_number']) ?></td>
                                <td><?= $d['delivery_date'] ? date('d.m.Y', strtotime($d['delivery_date'])) : '-' ?></td>
                                <td>
                                    <?php if ($d['confirmation']): ?>
                                        <span class="badge bg-success">✅ Onaylandı</span>
                                        <?php if (!empty($d['return_date'])): ?>
                                            <span class="badge bg-danger ms-1">🔄 İade Edildi</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Modal tetikleyici -->
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#confirmModal<?= $d['id'] ?>">
                                        Onayla
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="confirmModal<?= $d['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $d['id'] ?>" aria-hidden="true">
                                      <div class="modal-dialog modal-dialog-scrollable">
                                        <div class="modal-content">
                                          <form method="POST" action="confirm_device.php">
                                            <div class="modal-header">
                                              <h5 class="modal-title" id="modalLabel<?= $d['id'] ?>">Demirbaş Teslim Sözleşmesi</h5>
                                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                            </div>
                                            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                                              <p><strong>📄 Teslim Edilen Cihaz:</strong></p>
                                              <ul>
                                                <li><strong>Tip:</strong> <?= htmlspecialchars($d['device_type']) ?></li>
                                                <li><strong>Marka / Model:</strong> <?= htmlspecialchars($d['brand_model']) ?></li>
                                                <li><strong>Seri No:</strong> <?= htmlspecialchars($d['serial_number']) ?></li>
                                              </ul>
                                              <hr>
                                              <p>
                                                Teslimat Şartları:
                                                1.  Tarafıma teslim edilen cihazın yukarıda belirtilen özelliklerde ve çalışır durumda olduğunu kabul ediyorum.
                                                2.  Cihazın dış yüzeyine herhangi bir etiket, çıkartma, yazı, işaret veya benzeri iz bırakıcı unsur yapıştırmayacağımı/uygulamayacağımı taahhüt ederim.
                                                3.  Cihazın yalnızca kurumsal kullanım amacıyla kullanılacağını ve izinsiz kurulumlar yapılmayacağını kabul ederim.
                                                4.  Teslim alınan cihazın bakım, onarım, hasar, kayıp gibi durumlarında bağlı bulunduğum birimi bilgilendirmekle yükümlüyüm.
                                                5.Cihazın sorumluluğu tarafıma aittir. Cihazda oluşabilecek fiziksel veya yazılımsal hasarların sorumluluğunu kabul ederim. Gerekli durumlarda cihazı iade etmekle yükümlüyüm.
                                              </p>
                                              <p class="text-muted mt-3"><small>Devam etmek için aşağıdaki butona tıklayarak bu sözleşmeyi kabul ettiğinizi onaylayınız.</small></p>
                                            </div>
                                            <div class="modal-footer">
                                              <input type="hidden" name="device_id" value="<?= $d['id'] ?>">
                                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                              <button type="submit" class="btn btn-primary">📌 Teslim Aldım</button>
                                            </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="generate_pdf.php?device_id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank">📄 PDF</a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
