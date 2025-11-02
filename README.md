# 🧑‍💻 DML LMS — Developer-Facing Documentation

> Dokumentasi ini berfungsi sebagai panduan bagi **developer** yang akan berkontribusi, melakukan setup lokal, memahami struktur project, serta menjalankan testing dan seeding pada sistem **DML Learning Management System (LMS)**.

---

## 1. 🧩 CONTRIBUTING.md — Panduan Kontribusi Developer

### 1.1 Prasyarat
Pastikan kamu sudah menginstal dependensi berikut:
- **PHP** ≥ 8.2 dengan ekstensi: `pdo_mysql`, `mbstring`, `intl`, `zip`, `fileinfo`
- **Composer** ≥ v2.5
- **Node.js** ≥ 18 + **npm** atau **pnpm**
- **MySQL** ≥ 8.0 atau **MariaDB** ≥ 10.6
- **Redis** (opsional, untuk queue)
- **Git** (untuk version control)
- **Laravel CLI** global (opsional)

### 1.2 Setup Lokal

```bash
# 1️⃣ Clone repo
git clone https://github.com/dml-co/dml-lms.git
cd dml-lms

# 2️⃣ Install dependensi
composer install
npm install && npm run dev

# 3️⃣ Salin dan konfigurasi environment
cp .env.example .env
php artisan key:generate

# 4️⃣ Jalankan migrasi & seeder
php artisan migrate:fresh --seed

# 5️⃣ Jalankan server lokal
php artisan serve

# 6️⃣ (Opsional) Jalankan queue & schedule
php artisan queue:work
php artisan schedule:work
```

> Default server berjalan di **http://localhost:8000**

### 1.3 Struktur Branch
- `main` → Stable production branch
- `develop` → Active development branch
- `feature/*` → Pengembangan fitur baru
- `fix/*` → Perbaikan bug minor
- `docs/*` → Update dokumentasi

### 1.4 Aturan Kontribusi
1. Gunakan **feature branch** untuk perubahan baru.
2. Pastikan **test lulus (`php artisan test`)** sebelum PR.
3. Ikuti konvensi **PSR-12** dan **naming Laravel**.
4. Sertakan deskripsi detail di Pull Request (PR).

### 1.5 Testing Sebelum Push
```bash
php artisan test
php artisan test --filter=QuizService
```
Gunakan `--coverage` untuk melihat cakupan uji.

---

## 2. 📘 README.md (Root Repo)

### 2.1 Ringkasan
**DML LMS** adalah platform pembelajaran berbasis web untuk manajemen kursus, modul, pelajaran, kuis, progres, dan sertifikasi internal perusahaan.

### 2.2 Fitur Utama
- 🎓 Course & Module Management (CRUD + publish system)
- 🧑‍🏫 Lesson Builder (text/video/file)
- 🧮 Quiz System (auto-scoring & attempts)
- 🏅 Certificate Generator (PDF via queue)
- 👥 Role-based Access (Admin, Instructor, Student)
- 💬 Discussion Forum (nested thread)
- 🕒 Progress Tracking & Analytics Dashboard

### 2.3 Instalasi Cepat
```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

### 2.4 Kredensial Dummy (Seeder)
| Role | Email | Password |
|------|--------|-----------|
| Admin | admin@dml.co.id | password |
| Instructor | instructor@dml.co.id | password |
| Student | student@dml.co.id | password |

### 2.5 Struktur Direktori
```
app/
 ├── Http/
 │   ├── Controllers/
 │   ├── Middleware/
 │   └── Requests/
 ├── Models/
 ├── Services/
 ├── Policies/
 ├── Jobs/
 └── Providers/
resources/
 ├── views/ (Blade templates)
 ├── js/ (Alpine.js components)
 └── css/ (Tailwind source)
routes/
 ├── web.php
 ├── api.php (opsional, internal use)
database/
 ├── migrations/
 ├── factories/
 └── seeders/
