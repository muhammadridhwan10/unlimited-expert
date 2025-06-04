<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;

class IndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indicators = [
            // Performance Proyek
            ['category' => 'Performance Proyek', 'name' => 'Jumlah proyek yang ditangani', 'weight' => 10],
            ['category' => 'Performance Proyek', 'name' => 'Persentase proyek selesai tepat waktu', 'weight' => 5],
            ['category' => 'Performance Proyek', 'name' => 'Kualitas deliverables', 'weight' => 5],
            ['category' => 'Performance Proyek', 'name' => 'Kepuasan klien', 'weight' => 5],

            // Kompetensi Teknis
            ['category' => 'Kompetensi Teknis', 'name' => 'Ketepatan & kejelasan rekomendasi', 'weight' => 5],
            ['category' => 'Kompetensi Teknis', 'name' => 'Keakuratan hasil kerja', 'weight' => 5],
            ['category' => 'Kompetensi Teknis', 'name' => 'Kelengkapan dokumentasi', 'weight' => 5],
            ['category' => 'Kompetensi Teknis', 'name' => 'Kemampuan berkomunikasi', 'weight' => 5],
            ['category' => 'Kompetensi Teknis', 'name' => 'Kemampuan berbahasa asing', 'weight' => 5],

            // Sikap Kerja
            ['category' => 'Sikap Kerja', 'name' => 'Kedisiplinan & ketepatan waktu', 'weight' => 5],
            ['category' => 'Sikap Kerja', 'name' => 'Responsif terhadap atasan & klien', 'weight' => 5],
            ['category' => 'Sikap Kerja', 'name' => 'Inisiatif & problem solving', 'weight' => 5],
            ['category' => 'Sikap Kerja', 'name' => 'Memiliki Jiwa Kepemimpinan', 'weight' => 5],
            ['category' => 'Sikap Kerja', 'name' => 'Memiliki Sikap Profesionalisme', 'weight' => 5],

            // Teamwork
            ['category' => 'Teamwork', 'name' => 'Kerjasama dalam tim', 'weight' => 5],
            ['category' => 'Teamwork', 'name' => 'Kontribusi dalam diskusi & brainstorming', 'weight' => 5],

            // Pengembangan Diri
            ['category' => 'Pengembangan Diri', 'name' => 'Keikutsertaan pelatihan', 'weight' => 5],
            ['category' => 'Pengembangan Diri', 'name' => 'Peningkatan skill/progress peran', 'weight' => 5],

            // Kemampuan Lainnya
            ['category' => 'Kemampuan Lainnya', 'name' => 'Kemampuan membangun relasi klien', 'weight' => 2.5],
            ['category' => 'Kemampuan Lainnya', 'name' => 'Pemanfaatan tools / teknologi terkini', 'weight' => 2.5],
        ];

        foreach ($indicators as $indicator) {
            Attribute::create($indicator);
        }
    }
}
