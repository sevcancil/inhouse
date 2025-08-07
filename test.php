<?php

$user_id = 1; // kendi ID'n
$stmt = $pdo->prepare("SELECT * FROM leaves WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
foreach ($stmt->fetchAll() as $leave) {
    echo "ID: {$leave['id']} | Type: {$leave['leave_type_id']} | Status: {$leave['status']} | Gün: {$leave['total_days']}<br>";
}
?>