<?php

namespace App\Filament\Resources\LaporanInsidens\Schemas\Sections;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class KonfirmasiTandaTanganSection
{
    public static function make(): Section
    {
        return Section::make('✓ KONFIRMASI & TANDA TANGAN')
            ->description('Tinjau laporan dan tanda tangani secara digital sebelum mengirim')
            ->icon('heroicon-o-shield-check')
            ->schema([
                // Preview laporan
                TextEntry::make('preview_info')
                    ->label('📋 Preview Laporan')
                    ->content('Silakan tinjau laporan Anda terlebih dahulu sebelum menandatangani.')
                    ->helperText('Klik tombol di bawah untuk melihat laporan dalam format profesional')
                    ->columnSpanFull(),

                // Tombol untuk buka preview
                Actions::make([
                    Action::make('preview_laporan')
                        ->label('Lihat Preview Laporan')
                        ->url(fn() => '#', shouldOpenInNewTab: true)
                        ->color('info')
                        ->icon('heroicon-o-arrow-top-right-on-square'),
                ])
                    ->columnSpanFull(),

                // Alert untuk confirmation
                Placeholder::make('confirmation_alert')
                    ->content('⚠️ Dengan menandatangani laporan ini, Anda menyatakan bahwa:')
                    ->columnSpanFull(),

                // Checkboxes untuk pernyataan
                Grid::make(1)->schema([
                    Checkbox::make('confirm_data_accurate')
                        ->label('Data laporan sudah sesuai dengan kejadian yang sebenarnya')
                        ->required()
                        ->helperText('Pastikan semua informasi telah diisi dengan benar')
                        ->dehydrated(false),

                    Checkbox::make('confirm_responsibility')
                        ->label('Saya bertanggung jawab atas keakuratan laporan ini')
                        ->required()
                        ->helperText('Anda bertanggung jawab atas semua informasi yang disampaikan')
                        ->dehydrated(false),
                ]),

                // Signature info display
                Placeholder::make('signature_info')
                    ->label('🔐 Informasi Tanda Tangan Digital')
                    ->content(function () {
                        $user = Auth::user();
                        $now = now();

                        return "
                            <div class='space-y-2'>
                                <p><strong>Akan ditandatangani oleh:</strong> {$user->name}</p>
                                <p><strong>Tanggal & Waktu:</strong> {$now->format('d F Y \\p\\u\\k\\u\\l H:i')} WIB</p>
                                <p><strong>Jenis Signature:</strong> HMAC-SHA256 (Tamper-proof)</p>
                                <p class='text-xs text-slate-600 mt-3'>
                                    <strong>⚠️ PENTING:</strong> Tanda tangan ini akan disimpan secara permanent dan tidak dapat diubah. 
                                    Pastikan semua data sudah benar sebelum menandatangani.
                                </p>
                            </div>
                        ";
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible(false)
            ->collapsed(false);
    }
}
