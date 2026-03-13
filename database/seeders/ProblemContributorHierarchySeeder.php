<?php

namespace Database\Seeders;

use App\Models\ProblemContributorCategory;
use App\Models\ProblemContributorComponent;
use App\Models\ProblemContributorSubComponent;
use App\Models\ProblemContributorDescription;
use Illuminate\Database\Seeder;

class ProblemContributorHierarchySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'A. Faktor Staf' => [
                'Faktor Kognitif' => [
                    'Persepsi / Pemahaman' => ['Masalah dalam persepsi atau pemahaman informasi'],
                    'Berdasarkan Pengetahuan/Pemecahan Masalah' => ['Kegagalan menganalisis atau bertindak berdasarkan informasi yang tersedia'],
                    'Masalah Kausalitas' => ['Masalah dengan pemahaman penyebab atau kausalitas'],
                    'Masalah Kompleksitas' => ['Masalah dengan pemahaman kompleksitas situasi'],
                ],
                'Faktor Kinerja' => [
                    'Kesalahan Teknis - Fisik/ Keterampilan' => ['Kesalahan teknis dalam penatalaksanaan berdasarkan fisik dan keterampilan'],
                    'Slips' => ['Konsentrasi terpecah saat melakukan tindakan'],
                    'Lapses' => ['Lupa melakukan prosedur atau langkah yang diperlukan'],
                    'Kesalahan Penerapan Aturan' => ['Kesalahan dalam penerapan aturan atau prosedur'],
                    'Aturan Tidak Sesuai' => ['Aturan atau prosedur yang tidak sesuai dengan situasi'],
                    'Bias Review' => ['Berasumsi atau beopini tanpa melakukan review'],
                    'Bias Konfirmasi' => ['Berasumsi atau beopini tanpa konfirmasi data'],
                ],
                'Tingkah Laku' => [
                    'Gangguan Konsentrasi' => ['Gangguan dalam konsentrasi saat bekerja'],
                    'Ketidakpedulian' => ['Sikap tidak peduli terhadap prosedur keselamatan'],
                    'Perhatian Berlebihan' => ['Fokus berlebihan pada hal lain'],
                    'Hilang Konsentrasi' => ['Kehilangan konsentrasi saat melakukan tugas'],
                    'Kelelahan / Ketiduran' => ['Kelelahan atau ketiduran saat bekerja'],
                    'Terlalu Percaya Diri' => ['Sikap overconfidence yang mengabaikan prosedur'],
                    'Ketidakpatuhan' => ['Tidak mematuhi prosedur yang berlaku'],
                    'Pelanggaran Rutin' => ['Pelanggaran prosedur dilakukan secara rutin'],
                    'Perilaku Beresiko' => ['Melakukan perilaku yang berisiko'],
                    'Perilaku Sembrono' => ['Sikap sembrono dalam bekerja'],
                    'Sabotase' => ['Sabotase atau tindakan pidana'],
                ],
                'Faktor Komunikasi' => [
                    'Komunikasi Tertulis' => ['Masalah dalam komunikasi tertulis'],
                    'Komunikasi Elektronik' => ['Masalah dalam komunikasi elektronik'],
                    'Komunikasi Lisan' => ['Masalah dalam komunikasi lisan'],
                    'Perbedaan Bahasa' => ['Hambatan komunikasi karena perbedaan bahasa'],
                    'Health Literacy' => ['Keterbatasan pemahaman kesehatan pasien'],
                    'Dengan Staf' => ['Masalah komunikasi dengan sesama staf'],
                    'Dengan Pasien' => ['Masalah komunikasi dengan pasien'],
                ],
                'Faktor Patologis' => [
                    'Klasifikasi Penyakit' => ['Terkait dengan klasifikasi penyakit pasien'],
                    'Penyalahgunaan' => ['Masalah penyalahgunaan zat atau substansi'],
                ],
                'Faktor Emosi' => [
                    'Emosi' => ['Faktor emosi yang mempengaruhi kinerja'],
                ],
                'Faktor Sosial' => [
                    'Sosial' => ['Faktor sosial yang mempengaruhi kinerja'],
                ],
            ],
            'B. Faktor Pasien' => [
                'Faktor Kognitif' => [
                    'Persepsi / Pemahaman' => ['Masalah persepsi atau pemahaman pasien'],
                    'Kegagalan Analisis' => ['Kegagalan pasien menganalisis informasi kesehatan'],
                    'Masalah Kausalitas' => ['Masalah pemahaman kausalitas penyakit'],
                    'Masalah Kompleksitas' => ['Kesulitan memahami kompleksitas kondisi kesehatan'],
                ],
                'Tingkah Laku' => [
                    'Gangguan Konsentrasi' => ['Pasien mengalami gangguan konsentrasi'],
                    'Ketidakpedulian' => ['Pasien tidak peduli dengan instruksi perawatan'],
                    'Perhatian Berlebihan' => ['Pasien terlalu fokus pada hal yang tidak relevan'],
                    'Hilang Konsentrasi' => ['Pasien kehilangan konsentrasi saat instruksi'],
                    'Kelelahan / Ketiduran' => ['Pasien dalam keadaan lelah atau tertidur'],
                    'Terlalu Percaya Diri' => ['Pasien terlalu percaya diri mengabaikan instruksi'],
                    'Ketidakpatuhan' => ['Pasien tidak mematuhi instruksi perawatan'],
                    'Pelanggaran Rutin' => ['Pasien secara rutin tidak mematuhi instruksi'],
                    'Perilaku Beresiko' => ['Pasien melakukan perilaku yang berisiko'],
                    'Perilaku Sembrono' => ['Pasien bersikap sembrono terhadap kesehatan'],
                    'Sabotase' => ['Pasien melakukan tindakan yang membahayakan diri'],
                ],
                'Faktor Komunikasi' => [
                    'Komunikasi Tertulis' => ['Masalah komunikasi tertulis dengan pasien'],
                    'Komunikasi Elektronik' => ['Masalah komunikasi elektronik dengan pasien'],
                    'Komunikasi Lisan' => ['Masalah komunikasi lisan dengan pasien'],
                    'Perbedaan Bahasa' => ['Hambatan bahasa dalam komunikasi dengan pasien'],
                    'Health Literacy' => ['Pasien memiliki health literacy rendah'],
                    'Dengan Staf' => ['Masalah komunikasi pasien dengan staf'],
                    'Dengan Pasien Lain' => ['Masalah komunikasi antar pasien'],
                ],
                'Faktor Patologis' => [
                    'Klasifikasi Penyakit' => ['Penyakit yang dialami pasien mempengaruhi kejadian'],
                    'Penyalahgunaan' => ['Pasien memiliki riwayat penyalahgunaan zat'],
                ],
                'Faktor Emosi' => [
                    'Emosi' => ['Faktor emosi pasien mempengaruhi kinerja perawatan'],
                ],
                'Faktor Sosial' => [
                    'Sosial' => ['Faktor sosial pasien mempengaruhi kesehatan'],
                ],
            ],
            'C. Faktor Eksternal' => [
                'Lingkungan Alam' => [
                    'Bencana Alam' => ['Bencana alam yang mempengaruhi layanan'],
                    'Cuaca' => ['Kondisi cuaca yang ekstrem'],
                ],
                'Teknologi / Infrastruktur' => [
                    'Produk Teknologi' => ['Masalah dengan produk atau teknologi yang digunakan'],
                    'Infrastruktur' => ['Masalah dengan infrastruktur fasilitas'],
                ],
                'Pelayanan / Sistem' => [
                    'Sistem' => ['Masalah dengan sistem pelayanan'],
                    'Kebijakan' => ['Kebijakan yang tidak sesuai'],
                    'Proses' => ['Proses pelayanan yang tidak efisien'],
                ],
            ],
            'D. Faktor Fasyankes' => [
                'Kebijakan' => [
                    'Kebijakan' => ['Kebijakan rumah sakit yang tidak sesuai'],
                    'Prosedur' => ['Prosedur yang kurang jelas atau tidak efektif'],
                    'Protokol' => ['Protokol pelayanan yang tidak sesuai'],
                    'Proses' => ['Proses organisasi yang tidak efisien'],
                ],
                'Organisasi' => [
                    'Keputusan Organisasi' => ['Keputusan organisasi yang tidak tepat'],
                    'Budaya Organisasi' => ['Budaya organisasi yang tidak mendukung keselamatan'],
                ],
                'Kerjasama Tim' => [
                    'Tim' => ['Masalah dengan kerjasama tim'],
                    'Koordinasi' => ['Kurangnya koordinasi antar departemen'],
                    'Kolaborasi' => ['Masalah dalam kolaborasi tim multidisiplin'],
                ],
                'Sumber Daya' => [
                    'Sumber Daya' => ['Sumber daya yang terbatas'],
                    'Beban Kerja' => ['Beban kerja yang berlebihan'],
                    'Staffing' => ['Kekurangan jumlah staf'],
                ],
            ],
            'E. Faktor Lingkungan' => [
                'Lingkungan Fisik' => [
                    'Infrastruktur' => ['Infrastruktur lingkungan yang tidak memadai'],
                    'Tata Ruang' => ['Tata ruang yang tidak ergonomis'],
                    'Pencahayaan' => ['Pencahayaan yang tidak memadai'],
                    'Kebisingan' => ['Kebisingan yang tinggi'],
                    'Suhu' => ['Suhu ruangan yang tidak sesuai'],
                ],
                'Remote Area' => [
                    'Lokasi Jauh' => ['Lokasi jauh dari fasilitas pelayanan utama'],
                    'Aksesibilitas' => ['Aksesibilitas yang terbatas ke fasilitas'],
                ],
                'Asesmen Resiko' => [
                    'Asesmen Resiko' => ['Asesmen risiko lingkungan yang tidak memadai'],
                    'Evaluasi Keselamatan' => ['Evaluasi keselamatan lingkungan yang tidak rutin'],
                ],
                'Regulasi' => [
                    'Regulasi' => ['Regulasi tidak sesuai dengan standar'],
                    'Kode' => ['Kode/standar yang tidak konsisten'],
                ],
            ],
        ];

        foreach ($data as $categoryName => $components) {
            $category = ProblemContributorCategory::updateOrCreate(
                ['name' => $categoryName],
                ['code' => strtoupper(str_replace([' ', '.', '-'], '', substr($categoryName, 0, 3)))]
            );

            foreach ($components as $componentName => $subComponents) {
                $component = ProblemContributorComponent::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $componentName,
                    ]
                );

                foreach ($subComponents as $subComponentName => $descriptions) {
                    $subComponent = ProblemContributorSubComponent::updateOrCreate(
                        [
                            'component_id' => $component->id,
                            'name' => $subComponentName,
                        ]
                    );

                    foreach ($descriptions as $descriptionText) {
                        ProblemContributorDescription::updateOrCreate(
                            [
                                'sub_component_id' => $subComponent->id,
                                'description' => $descriptionText,
                            ]
                        );
                    }
                }
            }
        }

        $this->command->info('Problem contributor hierarchy seeded successfully!');
    }
}
