<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Section;

class LaporanInsidenInfolistSchema
{
    public static function sections(): array
    {
        return [
            static::sectionMeta(),
            static::sectionApproval(),
            static::sectionPelapor(),
            static::sectionInsiden(),
            static::sectionPasien(),
            static::sectionKronologi(),
            static::sectionKategoriDampak(),
            static::sectionTindakan(),
            static::sectionCatatanTambahan(),
            static::sectionVerifikasi(),
        ];
    }

    public static function sectionMeta(): Section
    {
        return Section::make('📌 Informasi Laporan')
            ->icon('heroicon-o-information-circle')
            ->schema([
                Infolists\Components\TextEntry::make('nomor_laporan')
                    ->label('Nomor Laporan')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft'         => 'gray',
                        'dilaporkan'    => 'warning',
                        'revisi'        => 'danger',
                        'diverifikasi'  => 'info',
                        'revisi_unit'   => 'danger',
                        'investigasi'   => 'success',
                        default         => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft'         => 'Draft',
                        'dilaporkan'    => 'Dilaporkan',
                        'revisi'        => 'Perlu Revisi',
                        'diverifikasi'  => 'Diverifikasi',
                        'revisi_unit'   => 'Perlu Revisi (Unit)',
                        'investigasi'   => 'Investigasi',
                        default         => $state,
                    }),

                Infolists\Components\TextEntry::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d F Y, H:i'),
            ])
            ->columns(3)
            ->compact();
    }

    public static function sectionApproval(): Section
    {
        return Section::make('🔄 Riwayat Workflow')
            ->icon('heroicon-o-clock')
            ->schema([
                Infolists\Components\TextEntry::make('reported_at')
                    ->label('Dikirim Pada')
                    ->dateTime('d F Y, H:i')
                    ->icon('heroicon-m-paper-airplane')
                    ->placeholder('Belum dikirim')
                    ->visible(fn($record) => dd($record)),

                Infolists\Components\TextEntry::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->icon('heroicon-m-check-circle')
                    ->visible(fn($record) => $record->verified_by !== null),

                Infolists\Components\TextEntry::make('verified_at')
                    ->label('Tanggal Verifikasi')
                    ->dateTime('d F Y, H:i')
                    ->icon('heroicon-m-calendar')
                    ->visible(fn($record) => $record->verified_at !== null),

                Infolists\Components\TextEntry::make('rejecter.name')
                    ->label('Dikembalikan Oleh')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn($record) => $record->rejected_by !== null),

                Infolists\Components\TextEntry::make('rejected_at')
                    ->label('Tanggal Dikembalikan')
                    ->dateTime('d F Y, H:i')
                    ->icon('heroicon-m-calendar')
                    ->visible(fn($record) => $record->rejected_at !== null),

                Infolists\Components\TextEntry::make('rejection_reason')
                    ->label('Alasan Pengembalian')
                    ->columnSpanFull()
                    ->html()
                    ->formatStateUsing(fn(?string $state): string => $state ? nl2br(e($state)) : '—')
                    ->visible(fn($record) => !empty($record->rejection_reason)),
            ])
            ->columns(2)
            ->collapsible()
            ->compact()
            ->visible(fn($record) => $record->status !== 'draft');
    }

    public static function sectionPelapor(): Section
    {
        return Section::make('📋 BAGIAN A: DATA PELAPOR')
            ->description('Identitas dan informasi kontak pelapor insiden')
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
        return Section::make('📍 BAGIAN B: RINCIAN KEJADIAN INSIDEN')
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
        return Section::make('👤 BAGIAN C: DATA PASIEN')
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
        return Section::make('📝 BAGIAN D: KRONOLOGI KEJADIAN')
            ->description('Uraian kronologis bagaimana insiden terjadi')
            ->icon('heroicon-o-document-text')
            ->schema([
                Infolists\Components\TextEntry::make('kronologi')
                    ->label('Kronologi Lengkap Insiden')
                    ->columnSpanFull()
                    ->html()
                    ->formatStateUsing(fn(?string $state): string => nl2br(e((string) $state))),

                Infolists\Components\TextEntry::make('insiden_terjadi_pada')
                    ->label('Insiden Terjadi Pada')
                    ->badge()
                    ->color('primary'),

                Infolists\Components\TextEntry::make('insiden_terjadi_pada_lainnya')
                    ->label('Keterangan Lainnya')
                    ->visible(fn($record) => $record->insiden_terjadi_pada === 'Lainnya')
                    ->placeholder('—'),
            ])
            ->collapsible()
            ->compact();
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
                    ->color(fn(?string $state): string => match ($state) {
                        'Biru (Tidak signifikan)' => 'info',
                        'Hijau (Minor)'           => 'success',
                        'Kuning (Moderat)'        => 'warning',
                        'Merah (Mayor)'           => 'danger',
                        'Hitam (Katastropik)'     => 'danger',
                        default                   => 'secondary',
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
            ->collapsible()
            ->collapsed()
            ->compact();
    }

    public static function sectionVerifikasi(): Section
    {
        return Section::make('✅ Informasi Verifikasi')
            ->icon('heroicon-o-check-badge')
            ->schema([
                Infolists\Components\TextEntry::make('verifier.name')
                    ->label('Diverifikasi Oleh')
                    ->icon('heroicon-m-user-circle')
                    ->placeholder('Belum diverifikasi'),

                Infolists\Components\TextEntry::make('verified_at')
                    ->label('Tanggal Verifikasi')
                    ->dateTime('d F Y, H:i')
                    ->icon('heroicon-m-calendar')
                    ->placeholder('—'),

                Infolists\Components\TextEntry::make('rejecter.name')
                    ->label('Dikembalikan Oleh')
                    ->icon('heroicon-m-user-circle')
                    ->placeholder('—')
                    ->visible(fn($record) => $record->rejected_by !== null),

                Infolists\Components\TextEntry::make('rejection_reason')
                    ->label('Alasan Pengembalian')
                    ->icon('heroicon-m-chat-bubble-left-ellipsis')
                    ->placeholder('—')
                    ->visible(fn($record) => !empty($record->rejection_reason))
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->visible(fn($record) => $record->verified_by !== null || $record->rejected_by !== null)
            ->compact();
    }
}
