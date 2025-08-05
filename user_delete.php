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

// Kendi kendini silmeye karşı koruma
if ($_SESSION['user']['id'] == $userId) {
    die("Kendi hesabınızı silemezsiniz.");
}

// Kullanıcı var mı kontrol et
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("Kullanıcı bulunamadı.");
}

// Silme işlemi
$deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$deleteStmt->execute([$userId]);

// Silme sonrası listeye yönlendirme
header("Location: user_management.php?deleted=1");
exit;
