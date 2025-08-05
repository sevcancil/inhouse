<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($user) || !is_array($user)) {
    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    } else {
        header("Location: login.php");
        exit;
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="dashboard.php">Inhouse</a>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item">
            <a class="nav-link text-white" href="profile.php">👤 Profil</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="support.php">🆘 Destek</a>
        </li>

        <?php if (isset($user['role']) && in_array($user['role'], ['it', 'manager'])): ?>
            <li class="nav-item">
                <a class="nav-link text-info" href="device_list.php">📋 Cihaz Takibi</a>
            </li>
        <?php endif; ?>

        <?php if (isset($user['role']) && in_array($user['role'], ['it', 'manager'])): ?>
            <li class="nav-item">
                <a class="nav-link text-info" href="announcements.php">📋 Duyurular</a>
            </li>
        <?php endif; ?>

        <?php if (isset($user['role']) && in_array($user['role'], ['it', 'manager'])): ?>
            <li class="nav-item">
                <a class="nav-link text-info" href="user_management.php">📋 Kullanıcı Ekle</a>
            </li>
        <?php endif; ?>

        <?php if (isset($user['role']) && $user['role'] === 'it'): ?>
            <li class="nav-item">
                <a class="nav-link text-warning" href="it_requests.php">🛠️ IT Talepleri</a>
            </li>
        <?php endif; ?>



        <?php if (isset($user['role']) && $user['role'] === 'it'): ?>
            <li class="nav-item">
                <a class="nav-link text-danger" href="bulk_download.php">🛠️ Tutanakları İndir</a>
            </li>
        <?php endif; ?>

        <li class="nav-item">
            <a class="nav-link text-white" href="logout.php">🚪 Çıkış</a>
        </li>
    </ul>
</nav>
