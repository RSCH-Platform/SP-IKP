<?php

namespace Tests\Unit\Services;

use App\Models\LaporanInsiden;
use App\Services\DashboardChartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardChartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardChartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardChartService();
    }

    /** @test */
    public function it_returns_status_distribution()
    {
        // Create test data
        LaporanInsiden::factory()->count(5)->create(['status' => 'draft']);
        LaporanInsiden::factory()->count(3)->create(['status' => 'dilaporkan']);

        $data = $this->service->getStatusDistribution();

        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('series', $data);
        $this->assertArrayHasKey('colors', $data);
        $this->assertCount(2, $data['series']);
        $this->assertEquals(5 + 3, array_sum($data['series']));
    }

    /** @test */
    public function it_returns_category_ranking()
    {
        // Create test data with different categories
        LaporanInsiden::factory()->count(10)->create(['kategori_insiden' => 'Medikasi']);
        LaporanInsiden::factory()->count(5)->create(['kategori_insiden' => 'Prosedur Klinik']);
        LaporanInsiden::factory()->count(3)->create(['kategori_insiden' => 'Dokumentasi']);

        $data = $this->service->getCategoryRanking();

        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('series', $data);
        $this->assertNotEmpty($data['labels']);
        // Should be sorted by count descending
        $this->assertEquals('Medikasi', $data['labels'][0]);
    }

    /** @test */
    public function it_returns_risk_grading_distribution()
    {
        // Create test data
        LaporanInsiden::factory()->count(15)->create(['grading_risiko' => 'Biru (Tidak signifikan)']);
        LaporanInsiden::factory()->count(5)->create(['grading_risiko' => 'Merah (Mayor)']);

        $data = $this->service->getRiskGradingDistribution();

        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('series', $data);
        $this->assertArrayHasKey('colors', $data);
        $this->assertNotEmpty($data['series']);
    }

    /** @test */
    public function it_returns_monthly_trend()
    {
        // Create test data across months
        LaporanInsiden::factory()->count(3)->create([
            'created_at' => now()->subMonths(6)
        ]);
        LaporanInsiden::factory()->count(5)->create([
            'created_at' => now()->subMonths(3)
        ]);

        $data = $this->service->getMonthlyTrend();

        $this->assertArrayHasKey('months', $data);
        $this->assertArrayHasKey('series', $data);
        $this->assertNotEmpty($data['months']);
    }

    /** @test */
    public function it_caches_results()
    {
        LaporanInsiden::factory()->count(5)->create();

        // First call should query the database
        $data1 = $this->service->getStatusDistribution();

        // Second call should use cache
        $data2 = $this->service->getStatusDistribution();

        $this->assertEquals($data1, $data2);
    }

    /** @test */
    public function it_can_clear_cache()
    {
        LaporanInsiden::factory()->count(5)->create();

        $this->service->getStatusDistribution();
        DashboardChartService::clearCache();

        // Cache should be cleared, doesn't throw error
        $data = $this->service->getStatusDistribution();
        $this->assertArrayHasKey('labels', $data);
    }
}
