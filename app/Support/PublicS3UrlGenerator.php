<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

/**
 * Custom URL Generator untuk mengatasi konflik hostname antara:
 * - Backend Docker  : menggunakan AWS_ENDPOINT (contoh: http://minio:9000)
 * - Frontend Browser: menggunakan AWS_URL     (contoh: http://192.168.1.4:9000/ikp)
 *
 * Masalah terjadi karena fungsi getUrl() bawaan Spatie masih merakit URL
 * menggunakan hostname dari AWS_ENDPOINT. Generator ini memaksa seluruh
 * URL yang tampil di browser untuk menggunakan base URL dari AWS_URL.
 */
class PublicS3UrlGenerator extends DefaultUrlGenerator
{
    /**
     * Menghasilkan URL publik statis yang dapat diakses oleh browser.
     *
     * Alur:
     * 1. Panggil getUrl() bawaan Spatie (hasilnya mungkin memakai hostname minio/internal).
     * 2. Ambil base URL publik dari konfigurasi AWS_URL di .env.
     * 3. Ganti bagian hostname dari URL hasil Spatie dengan AWS_URL yang benar.
     */
    public function getUrl(): string
    {
        // Dapatkan disk yang digunakan media ini (contoh: 's3')
        $diskName = $this->media->disk;

        // Ambil AWS_URL dari konfigurasi filesystem disk s3
        $publicBaseUrl = rtrim(config("filesystems.disks.{$diskName}.url", ''), '/');

        // Jika AWS_URL tidak di-set di .env, gunakan URL default bawaan Spatie
        if (empty($publicBaseUrl)) {
            return parent::getUrl();
        }

        // Dapatkan path file relatif terhadap root bucket
        // Contoh: farmasi-rawat-inap/Laporan Insiden/2026-05/ikp-2026-05-0049/file.jpg
        $relativePath = $this->getPathRelativeToRoot();

        // Rakit URL final: AWS_URL + '/' + path file
        $url = $publicBaseUrl . '/' . ltrim($relativePath, '/');

        return $this->versionUrl($url);
    }

    /**
     * Temporary URL tetap menggunakan implementasi bawaan Spatie
     * (untuk keperluan file private jika ada di masa mendatang).
     */
    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        return parent::getTemporaryUrl($expiration, $options);
    }
}
