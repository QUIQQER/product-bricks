<?php

declare(strict_types=1);

namespace QUITests\ProductBricks\Controls;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ERP\Money\Price as MoneyPrice;
use QUI\ERP\Products\Controls\Price as PriceControl;
use QUI\ERP\Products\Product\ViewFrontend;
use QUI\ProductBricks\Controls\ProductCards;
use QUI\ProductBricks\Controls\Slider\ProductSlider;
use ReflectionMethod;

class ProductPriceTest extends TestCase
{
    public function testProductCardsIgnoreBooleanOriginalPrice(): void
    {
        $Product = $this->createProductView(true);
        $Cards = new ProductCards();

        self::assertNull($Cards->getRetailPrice($Product));
    }

    public function testProductCardsUseValidOriginalPrice(): void
    {
        $Currency = QUI\ERP\Defaults::getCurrency();
        $Product = $this->createProductView(new MoneyPrice(20, $Currency));
        $Cards = new ProductCards();

        self::assertInstanceOf(PriceControl::class, $Cards->getRetailPrice($Product));
    }

    public function testProductSliderIgnoresBooleanOriginalPrice(): void
    {
        $Product = $this->createProductView(true);
        $PriceDisplay = $this->getMockBuilder(PriceControl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $PriceDisplay->method('create')->willReturn('<span>10.00</span>');
        $Product->method('getPriceDisplay')->willReturn($PriceDisplay);

        $getPriceHtml = new ReflectionMethod(ProductSlider::class, 'getPriceHtml');
        $Slider = new ProductSlider();

        self::assertStringContainsString(
            '<span>10.00</span>',
            $getPriceHtml->invoke($Slider, $Product)
        );
    }

    private function createProductView(MoneyPrice|bool $OriginalPrice): ViewFrontend
    {
        $Product = $this->getMockBuilder(ViewFrontend::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPrice', 'getPriceDisplay', 'hasOfferPrice', 'getOriginalPrice'])
            ->getMock();
        $Product->method('getPrice')->willReturn(
            new MoneyPrice(10, QUI\ERP\Defaults::getCurrency())
        );
        $Product->method('hasOfferPrice')->willReturn(true);
        $Product->method('getOriginalPrice')->willReturn($OriginalPrice);

        return $Product;
    }
}
