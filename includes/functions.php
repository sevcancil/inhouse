<?php
// Belirli bir hizmet yılına göre yıllık izin hakkını hesaplar
function getLeaveDaysForServiceYear($years_of_service) {
    if ($years_of_service < 1) {
        return 0;
    } elseif ($years_of_service <= 5) {
        return 14;
    } elseif ($years_of_service <= 15) {
        return 20;
    } else {
        return 26;
    }
}

// Başlangıç ve bitiş tarihlerine göre izin süresi hesaplar
function calculateTotalLeaveDays($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    if ($end < $start) {
        return 0;
    }

    return $start->diff($end)->days + 1;
}

// Negatif bakiyeleri mahsup eden, artanları sıfırlayan yıllık izin sistemi
function getCurrentLeaveBalance($pdo, $user_id, $hire_date) {
    $today = new DateTime();
    $start = new DateTime($hire_date);
    $balance = 0;
    $year = 1;

    while (true) {
        $anniversary = clone $start;
        $anniversary->modify("+$year year");

        if ($anniversary > $today) {
            break;
        }

        // Kıdeme göre o dönemdeki izin hakkı
        $years_of_service = $anniversary->diff($start)->y;
        $leave_right = getLeaveDaysForServiceYear($years_of_service);

        // Bu yıl dönümü ile bir sonraki yıl dönümü arasındaki kullanılan izin
        $next_anniversary = clone $anniversary;
        $next_anniversary->modify('+1 year');

        $stmt = $pdo->prepare("
            SELECT SUM(total_days) 
            FROM leaves 
            WHERE user_id = ? 
              AND status = 'approved' 
              AND start_date >= ? 
              AND start_date < ?
        ");
        $stmt->execute([
            $user_id,
            $anniversary->format('Y-m-d'),
            $next_anniversary->format('Y-m-d')
        ]);

        $used = (float)($stmt->fetchColumn() ?? 0);

        // Devretme yok, sadece negatif bakiye birikir
        $delta = $leave_right - $used;
        if ($delta < 0) {
            $balance += $delta; // sadece negatif değerler eklenir
        }
        elseif ($delta >= 0) {
             // Hizmet süresi yıl yıl artıyor, bu yüzden her yıl için yıl sayısını alıp izin süresini çekiyoruz
            $years_of_service = $year; // Örneğin 1. yıl, 2. yıl...
            $balance = getLeaveDaysForServiceYear($years_of_service);
}
        $year++;
    }

    return $balance;
}



//Şifreleme Mesajlar İçin
define('ENCRYPTION_KEY', 'inhouse_secret_2025'); // sabit anahtar

function encryptMessage($message) {
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($message, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
    return [
        'ciphertext' => $encrypted,
        'iv' => base64_encode($iv)
    ];
}

function decryptMessage($encrypted, $iv_base64) {
    $iv = base64_decode($iv_base64);
    return openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
}


?>
