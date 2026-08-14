<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use App\Services\RiskGradingEngine;

class RiskGradingEngineTest extends TestCase
{
    #[DataProvider('matrixDataProvider')]
    public function test_all_25_matrix_combinations(int $severity, int $probability, int $expectedScore, string $expectedBand, string $expectedLevel)
    {
        $result = RiskGradingEngine::calculate($severity, $probability);

        $this->assertEquals($expectedScore, $result['risk_score']);
        $this->assertEquals($expectedBand, $result['risk_band']);
        $this->assertEquals($expectedLevel, $result['risk_level']);
        $this->assertNotEmpty($result['required_action']);
    }

    public static function matrixDataProvider(): array
    {
        // Format: [severity, probability, expectedScore, expectedBand, expectedLevel]
        return [
            // Row 1
            [1, 1, 1, 'Biru', 'Rendah'],
            [1, 2, 2, 'Biru', 'Rendah'],
            [1, 3, 3, 'Biru', 'Rendah'],
            [1, 4, 4, 'Hijau', 'Sedang'],
            [1, 5, 5, 'Hijau', 'Sedang'],
            // Row 2
            [2, 1, 2, 'Biru', 'Rendah'],
            [2, 2, 4, 'Hijau', 'Sedang'],
            [2, 3, 6, 'Hijau', 'Sedang'],
            [2, 4, 8, 'Kuning', 'Tinggi'],
            [2, 5, 10, 'Kuning', 'Tinggi'],
            // Row 3
            [3, 1, 3, 'Biru', 'Rendah'],
            [3, 2, 6, 'Hijau', 'Sedang'],
            [3, 3, 9, 'Kuning', 'Tinggi'],
            [3, 4, 12, 'Kuning', 'Tinggi'],
            [3, 5, 15, 'Merah', 'Ekstrim'],
            // Row 4
            [4, 1, 4, 'Hijau', 'Sedang'],
            [4, 2, 8, 'Kuning', 'Tinggi'],
            [4, 3, 12, 'Kuning', 'Tinggi'],
            [4, 4, 16, 'Merah', 'Ekstrim'],
            [4, 5, 20, 'Merah', 'Ekstrim'],
            // Row 5
            [5, 1, 5, 'Hijau', 'Sedang'],
            [5, 2, 10, 'Kuning', 'Tinggi'],
            [5, 3, 15, 'Merah', 'Ekstrim'],
            [5, 4, 20, 'Merah', 'Ekstrim'],
            [5, 5, 25, 'Merah', 'Ekstrim'],
        ];
    }
}
