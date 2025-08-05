<?php
session_start();
require 'includes/db.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'it') {
    die("Yetkisiz erişim.");
}

// 1. Cihazları al
$stmt = $pdo->query("SELECT d.*, u.name FROM devices d JOIN users u ON d.user_id = u.id ORDER BY d.delivery_date DESC");
$devices = $stmt->fetchAll();

// 2. DomPDF ayarları
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$zip = new ZipArchive();
$zipFilename = 'tutanaklar_' . date('Ymd_His') . '.zip';
$tmpZipPath = sys_get_temp_dir() . '/' . $zipFilename;

if ($zip->open($tmpZipPath, ZipArchive::CREATE) !== TRUE) {
    die("ZIP dosyası oluşturulamadı.");
}

foreach ($devices as $device) {
    $dompdf = new Dompdf($options);

    // Kullanıcı adı
    $userName = htmlspecialchars($device['name']);

    // PDF içeriğini oluştur
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
    <p><strong>Ad Soyad:</strong> ' . htmlspecialchars($device['name']) . '</p>
    <p><strong>Dijital Olarak Onaylanmıştır</strong> - ' . date('d.m.Y H:i', strtotime($device['delivery_date'])) . '</p>';

if (!empty($device['return_date']) && !empty($device['return_note'])) {
    $html .= '<hr>';
    $html .= '<p><strong>İade Bilgisi:</strong> ' . htmlspecialchars($device['return_note']) . ' şeklinde iade alınmıştır. (' . date('d.m.Y H:i', strtotime($device['return_date'])) . ')</p>';
}

$html .= '
</body>
</html>';


    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4');
    $dompdf->render();

    // PDF'i geçici dosyaya kaydet
    $pdfOutput = $dompdf->output();
    $pdfFilename = 'tutanak_' . preg_replace('/[^a-z0-9]/i', '_', $userName) . '_' . $device['id'] . '.pdf';

    $zip->addFromString($pdfFilename, $pdfOutput);
}

$zip->close();

// 5. ZIP dosyasını indir
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
header('Content-Length: ' . filesize($tmpZipPath));
readfile($tmpZipPath);

// 6. Temizlik
unlink($tmpZipPath);
exit;
