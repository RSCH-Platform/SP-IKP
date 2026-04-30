<?php

namespace App\Support;

use App\Models\InvestigationData;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class InvestigationDataMediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        if (! $media->model instanceof InvestigationData) {
            return (new DefaultPathGenerator())->getPath($media);
        }

        return $this->resolvePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        if (! $media->model instanceof InvestigationData) {
            return (new DefaultPathGenerator())->getPathForConversions($media);
        }

        return $this->resolvePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        if (! $media->model instanceof InvestigationData) {
            return (new DefaultPathGenerator())->getPathForResponsiveImages($media);
        }

        return $this->resolvePath($media) . '/responsive-images/';
    }

    protected function resolvePath(Media $media): string
    {
        /** @var InvestigationData $investigationData */
        $investigationData = $media->model;
        $laporan = $investigationData->laporanInsiden;

        $unitName = $laporan?->unitKerja?->unit_name
            ?? $laporan?->unit_kerja
            ?? 'unit-kerja-tidak-diketahui';

        $unitSlug = Str::slug($unitName, '-');

        $month = optional($laporan?->tanggal_lapor)->format('Y-m')
            ?? optional($laporan?->tanggal_insiden)->format('Y-m')
            ?? date('Y-m');

        $reportSegment = $laporan?->nomor_laporan
            ? Str::slug($laporan->nomor_laporan, '-')
            : ($laporan?->id ? "laporan-{$laporan->id}" : 'laporan-tidak-tersedia');

        return trim("{$unitSlug}/Laporan Insiden/{$month}/{$reportSegment}/Investigation Data", '/');
    }
}
