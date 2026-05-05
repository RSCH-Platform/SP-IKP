<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use App\Models\LaporanInsiden;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class TindakanSection
{
    public static function make(bool $withAnalysis = false): Section
    {
        $fields = [
            Textarea::make('tindakan_dilakukan')
                ->label('Tindakan yang Telah Dilakukan Setelah Insiden')
                ->required()
                ->rows(16)
                ->helperText('Jelaskan seluruh tindakan yang telah dilakukan setelah insiden terjadi.')
                ->placeholder("Contoh:\n1. Segera memberikan pertolongan pertama\n2. Menghubungi dokter jaga\n3. Melaporkan kepada kepala ruangan")
                ->columnSpanFull(),

            Select::make('tindakan_dilakukan_oleh')
                ->label('Tindakan Dilakukan Oleh')
                ->required()
                ->options(LaporanInsiden::TINDAKAN_DILAKUKAN_OLEH_OPTIONS)
                ->placeholder('Pilih unit yang melakukan tindakan')
                ->live()
                ->native(false)
                ->helperText('Pilih unit yang melakukan tindakan setelah insiden terjadi'),

            TextInput::make('tindakan_dilakukan_oleh_lainnya')
                ->label('Sebutkan Lainnya')
                ->placeholder('Jelaskan unit lain yang melakukan tindakan setelah insiden terjadi')
                ->prefixIcon('heroicon-m-pencil')
                ->visible(fn(Get $get) => $get('tindakan_dilakukan_oleh') === 'Lainnya')
                ->required(fn(Get $get) => $get('tindakan_dilakukan_oleh') === 'Lainnya'),

            ToggleButtons::make('kejadian_pernah_terjadi_sebelumnya')
                ->label('Apakah kejadian serupa pernah terjadi sebelumnya?')
                ->options([
                    'Ya' => '✅ Ya',
                    'Tidak' => '❌ Tidak',
                ])
                ->inline()
                ->live()
                ->helperText('Pilih "Ya" jika insiden serupa pernah terjadi sebelumnya, pilih "Tidak" jika ini adalah pertama kalinya insiden ini terjadi.'),

            Textarea::make('kejadian_pernah_terjadi_sebelumnya_deskripsi')
                ->label('Deskripsikan kejadian sebelumnya (jika ada)')
                ->rows(5)
                ->helperText('(Opsional) Jelaskan secara singkat kejadian serupa yang pernah terjadi sebelumnya, termasuk kapan dan bagaimana penanganannya.')
                ->placeholder("Contoh:\nKejadian serupa pernah terjadi pada bulan Januari 2024, dimana seorang pasien mengalami kesalahan pemberian obat. Penanganan saat itu melibatkan pemberian antidotum dan evaluasi ulang prosedur pemberian obat di unit terkait.")
                ->visible(fn(Get $get) => $get('kejadian_pernah_terjadi_sebelumnya') === 'Ya')
                ->required(fn(Get $get) => $get('kejadian_pernah_terjadi_sebelumnya') === 'Ya'),
        ];

        if ($withAnalysis) {
            $fields[] = Forms\Components\Textarea::make('analisis_penyebab')
                ->label('Analisis Penyebab Insiden')
                ->rows(6)
                ->helperText('Analisis mendalam tentang penyebab insiden, faktor yang berkontribusi, dan rencana tindakan pencegahan ke depan.')
                ->placeholder("Contoh:\nPenyebab utama insiden adalah kurangnya komunikasi antara petugas saat shift change. Faktor yang berkontribusi termasuk kurangnya standar komunikasi yang jelas dan tidak adanya checklist handover. Rencana tindakan pencegahan meliputi implementasi SBAR untuk komunikasi antar shift dan pelatihan ulang bagi seluruh staf.")
                ->columnSpanFull();
        }

        return Section::make('BAGIAN E: TINDAKAN YANG DILAKUKAN')
            ->description('Tindakan yang telah dilakukan setelah terjadinya insiden')
            ->icon('heroicon-o-hand-raised')
            ->schema($fields)
            ->collapsible()
            ->collapsed()
            ->persistCollapsed()
            ->compact();
    }
}
