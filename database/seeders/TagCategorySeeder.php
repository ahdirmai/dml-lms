<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lms\Category as LmsCategory;
use App\Models\Lms\Tag as LmsTag;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data Kategori, Subkategori/Tag, dan Deskripsi
        $data = [
            [
                "kategori_utama" => "Teknologi & IT",
                "deskripsi" => "Kursus yang mencakup pengembangan perangkat lunak, infrastruktur digital, dan solusi teknologi terkini.",
                "subkategori" => ["Pemrograman Web", "Data Science", "Cyber Security", "Cloud Computing"],
            ],
            [
                "kategori_utama" => "Bisnis & Pemasaran",
                "deskripsi" => "Pelatihan untuk meningkatkan kemampuan dalam strategi bisnis, manajemen, penjualan, dan pemasaran digital.",
                "subkategori" => ["Digital Marketing", "Kewirausahaan", "Analisis Keuangan", "Manajemen Proyek"],
            ],
            [
                "kategori_utama" => "Kreativitas & Desain",
                "deskripsi" => "Fokus pada pengembangan keterampilan visual dan kreatif, dari desain produk hingga produksi media.",
                "subkategori" => ["UI/UX Design", "Desain Grafis", "Video Editing", "Fotografi"],
            ],
            [
                "kategori_utama" => "Bahasa & Komunikasi",
                "deskripsi" => "Kursus untuk menguasai bahasa asing dan meningkatkan kemampuan berbicara serta berinteraksi secara efektif.",
                "subkategori" => ["Bahasa Inggris (TOEFL/IELTS)", "Bahasa Asing Lain", "Public Speaking", "Negosiasi"],
            ],
            [
                "kategori_utama" => "Keterampilan Profesional",
                "deskripsi" => "Pelatihan untuk meningkatkan kompetensi umum yang dibutuhkan di lingkungan kerja profesional dan administrasi.",
                "subkategori" => ["Microsoft Excel", "Leadership", "Manajemen Waktu", "Administrasi Perkantoran"],
            ]
        ];

        // Looping untuk membuat Kategori Utama (LmsCategory) dan Tag (LmsTag)
        foreach ($data as $item) {
            $categoryName = $item['kategori_utama'];

            // 1. Membuat Kategori Utama (LmsCategory)
            $category = LmsCategory::create([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'description' => $item['deskripsi'], // Menggunakan deskripsi yang sudah ditentukan
            ]);

            // 2. Membuat Tag (LmsTag) dari Subkategori
            foreach ($item['subkategori'] as $tagName) {
                LmsTag::create([
                    'id' => (string) Str::uuid(),
                    'name' => $tagName,
                    'slug' => Str::slug($tagName),
                ]);
            }
        }
    }
}
