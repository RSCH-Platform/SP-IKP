<?php

namespace App\Support;

use App\Models\ProblemAction;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class ProblemActionMediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        if (! $media->model instanceof ProblemAction) {
            return (new DefaultPathGenerator())->getPath($media);
        }

        return $this->resolvePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        if (! $media->model instanceof ProblemAction) {
            return (new DefaultPathGenerator())->getPathForConversions($media);
        }

        return $this->resolvePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        if (! $media->model instanceof ProblemAction) {
            return (new DefaultPathGenerator())->getPathForResponsiveImages($media);
        }

        return $this->resolvePath($media) . '/responsive-images/';
    }

    protected function resolvePath(Media $media): string
    {
        /** @var ProblemAction $action */
        $action = $media->model;

        $incident = $action->problem?->incident;

        $unitName = $incident?->unitKerjas?->unit_name
            ?? $incident?->unit_kerja
            ?? 'unit-kerja-tidak-diketahui';

        $unitFolder = Str::slug($unitName, '-');

        $month = optional($incident?->tanggal_lapor)->format('Y-m')
            ?? optional($incident?->tanggal_insiden)->format('Y-m')
            ?? date('Y-m');

        $reportSegment = $incident?->nomor_laporan
            ? Str::slug($incident->nomor_laporan, '-')
            : ($incident?->id ? (string) $incident->id : 'laporan-tidak-tersedia');

        $basePath = trim("{$unitFolder}/Laporan Insiden/{$month}/{$reportSegment}", '/');

        return $basePath;
    }
}
