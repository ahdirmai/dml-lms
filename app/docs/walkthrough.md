# Panduan Walkthrough Aplikasi LMS

Dokumentasi ini berisi panduan langkah demi langkah untuk menggunakan aplikasi Learning Management System (LMS) ini. Panduan ini mencakup alur kerja untuk **Superadmin**, **Instructor**, dan **User (Student)**.

## Peran dan Akses

Aplikasi ini memiliki 3 role utama:

-   **Superadmin**: Memiliki akses penuh ke sistem, manajemen user, course, dan pengaturan sistem.
-   **Instructor**: Dapat membuat dan mengelola course mereka sendiri, serta memantau progress siswa.
-   **User / Student**: Peserta yang ditugaskan (assigned) untuk mengikuti course.

---

## 1. Alur Superadmin

Superadmin bertanggung jawab untuk inisialisasi sistem, manajemen user, dan taksonomi (kategori/tag) sebelum course dapat dibuat.

### A. Login Awal

Secara default, jika sistem baru di-install (seed), gunakan akun berikut:

-   **Email**: `admin@dml-lms.test`
-   **Password**: `password`

### B. Membuat Akun Pengguna (Instructor & Student)

Sebelum membuat course, pastikan ada Instructor (jika course dibuat oleh instructor lain) dan Student.

1. Masuk ke **Menu Admin > RBAC > Users**.
2. Klik tombol **Create New User**.
3. Isi form:
    - **Name**: Nama lengkap pengguna.
    - **Email**: Alamat email unik.
    - **Password**: Password (minimal 8 karakter).
    - **Roles**: Pilih role yang sesuai (`Instructor` atau `Student`). _Note: Defaultnya user akan mendapat role Student jika tidak dipilih._
4. Klik **Simpan**.

### C. Manajemen Kategori & Tags (Taxonomy)

Course membutuhkan kategori dan tag untuk pengelompokan.

**Membuat Kategori:**

1. Masuk ke **Menu Admin > Taxonomy > Categories**.
2. Klik **Create New Category**.
3. Isi **Name** kategori (contoh: _Leadership_, _Programming_, _Safety_).
4. Klik **Save**.

**Membuat Tags:**

1. Masuk ke **Menu Admin > Taxonomy > Tags**.
2. Klik **Create New Tag**.
3. Isi **Name** tag (contoh: _Mandatory_, _2024_, _IT_).
4. Klik **Save**.

### D. Membuat Course (Sebagai Admin)

Admin bisa membuat course untuk siapa saja.

1. Masuk ke **Menu Admin > Courses**.
2. Klik **Create New Course**.
3. **Drafting (Detail Dasar):**
    - **Title**: Judul Course.
    - **Category**: Pilih kategori yang sudah dibuat.
    - **Instructor**: Pilih instructor penanggung jawab.
    - **Description**: Deskripsi lengkap course.
    - **Flags**:
        - _Has Pretest/Posttest_: Centang jika course memiliki ujian.
        - _Using Due Date_: Centang jika course memiliki tenggat waktu pengerjaan.
4. Klik **Create Course**. Anda akan diarahkan ke halaman **Course Builder**.
5. **Course Builder (Curriculum):**
    - **Add Module**: Tambahkan modul (Bab) terlebih dahulu.
    - **Add Lesson**: Di dalam modul, tambahkan lesson (Materi). Bisa berupa Video atau Text.
    - **Add Quiz**: Tambahkan kuis pada lesson atau sebagai Pre/Post-test di level course.
6. **Publishing**:
    - Pastikan minimal ada 1 Module dan 1 Lesson.
    - Klik tombol **Publish** di pojok kanan atas.

### E. Assign User ke Course

User tidak mendaftar sendiri (self-enroll), melainkan harus ditugaskan (assigned).

1. Buka halaman detail course atau dari list course.
2. Klik menu **Assign Students**.
3. Cari nama student di daftar "Available Users".
4. Centang student yang ingin ditugaskan.
5. (Opsional) Jika _Using Due Date_ aktif, set tanggal **Start Date** dan **End Date** untuk masing-masing user.
6. Klik **Assign**.

---

## 2. Alur Instructor

Instructor fokus pada pembuatan konten pembelajaran dan pemantauan siswa mereka.

### A. Login

Masuk menggunakan akun dengan role Instructor yang telah dibuat oleh Admin.

-   Dashboard akan menampilkan ringkasan course yang dikelola.

### B. Membuat Course

Langkah ini mirip dengan Superadmin, namun Instructor otomatis terpilih sebagai pemilik course.

1. Masuk ke **Menu Instructor > Courses**.
2. Klik **Create New Course**.
3. Isi detail course (Judul, Deskripsi, Kategori, Level).
4. Simpan, lalu masuk ke **Course Builder**.
5. Susun materi (Modules & Lessons).
6. **Publish** course agar siap di-assign.

### C. Mengelola Siswa & Progress

1. Masuk ke menu **Course > Assign Students** untuk menambahkan siswa ke course Anda.
2. Masuk ke menu **Progress** pada course tertentu untuk melihat:
    - Siapa yang sudah menyelesaikan course.
    - Nilai Pretest/Posttest.
    - Sertifikat siswa (jika ada).
3. Anda bisa mengekspor nilai (Export Scores) ke Excel untuk pelaporan.

---

## 3. Alur User / Student

User adalah peserta yang mengikuti pembelajaran.

### A. Dashboard & My Courses

1. Login sebagai student.
2. Di **Dashboard**, user akan melihat ringkasan aktivitas.
3. Masuk ke menu **My Courses**.
    - Di sini muncul daftar course yang telah di-assign oleh Admin/Instructor.
    - Status course: _In Progress_, _Completed_, atau _Not Started_.

### B. Melakukan Pembelajaran (Taking Course)

1. Klik salah satu course di _My Courses_.
2. User akan masuk ke **Course Player**.
3. **Alur Belajar**:
    - Jika ada **Pretest** (dan wajib), user harus mengerjakannya terlebih dahulu sebelum bisa membuka materi.
    - User membuka **Lesson** satu per satu (Video/Bacaan).
    - Klik **Mark as Complete** atau selesaikan video untuk mencatat progress.
    - Kerjakan kuis per lesson jika ada.
4. **Penyelesaian**:
    - Setelah semua materi selesai, kerjakan **Posttest** (jika ada).
    - Jika lulus, progress menjadi 100%.

### C. Sertifikat & Review

1. Setelah course selesai (100%), tombol **Download Certificate** akan aktif (jika fitur sertifikat diaktifkan).
2. User dapat memberikan **Review** (Rating bintang) untuk course tersebut.

---

## Ringkasan Fitur Utama

| Fitur                      | Superadmin |    Instructor    |  Student  |
| :------------------------- | :--------: | :--------------: | :-------: |
| **Manage Users**           | ✅ (Full)  |        ❌        |    ❌     |
| **Manage Categories/Tags** |     ✅     |        ✅        |    ❌     |
| **Create/Edit Course**     |     ✅     |     ✅ (Own)     |    ❌     |
| **Publish Course**         |     ✅     |        ✅        |    ❌     |
| **Assign Students**        |     ✅     |        ✅        |    ❌     |
| **View Progress**          |  ✅ (All)  | ✅ (Own Courses) | ✅ (Self) |
| **Export Scores**          |     ✅     |        ✅        |    ❌     |
| **Take Course**            |     ❌     |        ❌        |    ✅     |
