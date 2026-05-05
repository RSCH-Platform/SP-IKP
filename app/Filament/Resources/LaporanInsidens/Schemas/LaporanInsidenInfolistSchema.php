<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;

class LaporanInsidenInfolistSchema
{
    public static function sections(): array
    {
        return [
            static::sectionPelapor(),
            static::sectionInsiden(),
            static::sectionPasien(),
            static::sectionKronologi(),
            static::sectionKategoriDampak(),
            static::sectionTindakan(),
            static::sectionCatatanTambahan(),
            // static::sectionVerifikasi(),
        ];
    }

    public static function sectionPelapor(): Section
    {
        return Section::make('📋 BAGIAN A: DATA PELAPOR')
            ->description('Identitas dan informasi kontak pelapor insiden (Otomatis Terisi)')
            ->icon('heroicon-o-user-circle')
            ->schema([
                Infolists\Components\TextEntry::make('nama_pelapor')
                    ->label('Nama Pelapor')
                    ->icon('heroicon-m-user'),

                Infolists\Components\TextEntry::make('unit_kerja')
                    ->label('Unit Kerja / Departemen')
                    ->icon('heroicon-m-building-office'),

                Infolists\Components\TextEntry::make('nomor_telepon')
                    ->label('Nomor Telepon / HP')
                    ->icon('heroicon-m-phone')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('tanggal_lapor')
                    ->label('Tanggal Pelaporan')
                    ->date('d F Y')
                    ->icon('heroicon-m-calendar'),
            ])
            ->columns(2)
            ->collapsible()
            ->compact();
    }

    public static function sectionInsiden(): Section
    {
        return Section::make('BAGIAN C: RINCIAN KEJADIAN INSIDEN')
            ->description('Informasi lengkap tentang waktu dan tempat terjadinya insiden')
            ->icon('heroicon-o-exclamation-triangle')
            ->schema([
                Infolists\Components\TextEntry::make('jenis_insiden')
                    ->label('Jenis Insiden')
                    ->badge()
                    ->color('warning'),

                Infolists\Components\TextEntry::make('lokasi_insiden')
                    ->label('Lokasi Kejadian')
                    ->icon('heroicon-m-map-pin'),

                Infolists\Components\TextEntry::make('tanggal_insiden')
                    ->label('Tanggal Insiden')
                    ->date('d F Y')
                    ->icon('heroicon-m-calendar-days'),

                Infolists\Components\TextEntry::make('waktu_insiden')
                    ->label('Waktu Insiden')
                    ->time('H:i')
                    ->icon('heroicon-m-clock'),
            ])
            ->columns(2)
            ->collapsible()
            ->compact();
    }

    public static function sectionPasien(): Section
    {
        return Section::make('BAGIAN C: DATA PASIEN')
            ->description('Informasi pasien yang terlibat dalam insiden')
            ->icon('heroicon-o-identification')
            ->schema([
                Infolists\Components\TextEntry::make('nama_pasien')
                    ->label('Nama Pasien')
                    ->icon('heroicon-m-user')
                    ->placeholder('Tidak melibatkan pasien'),

                Infolists\Components\TextEntry::make('nomor_rekam_medis')
                    ->label('No. Rekam Medis')
                    ->icon('heroicon-m-document-duplicate')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('ruangan')
                    ->label('Ruangan / Bangsal')
                    ->icon('heroicon-m-home')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('umur')
                    ->label('Umur')
                    ->suffix(' tahun')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('kelompok_umur')
                    ->label('Kelompok Umur')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('penanggung_biaya')
                    ->label('Penanggung Biaya')
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('tanggal_masuk_rs')
                    ->label('Tanggal Masuk RS')
                    ->dateTime('d F Y, H:i')
                    ->icon('heroicon-m-arrow-right-on-rectangle')
                    ->placeholder('—'),
            ])
            ->columns(2)
            ->collapsible()
            ->collapsed()
            ->compact();
    }

    public static function sectionKronologi(): Section
    {
        return Section::make('BAGIAN D: KRONOLOGI KEJADIAN')
            ->description('Uraian kronologis insiden dalam format table timeline yang disamakan dengan halaman edit')
            ->icon('heroicon-o-document-text')
            ->schema([
                View::make('filament.components.timeline-events-infolist'),
            ])
            ->collapsible()
            ->compact();
    }

    protected static function formatTimeline($record): string
    {
        $events = $record->relationLoaded('timelineEvents')
            ? $record->timelineEvents
            : $record->timelineEvents()->with('entries.category')->get();

        if ($events->isEmpty()) {
            return '<em>Tidak ada kronologi (timeline) yang tercatat.</em>';
        }

        $html = '';
        foreach ($events->sortBy('event_datetime') as $event) {
            $datetime = $event->event_datetime?->format('d F Y H:i') ?? '-';
            $html .= "<div class=\"font-semibold\">{$datetime}</div>";

            foreach ($event->entries as $entry) {
                $category = $entry->category?->name ?? 'Kategori';
                $description = e($entry->description);
                $html .= "<div class=\"ml-4\">• <strong>{$category}</strong>: {$description}</div>";
            }

            $html .= '<br />';
        }

        return $html;
    }

