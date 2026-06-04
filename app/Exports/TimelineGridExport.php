<?php

namespace App\Exports;

use App\Models\LaporanInsiden;
use Illuminate\Support\Carbon;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use Illuminate\Support\Facades\File;

class TimelineGridExport
{
    protected LaporanInsiden $laporanInsiden;

    public function __construct(LaporanInsiden $laporanInsiden)
    {
        $this->laporanInsiden = $laporanInsiden;
    }

    public function download()
    {
        // Ensure temp directory exists
        $tempDir = storage_path('temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $writer = new Writer();
        $fileName = $this->generateFileName();

        $filePath = $tempDir . '/' . $fileName;
        $writer->openToFile($filePath);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Timeline');

        // Get data
        $timelineEvents = $this->getTimelineEvents();
        $categories = $this->getTimelineCategories();

        if ($categories->isEmpty()) {
            // No timeline data, close and return empty file
            $writer->close();
            return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
        }

        // Group events by date
        $eventsByDate = collect($timelineEvents)
            ->groupBy(fn($event) => Carbon::parse($event->event_datetime)->format('Y-m-d'))
            ->sortKeys();

        // Header styles
        $headerStyle = new Style();
        $headerStyle->setBackgroundColor(Color::rgb(31, 78, 121)); // Dark blue
        $headerStyle->setFontColor(Color::rgb(255, 255, 255)); // White text
        $headerStyle->setFontBold();

        // Date header style
        $dateHeaderStyle = new Style();
        $dateHeaderStyle->setBackgroundColor(Color::rgb(79, 129, 189)); // Light blue
        $dateHeaderStyle->setFontColor(Color::rgb(255, 255, 255));
        $dateHeaderStyle->setFontBold();

        // Border style for data cells
        $borderParts = [
            new BorderPart(Border::TOP),
            new BorderPart(Border::BOTTOM),
            new BorderPart(Border::LEFT),
            new BorderPart(Border::RIGHT),
        ];
        $border = new Border(...$borderParts);
        $borderStyle = new Style();
        $borderStyle->setBorder($border);

        // Title row
        $titleCell = Cell::fromValue(
            sprintf(
                'TIMELINE INSIDEN - Laporan #%s (%s)',
                $this->laporanInsiden->id,
                $this->laporanInsiden->created_at->translatedFormat('d F Y')
            ),
            $dateHeaderStyle
        );
        $titleRow = new Row([$titleCell]);
        $writer->addRow($titleRow);

        // Empty row
        $writer->addRow(new Row([]));

        // Process each date group
        foreach ($eventsByDate as $date => $dateEvents) {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
            $formattedDate = $dateObj->translatedFormat('d F Y');

            // Date header
            $dateHeaderCell = Cell::fromValue($formattedDate, $dateHeaderStyle);
            $dateHeaderRow = new Row([$dateHeaderCell]);
            $writer->addRow($dateHeaderRow);

            // Column headers (Waktu | Category1 | Category2 | ...)
            $headerCells = [Cell::fromValue('Waktu', $headerStyle)];
            foreach ($categories as $category) {
                $categoryLabel = sprintf(
                    '%s (%s)',
                    $category->name,
                    $category->code
                );
                $headerCells[] = Cell::fromValue($categoryLabel, $headerStyle);
            }
            $headerRow = new Row($headerCells);
            $writer->addRow($headerRow);

            // Data rows
            $sortedEvents = collect($dateEvents)
                ->sortBy('event_datetime');

            foreach ($sortedEvents as $event) {
                $eventTime = Carbon::parse($event->event_datetime);
                $timeFormatted = $eventTime->format('H:i');

                $dataCells = [Cell::fromValue($timeFormatted)];

                foreach ($categories as $category) {
                    $entry = $event->entries()
                        ->where('category_id', $category->id)
                        ->first();

                    $description = $entry?->description ?? '';
                    $dataCells[] = Cell::fromValue($description, $borderStyle);
                }

                $dataRow = new Row($dataCells);
                $writer->addRow($dataRow);
            }

            // Empty row between dates
            $writer->addRow(new Row([]));
        }

        // Set column widths
        $sheet->setColumnWidth(25, 1); // Waktu column
        for ($i = 2; $i <= count($categories) + 1; $i++) {
            $sheet->setColumnWidth(35, $i); // Category columns
        }

        $writer->close();

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    protected function getTimelineEvents()
    {
        return $this->laporanInsiden->timelineEvents()
            ->with('entries.category')
            ->orderBy('event_datetime')
            ->get();
    }

    protected function getTimelineCategories()
    {
        // Get all unique categories from timeline entries, ordered by sort_order
        return \App\Models\TimelineCategory::whereHas(
            'entries',
            function ($query) {
                $query->whereIn(
                    'timeline_event_id',
                    $this->laporanInsiden->timelineEvents()->pluck('id')
                );
            }
        )
            ->orderBy('sort_order')
            ->get();
    }

    protected function generateFileName(): string
    {
        // Get description from kategori_insiden or deskripsi_kategori_insiden
        $description = $this->laporanInsiden->deskripsi_kategori_insiden
            ?? $this->laporanInsiden->kategori_insiden
            ?? 'Timeline';

        // Normalize filename: lowercase, replace spaces and special chars with underscore, limit to 50 chars
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', $description);
        $normalized = trim($normalized, '_');
        $normalized = substr($normalized, 0, 50);

        // Format: YYYY-MM-DD_description.xlsx
        $date = $this->laporanInsiden->tanggal_insiden
            ? Carbon::parse($this->laporanInsiden->tanggal_insiden)->format('Y-m-d')
            : now()->format('Y-m-d');

        return sprintf('%s_%s.xlsx', $date, $normalized);
    }
}
