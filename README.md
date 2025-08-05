# Inhouse - Ofis İçi Personel Yönetim Sistemi

Inhouse, şirket içi personel, ekipman ve görev takibini kolaylaştırmak için geliştirilen PHP tabanlı bir web uygulamasıdır. Sistem; kullanıcı yönetimi, cihaz teslim ve iade işlemleri, görev atama, duyuru yönetimi ve doğum günü bildirimleri gibi birçok modül içerir.

## 🔧 Özellikler

- 📋 Görev atama ve takip sistemi
- 💬 Rol bazlı duyuru yayınlama (IT & Manager)
- 🖥️ Cihaz teslim ve iade tutanakları (PDF formatında)
- 🎂 Doğum günü hatırlatmaları
- 👥 Kullanıcı yönetimi (ekle/düzenle/sil)
- 📄 Toplu veya bireysel PDF oluşturma
- 🔐 Rol tabanlı yetkilendirme
- 🧩 Anydesk ID takibi

## 🛠️ Kullanılan Teknolojiler

- PHP 8.x
- MySQL
- Bootstrap 5
- DomPDF (PDF oluşturma)
- XAMPP (geliştirme ortamı)

## 🧑‍💻 Kurulum

1. Bu projeyi klonlayın:
   ```bash
   git clone https://github.com/kullaniciadi/inhouse.git
Veritabanınızı oluşturun ve users, announcements, tasks, devices vb. tabloları içeren SQL dosyasını içeri aktarın.

includes/db.php dosyasında veritabanı bağlantı bilgilerinizi ayarlayın.

DomPDF kütüphanesini kurun:
composer require dompdf/dompdf

XAMPP veya benzeri bir sunucu ile çalıştırın:
http://localhost/inhouse/ adresinden uygulamayı görüntüleyebilirsiniz.

🧩 Geliştirme Notları
Bu sistem zamanla geliştirilmeye ve yeni özellikler eklenmeye devam edecektir. Planlanan özellikler:

Bildirim sistemi

Mobil uyumlu arayüz

Gelişmiş raporlama ve filtreleme seçenekleri

Kullanıcı aktivite logları

Site içi mesajlaşma

👨‍💼 Rollere Göre Yetkiler

IT ---	Kullanıcı yönetimi, duyuru ekleme, cihaz işlemleri
Manager ---	Görev ve duyuru yönetimi, kullanıcı yönetimi
Personel ---	Görevleri görüntüleme, cihaz teslimi
