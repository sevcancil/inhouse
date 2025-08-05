<?php
session_start();
require 'includes/db.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Güvenlik kontrolü
if (!isset($_SESSION['user']) || !isset($_GET['device_id'])) {
    die("Yetkisiz erişim.");
}

$user = $_SESSION['user'];
$deviceId = (int) $_GET['device_id'];

// Cihaz gerçekten bu kullanıcıya mı ait?
$stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ? AND user_id = ?");
$stmt->execute([$deviceId, $user['id']]);
$device = $stmt->fetch();

if (!$device) {
    die("Bu cihaza erişiminiz yok.");
}

// DomPDF ayarları (Türkçe için font desteği)
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// HTML içeriğini başlat
$html = '
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "DejaVu Sans", sans-serif; }
        h2 { text-align: center; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h2>STH DEMİRBAŞ TESLİM TUTANAĞI</h2>
    <hr>
    <p><strong>Teslim Edilen Cihaz:</strong></p>
    <ul>
        <li><strong>Tip:</strong> ' . htmlspecialchars($device['device_type']) . '</li>
        <li><strong>Marka / Model:</strong> ' . htmlspecialchars($device['brand_model']) . '</li>
        <li><strong>Seri No:</strong> ' . htmlspecialchars($device['serial_number']) . '</li>
        <li><strong>Teslim Tarihi:</strong> ' . date('d.m.Y', strtotime($device['delivery_date'])) . '</li>
    </ul>
    <hr>
    <p>
        <strong>Teslimat Şartları:</strong><br><br>
        1. Tarafıma teslim edilen cihazın yukarıda belirtilen özelliklerde ve çalışır durumda olduğunu kabul ediyorum.<br>
        2. Cihazın dış yüzeyine herhangi bir etiket, çıkartma, yazı, işaret veya benzeri iz bırakıcı unsur yapıştırmayacağımı/uygulamayacağımı taahhüt ederim.<br>
        3. Cihazın yalnızca kurumsal kullanım amacıyla kullanılacağını ve izinsiz kurulumlar yapılmayacağını kabul ederim.<br>
        4. Teslim alınan cihazın bakım, onarım, hasar, kayıp gibi durumlarında bağlı bulunduğum birimi bilgilendirmekle yükümlüyüm.<br>
        5. Cihazın sorumluluğu tarafıma aittir. Cihazda oluşabilecek fiziksel veya yazılımsal hasarların sorumluluğunu kabul ederim. Gerekli durumlarda cihazı iade etmekle yükümlüyüm.
    </p>
    <hr>
    <p><strong>Ad Soyad:</strong> ' . htmlspecialchars($user['name']) . '</p>
    <p><strong>Dijital Olarak Onaylanmıştır</strong> - ' . date('d.m.Y H:i') . '</p>
';

// ✅ İade bilgisi varsa, onu da ekle
if (!empty($device['return_date']) && !empty($device['return_note'])) {
    $html .= '
    <hr>
    <p><strong>İade Bilgisi:</strong> ' . htmlspecialchars($device['return_note']) . ' şeklinde iade alınmıştır. (' . date('d.m.Y H:i', strtotime($device['return_date'])) . ')</p>';
}

// HTML'i kapat
$html .= '
</body>
</html>';

// PDF oluştur
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("teslim_tutanagi.pdf", ["Attachment" => true]);
exit;
