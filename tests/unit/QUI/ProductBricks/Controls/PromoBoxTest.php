<?php

declare(strict_types=1);

namespace QUITests\ProductBricks\Controls;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use QUI\ProductBricks\Controls\PromoBox;
use QUI\ProductBricks\Controls\PromoBoxImageNextToContent;

class PromoBoxTest extends TestCase
{
    #[DataProvider('colorSchemeProvider')]
    public function testPromoBoxRendersConfiguredContent(string $scheme, string $expectedClass): void
    {
        $Box = new PromoBox([
            'content' => 'PHPUnit promotion',
            'colorScheme' => $scheme,
            'contentPosition' => 'flex-end',
            'minHeight' => 320
        ]);

        $body = $Box->getBody();

        self::assertStringContainsString('PHPUnit promotion', $body);
        self::assertStringContainsString($expectedClass, $body);
        self::assertStringContainsString('align-items: flex-end', $body);
        self::assertStringContainsString('min-height: 320px', $body);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function colorSchemeProvider(): iterable
    {
        yield 'light' => ['light', 'colorScheme-light'];
        yield 'dark' => ['dark', 'colorScheme-dark'];
        yield 'fallback' => ['unknown', 'colorScheme-none'];
    }

    public function testPromoBoxRegistersLinkControlOptions(): void
    {
        $Box = new PromoBox([
            'url' => 'https://example.test/promotion',
            'target' => '_blank'
        ]);

        $Box->getBody();

        self::assertSame(
            'package/quiqqer/product-bricks/bin/controls/PromoBox',
            $Box->getAttribute('qui-class')
        );
        self::assertSame(
            'https://example.test/promotion',
            $Box->getAttribute('data-qui-options-url')
        );
        self::assertSame('_blank', $Box->getAttribute('data-qui-options-target'));
    }

    public function testImageNextToContentBoxRendersLayoutAndLink(): void
    {
        $Box = new PromoBoxImageNextToContent([
            'content' => 'PHPUnit side promotion',
            'layout' => 'image-left',
            'backgroundColor' => '#abcdef',
            'contentPosition' => 'center',
            'minHeight' => 450,
            'url' => '/promotion',
            'target' => '_self'
        ]);

        $body = $Box->getBody();

        self::assertStringContainsString('PHPUnit side promotion', $body);
        self::assertStringContainsString('layout-image-left', $body);
        self::assertStringContainsString('background-color: #abcdef', $body);
        self::assertStringContainsString('min-height: 450px', $body);
        self::assertStringContainsString('href="/promotion"', $body);
        self::assertStringContainsString('target="_self"', $body);
    }
}
