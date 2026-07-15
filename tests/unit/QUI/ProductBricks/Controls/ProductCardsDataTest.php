<?php

declare(strict_types=1);

namespace QUITests\ProductBricks\Controls;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Products\Product\Product;
use QUI\ERP\Products\Product\ViewFrontend;
use QUI\ProductBricks\Controls\ProductCards;
use QUI\ProductBricks\Controls\ProductCardsDetails;

class ProductCardsDataTest extends TestCase
{
    public function testProductCardsBuildDataWithoutPrices(): void
    {
        $ProductView = $this->createMock(ViewFrontend::class);
        $Product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getViewFrontend'])
            ->getMock();
        $Product->method('getViewFrontend')->willReturn($ProductView);
        $Cards = $this->createProductCards();

        self::assertSame(
            [['Product' => $ProductView]],
            $Cards->getPublicProductsData([$Product])
        );
        self::assertStringEndsWith('/ProductCards.html', $Cards->getPublicHtmlFilePath());
        self::assertStringEndsWith('/ProductCards.css', $Cards->getPublicCssFilePath());
    }

    public function testDetailedCardsBuildDataWithoutPricesOrFields(): void
    {
        $ProductView = $this->createMock(ViewFrontend::class);
        $ProductView->method('getFields')->willReturn([]);
        $Product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getViewFrontend'])
            ->getMock();
        $Product->method('getViewFrontend')->willReturn($ProductView);
        $Cards = $this->createDetailedProductCards();

        self::assertSame(
            [[
                'Product' => $ProductView,
                'details' => []
            ]],
            $Cards->getPublicProductsData([$Product])
        );
        self::assertStringEndsWith('/ProductCardsDetails.html', $Cards->getPublicHtmlFilePath());
        self::assertStringEndsWith('/ProductCardsDetails.css', $Cards->getPublicCssFilePath());
    }

    private function createProductCards(): ProductCards
    {
        return new class (['showPrices' => false]) extends ProductCards {
            /**
             * @param array<int, Product> $products
             * @return array<int, array<string, mixed>>
             */
            public function getPublicProductsData(array $products): array
            {
                return $this->getProductsData($products);
            }

            public function getPublicHtmlFilePath(): string
            {
                return $this->getHtmlFilePath();
            }

            public function getPublicCssFilePath(): string
            {
                return $this->getCSSFilePath();
            }
        };
    }

    private function createDetailedProductCards(): ProductCardsDetails
    {
        return new class (['showPrices' => false]) extends ProductCardsDetails {
            /**
             * @param array<int, Product> $products
             * @return array<int, array<string, mixed>>
             */
            public function getPublicProductsData(array $products): array
            {
                return $this->getProductsData($products);
            }

            public function getPublicHtmlFilePath(): string
            {
                return $this->getHtmlFilePath();
            }

            public function getPublicCssFilePath(): string
            {
                return $this->getCSSFilePath();
            }
        };
    }
}
