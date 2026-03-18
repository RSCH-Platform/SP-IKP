<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use Filament\Forms\Components\Select;

class LaporanInsidenFormOptions
{
    public const JENIS_INSIDEN_OPTIONS = [
        'KPC (Kondisi Potensial Cedera)' => 'KPC (Kondisi Potensial Cedera)',
        'KNC (Kejadian Nyaris Cedera)' => 'KNC (Kejadian Nyaris Cedera)',
        'KTD (Kejadian Tidak Diharapkan)' => 'KTD (Kejadian Tidak Diharapkan)',
        'KTC (Kejadian Tidak Cedera)' => 'KTC (Kejadian Tidak Cedera)',
        'Sentinel' => 'Sentinel',
    ];

    public const DAMPAK_INSIDEN_OPTIONS = [
        'Tidak ada cedera' => '✅ Tidak ada cedera',
        'Cedera ringan' => '🟡 Cedera ringan',
        'Cedera sedang' => '🟠 Cedera sedang',
        'Cedera berat' => '🔴 Cedera berat',
        'Meninggal' => '⚫ Meninggal',
    ];

    public const KATEGORI_INSIDEN_OPTIONS = [
        'Medication / Cairan IV' => 'Medication / Cairan IV',
        'Prosedur Klinis' => 'Prosedur Klinis',
        'Diagnostik' => 'Diagnostik',
        'Infeksi Terkait Pelayanan Kesehatan' => 'Infeksi Terkait Pelayanan Kesehatan',
        'Pasien Jatuh' => 'Pasien Jatuh',
        'Identifikasi Pasien' => 'Identifikasi Pasien',
        'Komunikasi' => 'Komunikasi',
        'Dokumentasi Klinis' => 'Dokumentasi Klinis',
        'Peralatan Medis' => 'Peralatan Medis',
        'Transfusi Darah / Produk Darah' => 'Transfusi Darah / Produk Darah',
        'Administrasi / Proses Pelayanan' => 'Administrasi / Proses Pelayanan',
        'Lingkungan / Fasilitas' => 'Lingkungan / Fasilitas',
        'Faktor Manusia' => 'Faktor Manusia',
        'Lainnya' => 'Lainnya',
    ];

    public const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'dilaporkan' => 'Dilaporkan',
        'revisi' => 'Revisi',
        'diverifikasi' => 'Diverifikasi',
        'revisi_unit' => 'Revisi Unit',
        'investigasi' => 'Investigasi',
    ];

    public static function makeSelect(string $name, string $label, array $options): Select
    {
        return Select::make($name)
            ->label($label)
            ->options($options)
            ->native(false)
            ->searchable();
    }
}
