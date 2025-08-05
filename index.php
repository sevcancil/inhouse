<?php
// Eğer kullanıcı zaten giriş yaptıysa dashboard'a gönder, yoksa login'e
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
