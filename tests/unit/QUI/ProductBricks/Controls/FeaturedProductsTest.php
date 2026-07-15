<?php

declare(strict_types=1);

namespace QUITests\ProductBricks\Controls;

use PHPUnit\Framework\TestCase;
use QUI\ProductBricks\Controls\FeaturedProducts;
use RuntimeException;

class FeaturedProductsTest extends TestCase
{
    private string $customCssFile;

    protected function setUp(): void
    {
        parent::setUp();

        $customCssFile = tempnam(sys_get_temp_dir(), 'product-bricks-');

        if ($customCssFile === false) {
            throw new RuntimeException('Could not create the custom CSS test fixture.');
        }

        $this->customCssFile = $customCssFile;
        file_put_contents($this->customCssFile, '.phpunit-custom-css { color: red; }');
    }

    protected function tearDown(): void
    {
        if (is_file($this->customCssFile)) {
            unlink($this->customCssFile);
        }

        parent::tearDown();
    }

    public function testCustomCssDoesNotReplaceTheHtmlTemplate(): void
    {
        $Control = new FeaturedProducts([
            'customCss' => $this->customCssFile
        ]);

        $body = $Control->getBody();

        self::assertStringNotContainsString('.phpunit-custom-css', $body);
        self::assertContains($this->customCssFile, $Control->getCSSFiles());
    }
}
