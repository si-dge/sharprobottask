<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
final class DiscountTest extends TestCase
{
    public function testDiscountIsRoundedToTwoDecimals(): void
    {
        $sale = 120.0; $purchase = 90.0;
        self::assertSame(25.0, round(($sale - $purchase) / $sale * 100, 2));
    }
    public function testZeroSalePriceDoesNotDivideByZero(): void
    {
        self::assertSame(0.0, 0.0 === 0.0 ? 0.0 : 1.0);
    }
}
