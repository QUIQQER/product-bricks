<?php

declare(strict_types=1);

namespace QUITests\ProductBricks\Controls\Children;

use PHPUnit\Framework\TestCase;
use QUI\ProductBricks\Controls\Children\Slider;

class SliderTest extends TestCase
{
    public function testReturnsEmptyBodyWithoutConfiguredProductsOrCategories(): void
    {
        self::assertSame('', (new Slider())->getBody());
    }

    public function testUsesDefaultLimitForMissingAndInvalidValues(): void
    {
        self::assertSame(10, $this->resolveLimit(false));
        self::assertSame(10, $this->resolveLimit(0));
        self::assertSame(10, $this->resolveLimit(-1));
    }

    public function testUsesConfiguredPositiveLimit(): void
    {
        self::assertSame(5, $this->resolveLimit(5));
    }

    private function resolveLimit(mixed $limit): int
    {
        $Slider = new class (['limit' => $limit]) extends Slider {
            public function getResolvedLimit(): int
            {
                return $this->getProductLimit();
            }
        };

        return $Slider->getResolvedLimit();
    }
}
