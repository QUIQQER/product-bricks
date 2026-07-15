<?php

declare(strict_types=1);

namespace QUITests\ProductBricks\Controls;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Products\Interfaces\CategoryInterface;
use QUI\ProductBricks\Controls\CategoryBox;
use QUI\Projects\Site;

class CategoryBoxTest extends TestCase
{
    public function testBuildsCategoryEntryFromSiteAttributes(): void
    {
        $Site = $this->createSite([
            'title' => 'PHPUnit category',
            'short' => 'PHPUnit description',
            'image_site' => 'image-id'
        ]);
        $Site->method('getUrl')->willReturn('/phpunit-category');
        $Box = new CategoryBox();

        self::assertSame([
            'Site' => $Site,
            'title' => 'PHPUnit category',
            'desc' => 'PHPUnit description',
            'url' => '/phpunit-category',
            'image' => 'image-id'
        ], $Box->setCategoryAttributes($Site));
    }

    public function testUsesCategoryDescriptionAsSiteFallback(): void
    {
        $Category = $this->createMock(CategoryInterface::class);
        $Category->method('getDescription')->willReturn('PHPUnit category fallback');
        $Site = $this->createSite([
            'title' => 'PHPUnit category',
            'short' => '',
            'image_site' => false
        ]);
        $Site->method('getUrl')->willReturn('/phpunit-category');
        $Box = $this->getMockBuilder(CategoryBox::class)
            ->onlyMethods(['getCategoryFromSite'])
            ->getMock();
        $Box->method('getCategoryFromSite')->with($Site)->willReturn($Category);

        self::assertSame(
            'PHPUnit category fallback',
            $Box->setCategoryAttributes($Site)['desc']
        );
    }

    public function testRejectsMissingCategoryAssignment(): void
    {
        $Site = $this->createSite([
            'id' => 73,
            'title' => 'PHPUnit category',
            'quiqqer.products.settings.categoryId' => false
        ]);
        $Box = $this->getMockBuilder(CategoryBox::class)
            ->onlyMethods(['getCategoryFromSite'])
            ->getMock();
        $Box->expects(self::never())->method('getCategoryFromSite');

        self::assertFalse($Box->checkAssignedCategory($Site));
    }

    public function testAcceptsExistingCategoryAssignment(): void
    {
        $Category = $this->createMock(CategoryInterface::class);
        $Site = $this->createSite([
            'quiqqer.products.settings.categoryId' => 7
        ]);
        $Box = $this->getMockBuilder(CategoryBox::class)
            ->onlyMethods(['getCategoryFromSite'])
            ->getMock();
        $Box->expects(self::once())->method('getCategoryFromSite')->with($Site)->willReturn($Category);

        self::assertTrue($Box->checkAssignedCategory($Site));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createSite(array $attributes): Site
    {
        $Site = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute', 'getUrl'])
            ->getMock();
        $Site->method('getAttribute')->willReturnCallback(
            static fn(string $name): mixed => $attributes[$name] ?? false
        );

        return $Site;
    }
}
