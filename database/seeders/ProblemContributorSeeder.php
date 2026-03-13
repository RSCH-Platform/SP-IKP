<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProblemContributorCategory;
use App\Models\ProblemContributorComponent;
use App\Models\ProblemContributorSubComponent;
use App\Models\ProblemContributorDescription;

class ProblemContributorSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'A. Faktor Staf' => [
                'Faktor Kognitif' => [
                    'Persepsi / Pemahaman' => [],
                    'Berdasarkan Pengetahuan / Pemecahan Masalah' => [
                        'Kegagalan menganalisis / bertindak berdasarkan informasi yang tersedia',
                        'Masalah dengan kausalitas / penyebab',
                        'Masalah dengan kompleksitas',
                    ],
                ],
                'Faktor Kinerja / Performance' => [
                    'Kesalahan Teknis dalam Penatalaksanaan (Berdasarkan Fisik dan Keterampilan)' => [
                        'Slips (konsentrasi terpecah)',
                        'Lapses (Lupa)',
                    ],
                    'Kesalahan Teknis dalam Penatalaksanaan (Berdasarkan aturan/prosedur)' => [
                        'Kesalahan penerapan aturan / prosedur',
                        'Aturan / prosedur yang tidak sesuai',
                    ],
                    'Melakukan pemilihan / seleksi' => [],
                    'Bias (cenderung berasumsi / Opini tanpa data/fakta)' => [
                        'Bias review (berasumsi / beropini tanpa review)',
                        'Bias konfirmasi (berasumsi / beropini tanpa konfirmasi)',
                    ],
                ],
                'Tingkah Laku' => [
                    'Masalah Perhatian' => [
                        'Gangguan konsentrasi',
                        'Ketidakpedulian',
                        'Perhatian berlebihan',
                        'Hilang konsentrasi',
                    ],
                    'Kelelahan / Ketiduran' => [],
                    'Terlalu percaya diri' => [],
                    'Ketidakpatuhan' => [],
                    'Pelanggaran dilakukan secara rutin' => [],
                    'Perilaku beresiko' => [],
                    'Perilaku sembrono' => [],
                    'Sabotase / Tindak Pidana' => [],
                ],
                'Faktor Komunikasi' => [
                    'Metode Komunikasi' => [
                        'Tertulis',
                        'Elektronik',
                        'Lisan',
                    ],
                    'Perbedaan bahasa' => [],
                    'Awam tentang kesehatan (Health Literacy)' => [],
                    'Dengan siapa' => [
                        'Dengan Staf',
                        'Dengan Pasien',
                    ],
                ],
                'Faktor Patologis / Penyakit Pasien' => [
                    'Klasifikasi penyakit (ICD IX / ICD X)' => [],
                    'Masalah Penyalahgunaan' => [],
                ],
                'Faktor Emosi' => [],
                'Faktor Sosial' => [],
            ],
            'B. Faktor Pasien' => [
                'Faktor Kognitif' => [
                    'Persepsi / Pemahaman' => [
                        'Kegagalan Menganalisa / Bertindak berdasarkan informasi yang tersedia',
                        'Masalah dengan Kausalitas',
                        'Masalah dengan kompleksitas',
                    ],
                    'Berbasis pengetahuan / pemecahan masalah' => [],
                ],
                'Tingkah Laku' => [
                    'Masalah Perhatian' => [
                        'Gangguan konsentrasi',
                        'Ketidak pedulian',
                        'Perhatian barlebihan',
                        'Hilang konsentrasi',
                    ],
                    'Kelelahan / Ketiduran' => [],
                    'Terlalu percaya Diri' => [],
                    'Ketidak patuhan' => [],
                    'Pelanggaran dilakukan secara rutin' => [],
                    'Perilaku beresiko' => [],
                    'Perilaku sembrono' => [],
                    'Sabotase / Tindak Pidana' => [],
                ],
                'Faktor Komunikasi' => [
                    'Metode Komunikasi' => [
                        'Tertulus',
                        'Elektronik',
                        'Lisan',
                    ],
                    'Perbedaan bahasa' => [],
                    'Awam tentang kesehatan (Health Literacy)' => [],
                    'Dengan siapa' => [
                        'Dengan Staf',
                        'Dengan Pasien',
                    ],
                ],
                'Faktor Faktor terkait: Faktor Patologis / Penyakit Pasien' => [
                    'Klasifikasi penyakit (ICD IX / ICD X)' => [],
                    'Masalah Penyalah gunaan' => [],
                ],
                'Faktor Emosi' => [],
                'Faktor Sosial' => [],
            ],
            'C. Faktor Eksternal' => [
                'Lingkungan Alam' => [],
                'Produk Teknologi infrastruktur' => [],
                'Pelayanan, Sistem, Kebijakan' => [],
            ],
            'D. Faktor Fasyankes' => [
                'Kebijakan, Prosedur, Protokol, Proses' => [],
                'Keputusan Organisasi, Budaya Organisasi' => [],
                'Kerjasama Tims' => [],
                'Sumber Daya / Beban Kerja' => [],
            ],
            'E. Faktor Lingkungan' => [
                'Lingkungan fisik / Infrastruktur' => [],
                'Lokasi yang jauh dari fasilitas pelayanan (Remote area)' => [],
                'Asesmen resiko lingkungan / Evaluasi keselamatan lingkungan' => [],
                'Regulasi / Kode yang digunakan saat ini' => [],
            ],
        ];

        foreach ($data as $categoryName => $components) {
            $category = ProblemContributorCategory::firstOrCreate(
                ['name' => $categoryName],
                ['code' => strtoupper(str_replace([' ', '.', '-'], '', substr($categoryName, 0, 5)))]
            );

            foreach ($components as $componentName => $subComponents) {
                $component = ProblemContributorComponent::firstOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $componentName,
                    ]
                );

                foreach ($subComponents as $subComponentName => $descriptions) {
                    $subComponent = ProblemContributorSubComponent::firstOrCreate(
                        [
                            'component_id' => $component->id,
                            'name' => $subComponentName !== '' ? $subComponentName : $componentName,
                        ]
                    );

                    foreach ($descriptions as $description) {
                        if (!empty($description)) {
                            ProblemContributorDescription::firstOrCreate(
                                [
                                    'sub_component_id' => $subComponent->id,
                                    'description' => $description,
                                ]
                            );
                        }
                    }
                }
            }
        }

        $this->command->info('Problem Contributor hierarchy seeded successfully!');
    }
}
