<?php
session_start();
require_once 'includes/db.php';

// Giriş kontrolü
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Sabit şifreleme anahtarı (32 karakter = 256 bit)
define('ENCRYPTION_KEY', 'mySuperSecureStaticKey123456789012'); // .env dosyasına taşıman daha güvenlidir

function encryptMessage($plaintext) {
    $iv = openssl_random_pseudo_bytes(16);
    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
    return [
        'ciphertext' => $ciphertext,
        'iv' => base64_encode($iv)
    ];
}


// Alıcı ID'si URL'den gelsin
$receiver_id = $_GET['to'] ?? null;
if (!$receiver_id || !is_numeric($receiver_id)) {
    die("Geçersiz kullanıcı.");
}

// Form gönderimi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user']['id'];
    $receiver_id = $_POST['receiver_id'];
    $plaintext = trim($_POST['message']);

    if ($plaintext === '') {
        $error = "Mesaj boş olamaz.";
    } else {
        $encrypted = encryptMessage($plaintext);
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, iv) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sender_id, $receiver_id, $encrypted['ciphertext'], $encrypted['iv']]);

        }
    }
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Mesaj Gönder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h4>✉️ Mesaj Gönder</h4>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success">Mesaj başarıyla gönderildi.</div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($receiver_id) ?>">
        <div class="mb-3">
            <label for="message" class="form-label">Mesajınız</label>
            <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Gönder</button>
    </form>
</div>
</body>
</html>
