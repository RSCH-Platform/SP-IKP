<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use App\Models\LaporanInsiden;
use Filament\Forms\Components\Select;

class LaporanInsidenFormOptions
{
    public const JENIS_INSIDEN_OPTIONS = LaporanInsiden::JENIS_INSIDEN_OPTIONS;

    public const DAMPAK_INSIDEN_OPTIONS = LaporanInsiden::DAMPAK_INSIDEN_OPTIONS;

    public const KATEGORI_INSIDEN_OPTIONS = LaporanInsiden::KATEGORI_INSIDEN_OPTIONS;

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