```

### 2.6 Command Artisan Penting
| Command | Fungsi |
|----------|--------|
| `php artisan migrate:fresh --seed` | Reset DB dan isi data awal |
| `php artisan optimize` | Cache config, route, dan view |
| `php artisan queue:work` | Jalankan worker antrian |
| `php artisan storage:link` | Buat symbolic link ke storage publik |
| `php artisan test` | Jalankan pengujian unit & fitur |

### 2.7 Dokumentasi Lain
| Dokumen | Keterangan |
|----------|------------|
| `DML LMS — Database Schema Doc.md` | Struktur & relasi DB |
| `DML LMS — Deployment Guide.md` | Panduan server & Docker |
| `DML LMS — Testing Plan.md` | Strategi pengujian |
| `DML LMS — Security Policy.md` | Kebijakan keamanan |

---

## 3. ⚙️ ENV Configuration Guide

Berikut variabel `.env` yang **wajib dikonfigurasi** sebelum menjalankan aplikasi:

### 3.1 Aplikasi
```
APP_NAME="DML LMS"
APP_ENV=local
APP_KEY=base64:xxxx
APP_DEBUG=true
APP_URL=http://localhost:8000
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### 3.2 Database
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dml_lms
DB_USERNAME=root
DB_PASSWORD=
```

### 3.3 Cache & Queue
```
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
```

> Gunakan `redis` jika tersedia untuk kinerja lebih baik.

### 3.4 Mail
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@dml.co.id"
MAIL_FROM_NAME="DML LMS"
```

### 3.5 Filesystem (Storage)
```
FILESYSTEM_DISK=public
# Atau gunakan AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=dml-lms-bucket
```

### 3.6 Keamanan
```
SESSION_SECURE_COOKIE=true
SESSION_SAMESITE=lax
APP_DEBUG=false
```

---

## 4. 🌱 Seeder Data Reference

### 4.1 Seeder Utama
Seeder utama bernama **`LmsFullSeeder`** yang memanggil beberapa seeder lain:
```php
$this->call([
    RoleSeeder::class,
    UserSeeder::class,
    CategorySeeder::class,
    CourseSeeder::class,
    ModuleSeeder::class,
    LessonSeeder::class,
    QuizSeeder::class,
    EnrollmentSeeder::class,
    ProgressSeeder::class,
]);
```

### 4.2 RoleSeeder
Membuat role default:
```php
['admin', 'instructor', 'student']
```
Semua role diatur dengan permission Spatie sesuai modul.

### 4.3 UserSeeder
Membuat akun contoh berikut:
| Role | Email | Password | Keterangan |
|------|--------|-----------|------------|
| Admin | admin@dml.co.id | password | Dapat mengelola seluruh konten |
| Instructor | instructor@dml.co.id | password | Mengajar & membuat kursus |
| Student | student@dml.co.id | password | Mengikuti kursus & ujian |

### 4.4 CourseSeeder & ModuleSeeder
- Membuat minimal 3 kursus: “Basic Programming”, “Web Development”, “Data Analytics”  
- Tiap kursus memiliki 2–3 modul, misalnya: *Introduction*, *Core Topics*, *Final Project*

### 4.5 LessonSeeder
- Mengisi setiap modul dengan 3–5 lesson (tipe text/video/file).
- Menambahkan konten contoh:
  ```php
  Lesson::factory()->create([
    'title' => 'Introduction to PHP',
    'type' => 'video',
    'content_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
  ]);
  ```

### 4.6 QuizSeeder
- Setiap kursus memiliki minimal 1 quiz.
- Quiz terdiri dari 5 pertanyaan `multiple choice`.
- 1–2 opsi benar, skor otomatis dihitung.

### 4.7 EnrollmentSeeder
- Mendaftarkan setiap student ke 1–2 kursus acak.

### 4.8 ProgressSeeder
- Menandai sebagian lesson sebagai `completed` untuk menampilkan progres.

### 4.9 CertificateSeeder (opsional)
- Menghasilkan sertifikat otomatis untuk kursus yang telah 100% selesai.

### 4.10 Tips Testing Seeder
```bash
php artisan db:seed --class=LmsFullSeeder
php artisan tinker
>>> App\Models\Course::count();
```

---

## ✅ Kesimpulan
Dokumen ini mencakup seluruh panduan developer-facing utama untuk DML LMS:  
- Kontribusi dan setup dev environment  
- Instalasi & struktur project  
- Konfigurasi `.env` dan variabel penting  
- Referensi seeder data dummy lengkap

> Dengan mengikuti panduan ini, developer baru dapat menjalankan proyek hanya dalam **<10 menit** dengan setup yang aman, konsisten, dan siap dikembangkan lebih lanjut.

---
© 2025 DML — Developer Documentation