    public static function sectionKategoriDampak(): Section
    {
        return Section::make('⚠️ BAGIAN E: KATEGORI DAN DAMPAK INSIDEN')
            ->description('Klasifikasi jenis dan tingkat dampak insiden')
            ->icon('heroicon-o-shield-exclamation')
            ->schema([
                Infolists\Components\TextEntry::make('kategori_insiden')
                    ->label('Kategori Insiden')
                    ->badge()
                    ->color('info'),

                Infolists\Components\TextEntry::make('dampak_insiden')
                    ->label('Dampak Insiden')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Tidak ada cedera' => 'success',
                        'Cedera ringan'    => 'warning',
                        'Cedera sedang'    => 'warning',
                        'Cedera berat'     => 'danger',
                        'Meninggal'        => 'danger',
                        default            => 'secondary',
                    }),

                Infolists\Components\TextEntry::make('grading_risiko')
                    ->label('Grading Risiko')
                    ->badge()
                    ->icon(fn($record) => match ($record->grading_risiko) {
                        'Biru'   => 'heroicon-m-information-circle',
                        'Hijau'  => 'heroicon-m-check-circle',
                        'Kuning' => 'heroicon-m-exclamation-triangle',
                        'Merah'  => 'heroicon-m-x-circle',
                        'Hitam'  => 'heroicon-m-fire',
                        default  => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'Biru'   => 'Biru — Tidak signifikan',
                        'Hijau'  => 'Hijau — Minor',
                        'Kuning' => 'Kuning — Moderat',
                        'Merah'  => 'Merah — Mayor',
                        'Hitam'  => 'Hitam — Katastropik',
                        default  => $state,
                    })
                    ->color(fn($record) => match ($record->grading_risiko) {
                        'Biru'   => 'info',
                        'Hijau'  => 'success',
                        'Kuning' => 'warning',
                        'Merah'  => 'danger',
                        'Hitam'  => 'gray',
                        default  => 'secondary',
                    })
                    ->tooltip(fn($record) => match ($record->grading_risiko) {
                        'Biru'   => 'Risiko tidak signifikan terhadap sistem',
                        'Hijau'  => 'Risiko kecil / minor',
                        'Kuning' => 'Risiko sedang yang perlu perhatian',
                        'Merah'  => 'Risiko besar yang perlu penanganan segera',
                        'Hitam'  => 'Risiko kritis / katastropik',
                        default  => null,
                    })
                    ->placeholder('Belum ditentukan'),
            ])
            ->columns(3)
            ->collapsible()
            ->compact();
    }

    public static function sectionTindakan(): Section
    {
        return Section::make('🩹 BAGIAN F: TINDAKAN YANG DILAKUKAN')
            ->description('Tindakan yang telah dilakukan setelah terjadinya insiden')
            ->icon('heroicon-o-hand-raised')
            ->schema([
                Infolists\Components\TextEntry::make('tindakan_dilakukan')
                    ->label('Tindakan yang Telah Dilakukan')
                    ->columnSpanFull()
                    ->html()
                    ->formatStateUsing(fn(?string $state): string => $state ? nl2br(e($state)) : '<span class="text-gray-400">Belum ada tindakan yang dilaporkan</span>')
                    ->placeholder('Belum ada tindakan yang dilaporkan'),
            ])
            ->collapsible()
            ->compact();
    }

    public static function sectionCatatanTambahan(): Section
    {
        return Section::make('📎 CATATAN TAMBAHAN')
            ->description('Informasi tambahan (jika ada)')
            ->icon('heroicon-o-paper-clip')
            ->schema([
                Infolists\Components\TextEntry::make('catatan_tambahan')
                    ->label('Catatan')
                    ->columnSpanFull()
                    ->html()
                    ->formatStateUsing(fn(?string $state): string => $state ? nl2br(e($state)) : '<span class="text-gray-400">Tidak ada catatan tambahan</span>')
                    ->placeholder('Tidak ada catatan tambahan'),
            ])
            ->hidden()
            ->collapsible()
            ->collapsed()
            ->compact();
    }

    // public static function sectionVerifikasi(): Section
    // {
    //     return Section::make('✅ Informasi Verifikasi')
    //         ->icon('heroicon-o-check-badge')
    //         ->schema([
    //             Infolists\Components\TextEntry::make('verifier.name')
    //                 ->label('Diverifikasi Oleh')
    //                 ->icon('heroicon-m-user-circle')
    //                 ->placeholder('Belum diverifikasi'),

    //             Infolists\Components\TextEntry::make('verified_at')
    //                 ->label('Tanggal Verifikasi')
    //                 ->dateTime('d F Y, H:i')
    //                 ->icon('heroicon-m-calendar')
    //                 ->placeholder('—'),

    //             Infolists\Components\TextEntry::make('rejecter.name')
    //                 ->label('Dikembalikan Oleh')
    //                 ->icon('heroicon-m-user-circle')
    //                 ->placeholder('—')
    //                 ->visible(fn($record) => $record->rejected_by !== null),

    //             Infolists\Components\TextEntry::make('rejection_reason')
    //                 ->label('Alasan Pengembalian')
    //                 ->icon('heroicon-m-chat-bubble-left-ellipsis')
    //                 ->placeholder('—')
    //                 ->visible(fn($record) => !empty($record->rejection_reason))
    //                 ->columnSpanFull(),
    //         ])
    //         ->columns(2)
    //         ->visible(fn($record) => $record->verified_by !== null || $record->rejected_by !== null)
    //         ->compact();
    // }
}
