<?php
// includes/update_total_days.php  gibi bir dosyada çalıştırabilirsin

require_once 'db.php';
require_once 'functions.php';

$stmt = $pdo->query("SELECT id, start_date, end_date FROM leaves WHERE total_days = 0 OR total_days IS NULL");
$leaves = $stmt->fetchAll();

foreach ($leaves as $leave) {
    $total = calculateTotalLeaveDays($leave['start_date'], $leave['end_date']);
    $update = $pdo->prepare("UPDATE leaves SET total_days = ? WHERE id = ?");
    $update->execute([$total, $leave['id']]);
}

echo "Toplam izin günleri başarıyla hesaplandı ve güncellendi.";

?>
