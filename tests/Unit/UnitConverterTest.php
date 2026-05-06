<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UnitConverter;

class UnitConverterTest extends TestCase
{
    public function test_mass_to_g_base(): void
    {
        $this->assertEquals(1.0,    UnitConverter::factorToBase('g',  'g'));
        $this->assertEquals(1000.0, UnitConverter::factorToBase('kg', 'g'));
    }

    public function test_mass_to_kg_base(): void
    {
        $this->assertEqualsWithDelta(0.001, UnitConverter::factorToBase('g',  'kg'), 1e-9);
        $this->assertEqualsWithDelta(1.0,   UnitConverter::factorToBase('kg', 'kg'), 1e-9);
    }

    public function test_volume_conversions(): void
    {
        $this->assertEquals(1.0,    UnitConverter::factorToBase('ml', 'ml'));
        $this->assertEquals(1000.0, UnitConverter::factorToBase('l',  'ml'));
        $this->assertEquals(1.0,    UnitConverter::factorToBase('cm3','ml'));
    }

    public function test_unit_conversion(): void
    {
        $this->assertEquals(1.0, UnitConverter::factorToBase('u', 'u'));
    }

    public function test_incompatible_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UnitConverter::factorToBase('g', 'ml');
    }

    public function test_500g_equals_half_kg(): void
    {
        $factor = UnitConverter::factorToBase('g', 'kg');
        $this->assertEqualsWithDelta(0.5, 500 * $factor, 1e-9);
    }

    public function test_price_calculation_kg_product(): void
    {
        // Product priced at 3000/kg — sell 500g → expect 1500
        $factor   = UnitConverter::factorToBase('g', 'kg'); // 0.001
        $qty      = 500 * $factor;                          // 0.5 kg
        $subtotal = 3000 * $qty;
        $this->assertEqualsWithDelta(1500.0, $subtotal, 0.01);
    }
}
