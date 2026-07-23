<?php

namespace App\Exports;

use App\Models\LaporanInsiden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class InvestigatedReportsExport
{
    /**
     * Semua kolom yang tersedia beserta label tampilan.
     *
     * @var array<string, string>
     */
    public static array $availableColumns = [
        'tanggal_insiden'            => 'Tanggal Insiden',
        'deskripsi_kategori_insiden' => 'Judul Insiden',
        'jenis_insiden'              => 'Jenis Insiden',
        'unit_kerja'                 => 'Unit Kerja',
        'status'                     => 'Status',
        'akar_masalah'               => 'Akar Masalah',
        'rekomendasi'                => 'Rekomendasi',
    ];

    /**
     * Kolom "base" (bukan per-problem) yang akan di-merge antar baris
     * dalam satu grup laporan.
     *
     * @var string[]
     */
    protected static array $baseColumns = [
        'tanggal_insiden',
        'deskripsi_kategori_insiden',
        'jenis_insiden',
        'unit_kerja',
        'status',
    ];

    /**
     * Lebar kolom default (dalam karakter).
     *
     * @var array<string, int>
     */
    protected static array $columnWidths = [
        'tanggal_insiden'            => 18,
        'deskripsi_kategori_insiden' => 42,
        'jenis_insiden'              => 20,
        'unit_kerja'                 => 28,
        'status'                     => 16,
        'akar_masalah'               => 46,
        'rekomendasi'                => 46,
    ];

    /** @var string[] Daftar kunci kolom yang akan diekspor (sudah divalidasi & diurutkan). */
    protected array $selectedColumns;

    /**
     * @param string|null $selectedYear         Filter tahun (nullable = semua tahun)
     * @param string|null $selectedMonth        Filter bulan (nullable = semua bulan)
     * @param string      $selectedJenisInsiden Filter jenis insiden ('' = semua)
     * @param string      $selectedStatus       Filter status ('' = semua)
     * @param string[]    $selectedColumns      Daftar kunci kolom yang dipilih user
     */
    public function __construct(
        protected ?string $selectedYear,
        protected ?string $selectedMonth,
        protected ?string $selectedJenisInsiden = '',
        protected ?string $selectedStatus       = '',
        array             $selectedColumns      = [],
    ) {
        // Normalise nullable inputs to empty string
        $this->selectedJenisInsiden = $selectedJenisInsiden ?? '';
        $this->selectedStatus       = $selectedStatus       ?? '';
        // Jika tidak ada kolom terpilih, gunakan semua kolom
        if (empty($selectedColumns)) {
            $selectedColumns = array_keys(static::$availableColumns);
        }

        // Urutkan sesuai urutan availableColumns & buang key tidak valid
        $this->selectedColumns = array_values(
            array_intersect(array_keys(static::$availableColumns), $selectedColumns)
        );
    }

    /**
     * Buat file XLSX dan kembalikan sebagai response download.
     */
    public function download(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $tempDir = storage_path('temp');

        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $fileName = $this->generateFileName();
        $filePath = $tempDir . '/' . $fileName;

        $writer = new Writer();
        $writer->openToFile($filePath);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Investigasi');

        // Tulis baris header
        $headerStyle = (new Style())
            ->setBackgroundColor(Color::rgb(31, 78, 121))
            ->setFontColor(Color::rgb(255, 255, 255))
            ->setFontBold();

        $headerCells = array_map(
            fn (string $col) => Cell::fromValue(static::$availableColumns[$col], $headerStyle),
            $this->selectedColumns,
        );
        $writer->addRow(new Row($headerCells));

        // Fetch semua data sesuai filter (tanpa pagination)
        $records    = $this->buildQuery()->get();
        $currentRow = 2; // Baris 1 = header, data mulai dari baris 2

        foreach ($records as $record) {
            $problemRows = $this->buildProblemRows($record);
            $rowspan     = count($problemRows);

            foreach ($problemRows as $problem) {
                $cells = [];

                foreach ($this->selectedColumns as $col) {
                    $value = match ($col) {
                        'tanggal_insiden'            => $record->tanggal_insiden instanceof \DateTimeInterface
                                                            ? $record->tanggal_insiden->format('d M Y')
                                                            : ($record->tanggal_insiden ?? '-'),
                        'deskripsi_kategori_insiden' => filled($record->deskripsi_kategori_insiden)
                                                            ? (string) $record->deskripsi_kategori_insiden
                                                            : '-',
                        'jenis_insiden'              => filled($record->jenis_insiden)
                                                            ? (string) $record->jenis_insiden
                                                            : '-',
                        'unit_kerja'                 => $record->unit_kerja
                                                            ?? $record->unitKerja?->unit_name
                                                            ?? '-',
                        'status'                     => filled($record->status)
                                                            ? (string) $record->status
                                                            : '-',
                        'akar_masalah'               => $problem['akar_masalah'] ?? '-',
                        'rekomendasi'                => $problem['rekomendasi'] ?? '-',
                        default                      => '-',
                    };

                    $cells[] = Cell::fromValue((string) $value);
                }

                $writer->addRow(new Row($cells));
            }

            // Merge cells pada kolom "base" jika laporan memiliki > 1 baris problem,
            // sehingga tampak seperti rowspan di tabel HTML.
            // OpenSpout Options::mergeCells(): kolom 0-indexed (A=0), baris 1-indexed.
            if ($rowspan > 1) {
                $colIndex = 0; // 0-indexed (A=0, B=1, ...)

                foreach ($this->selectedColumns as $col) {
                    if (in_array($col, static::$baseColumns, true)) {
                        $writer->getOptions()->mergeCells(
                            $colIndex,
                            $currentRow,
                            $colIndex,
                            $currentRow + $rowspan - 1,
                        );
                    }

                    $colIndex++;
                }
            }

            $currentRow += $rowspan;
        }

        // Set lebar kolom
        $colIndex = 1;

        foreach ($this->selectedColumns as $col) {
            $sheet->setColumnWidth(static::$columnWidths[$col] ?? 20, $colIndex++);
        }

        $writer->close();

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Bangun query dengan filter aktif, sama persis seperti getViewData() di widget.
     */
    protected function buildQuery(): Builder
    {
        return LaporanInsiden::query()
            ->when(
                filled($this->selectedYear),
                fn (Builder $q): Builder => $q->whereYear('tanggal_insiden', (int) $this->selectedYear),
            )
            ->when(
                filled($this->selectedMonth),
                fn (Builder $q): Builder => $q->whereMonth('tanggal_insiden', (int) $this->selectedMonth),
            )
            ->when(
                filled($this->selectedJenisInsiden),
                fn (Builder $q): Builder => $q->where('jenis_insiden', $this->selectedJenisInsiden),
            )
            ->when(
                filled($this->selectedStatus),
                fn (Builder $q): Builder => $q->where('status', $this->selectedStatus),
            )
            ->with([
                'unitKerja',
                'problems.whys',
                'problems.recommendations',
            ])
            ->latest('tanggal_insiden');
    }

    /**
     * Bangun daftar baris problem — sama persis dengan buildProblemRows() di widget.
     *
     * @return array<int, array{akar_masalah: string, rekomendasi: string}>
     */
    protected function buildProblemRows(LaporanInsiden $record): array
    {
        $rows = [];

        foreach ($record->problems as $problem) {
            $latestWhyLevel = $problem->whys->max('why_level');

            $akarMasalahItems = $problem->whys
                ->when(
                    filled($latestWhyLevel),
                    fn (Collection $whys): Collection => $whys->where('why_level', $latestWhyLevel),
                )
                ->pluck('problem_statement')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $recommendationItems = $problem->recommendations
                ->pluck('recommendation_text')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $akarMasalahItems    = $akarMasalahItems    !== [] ? $akarMasalahItems    : ['-'];
            $recommendationItems = $recommendationItems !== [] ? $recommendationItems : ['-'];

            $maxRows = max(count($akarMasalahItems), count($recommendationItems));

            for ($index = 0; $index < $maxRows; $index++) {
                $rows[] = [
                    'akar_masalah' => $akarMasalahItems[$index]    ?? '-',
                    'rekomendasi'  => $recommendationItems[$index] ?? '-',
                ];
            }
        }

        // Fallback: satu baris kosong jika tidak ada problem (sama seperti widget)
        return $rows !== [] ? $rows : [['akar_masalah' => '', 'rekomendasi' => '']];
    }

    /**
     * Generate nama file berdasarkan filter aktif dan timestamp.
     */
    protected function generateFileName(): string
    {
        $parts = ['investigasi'];

        if ($this->selectedYear) {
            $parts[] = $this->selectedYear;
        }

        if ($this->selectedMonth) {
            $parts[] = str_pad($this->selectedMonth, 2, '0', STR_PAD_LEFT);
        }

        $parts[] = now()->format('Ymd_His');

        return implode('_', $parts) . '.xlsx';
    }
}
