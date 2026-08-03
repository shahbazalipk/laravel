<?php

namespace Tests\Unit;

use App\Enums\PromoDiscountType;
use App\Models\PromoCode;
use App\Services\PromoCodeService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PromoCodeServiceTest extends TestCase
{
    #[Test]
    public function it_applies_percentage_discount_before_vat_when_tax_exclusive(): void
    {
        $promo = new PromoCode([
            'code' => 'PCT10',
            'discount_type' => PromoDiscountType::Percentage,
            'discount_value' => 10,
        ]);
        $promo->id = 7;

        $pricing = app(PromoCodeService::class)->applyToPricing([
            'base_price' => 1000,
            'tax_amount' => 50,
            'total_amount' => 1050,
            'vat_percentage' => 5,
            'tax_inclusive' => false,
            'currency' => 'PKR',
        ], $promo);

        $this->assertEquals(100.0, $pricing['discount_amount']);
        $this->assertEquals(900.0, $pricing['base_price']);
        $this->assertEquals(45.0, $pricing['tax_amount']);
        $this->assertEquals(945.0, $pricing['total_amount']);
        $this->assertSame(7, $pricing['promo_code_id']);
        $this->assertSame('PCT10', $pricing['promo_code']);
    }

    #[Test]
    public function it_applies_fixed_discount_and_never_goes_below_zero(): void
    {
        $promo = new PromoCode([
            'code' => 'FLAT',
            'discount_type' => PromoDiscountType::Fixed,
            'discount_value' => 5000,
            'currency' => 'PKR',
        ]);
        $promo->id = 3;

        $pricing = app(PromoCodeService::class)->applyToPricing([
            'base_price' => 1000,
            'tax_amount' => 50,
            'total_amount' => 1050,
            'vat_percentage' => 5,
            'tax_inclusive' => false,
            'currency' => 'PKR',
        ], $promo);

        $this->assertEquals(1000.0, $pricing['discount_amount']);
        $this->assertEquals(0.0, $pricing['base_price']);
        $this->assertEquals(0.0, $pricing['tax_amount']);
        $this->assertEquals(0.0, $pricing['total_amount']);
    }

    #[Test]
    public function it_applies_percentage_discount_for_tax_inclusive_pricing(): void
    {
        $promo = new PromoCode([
            'code' => 'INCL',
            'discount_type' => PromoDiscountType::Percentage,
            'discount_value' => 10,
        ]);
        $promo->id = 9;

        // Gross 1050 inclusive of 5% VAT => base 1000
        $pricing = app(PromoCodeService::class)->applyToPricing([
            'base_price' => 1000,
            'tax_amount' => 50,
            'total_amount' => 1050,
            'vat_percentage' => 5,
            'tax_inclusive' => true,
            'currency' => 'PKR',
        ], $promo);

        $this->assertEquals(100.0, $pricing['discount_amount']);
        $this->assertEquals(900.0, $pricing['base_price']);
        $this->assertEquals(945.0, $pricing['total_amount']);
    }
}
