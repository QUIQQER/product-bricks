<?php

declare(strict_types=1);

namespace QUITests\ProductBricks;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ERP\Products\Handler\Products;
use QUI\ERP\Products\Product\Product;
use QUI\ERP\Products\Product\Types\Product as ProductType;
use QUI\ERP\Products\Product\Types\VariantChild;
use QUI\ERP\Products\Product\Types\VariantParent;
use QUI\ERP\Products\Utils\Tables;
use QUI\ProductBricks\Controls\Children\Slider;
use QUI\ProductBricks\Controls\FeaturedProducts;
use QUI\ProductBricks\Controls\ProductCards;
use QUI\ProductBricks\Controls\ProductCardsDetails;
use QUI\ProductBricks\Utils\ProductSearch;
use ReflectionProperty;

class ProductSearchTest extends TestCase
{
    private const FIRST = 991832901;
    private const SECOND = 991832902;
    private const PARENT = 991832903;
    private const CHILD = 991832904;
    private const CATEGORY = 9918329;

    private mixed $previousSessionUser;

    /** @var array<string, mixed> */
    private array $previousVariantSettings;

    protected function setUp(): void
    {
        self::cleanFixtures();
        $Users = QUI::getUsers();
        $Session = new ReflectionProperty($Users, 'Session');
        $this->previousSessionUser = $Session->getValue($Users);
        $Session->setValue($Users, $Users->getSystemUser());

        $Config = QUI::getPackage('quiqqer/products')->getConfig();
        self::assertNotNull($Config);
        $this->previousVariantSettings = $Config->getSection('variants');
        $Config->set('variants', 'findChildrenInSearch', 1);
        $Config->set('variants', 'findVariantParentByChildValues', 1);
        $Config->save();

        $fixtures = [
            self::FIRST => [ProductType::class, 2],
            self::SECOND => [ProductType::class, 8],
            self::PARENT => [VariantParent::class, 10],
            self::CHILD => [VariantChild::class, 20]
        ];

        foreach ($fixtures as $id => [$type, $orderCount]) {
            self::connection()->insert(Tables::getProductTableName(), [
                'id' => $id,
                'type' => $type,
                'active' => 1,
                'category' => 0,
                'categories' => ',' . self::CATEGORY . ',',
                'fieldData' => '[]',
                'parent' => $id === self::CHILD ? self::PARENT : null,
                'orderCount' => $orderCount
            ]);
            self::connection()->insert(Tables::getProductCacheTableName(), [
                'id' => $id,
                'type' => $type,
                'active' => 1,
                'lang' => QUI::getLocale()->getCurrent(),
                'category' => ',' . self::CATEGORY . ',',
                'parentId' => $id === self::CHILD ? self::PARENT : null,
                'title' => 'PHPUnit product-bricks ' . $id,
                'F18' => $id,
                'c_date' => '2026-01-01 00:00:00',
                'e_date' => '2026-01-01 00:00:00'
            ]);
        }
    }

    protected function tearDown(): void
    {
        self::cleanFixtures();
        $Config = QUI::getPackage('quiqqer/products')->getConfig();

        if ($Config !== null) {
            $Config->setSection('variants', $this->previousVariantSettings);
            $Config->save();
        }

        (new ReflectionProperty(QUI::getUsers(), 'Session'))->setValue(
            QUI::getUsers(),
            $this->previousSessionUser
        );
    }

    #[DataProvider('controlProvider')]
    public function testControlsHideChildrenOfInactiveParents(string $controlClass, bool $useCategory): void
    {
        self::deactivateParent();
        $selection = $useCategory
            ? ['categoryIds' => (string)self::CATEGORY]
            : ['productIds' => (string)self::CHILD];

        if ($useCategory) {
            foreach ([self::FIRST, self::SECOND] as $id) {
                self::connection()->update(Tables::getProductCacheTableName(), ['active' => 0], ['id' => $id]);
                self::connection()->update(Tables::getProductTableName(), ['active' => 0], ['id' => $id]);
            }
        }

        $Control = new $controlClass(array_merge($selection, [
            'showVariantChildren' => true,
            'showPrices' => false,
            'limit' => 1
        ]));

        self::assertSame('', $Control->getBody());
    }

    /** @return array<string, array{class-string<Slider|ProductCards>, bool}> */
    public static function controlProvider(): array
    {
        return [
            'slider product' => [Slider::class, false],
            'slider category' => [Slider::class, true],
            'cards product' => [ProductCards::class, false],
            'cards category' => [ProductCards::class, true],
            'detailed cards product' => [ProductCardsDetails::class, false],
            'detailed cards category' => [ProductCardsDetails::class, true]
        ];
    }

    public function testFeaturedProductsExcludeInvisibleVariantsBeforeLimiting(): void
    {
        self::deactivateParent();
        $Control = new FeaturedProducts([
            'showVariantChildren' => true,
            'limit' => 1,
            'order' => 'orderCount DESC'
        ]);

        self::assertSame([self::SECOND], self::ids($Control->getProducts(['categoryId' => self::CATEGORY])));
        self::assertSame([self::FIRST], self::ids($Control->getProducts([
            'categoryId' => self::CATEGORY,
            'where' => ['id' => self::FIRST]
        ])));
        self::assertSame([], $Control->getProducts([
            'categoryId' => self::CATEGORY,
            'where' => ['id' => self::CHILD]
        ]));
    }

    #[DataProvider('variantSettingsProvider')]
    public function testLocalAndGlobalVariantSettings(bool $globalEnabled, bool $localEnabled): void
    {
        $Config = QUI::getPackage('quiqqer/products')->getConfig();
        self::assertNotNull($Config);
        $Config->set('variants', 'findChildrenInSearch', (int)$globalEnabled);
        $Config->save();

        $products = ProductSearch::getProducts(
            ['productIds' => [self::CHILD]],
            $localEnabled,
            1,
            'id ASC'
        );

        $expected = $globalEnabled && $localEnabled ? [self::CHILD] : [];
        self::assertSame($expected, self::ids($products));
    }

    /** @return array<string, array{bool, bool}> */
    public static function variantSettingsProvider(): array
    {
        return [
            'both enabled' => [true, true],
            'globally disabled' => [false, true],
            'locally disabled' => [true, false],
            'both disabled' => [false, false]
        ];
    }

    public function testSelectionSortAndLimitArePreserved(): void
    {
        self::deactivateParent();
        $products = ProductSearch::getProducts(
            ['productIds' => [self::FIRST, self::SECOND, self::CHILD]],
            true,
            1,
            'orderCount DESC'
        );
        self::assertSame([self::SECOND], self::ids($products));

        $products = ProductSearch::getProducts(
            ['categories' => [self::CATEGORY]],
            true,
            2,
            'name ASC'
        );
        self::assertSame([self::FIRST, self::SECOND], self::ids($products));

        self::assertSame([], ProductSearch::getProducts(['productIds' => []], true, 1, 'id ASC'));
    }

    private static function deactivateParent(): void
    {
        self::connection()->update(Tables::getProductTableName(), ['active' => 0], ['id' => self::PARENT]);
        self::connection()->update(Tables::getProductCacheTableName(), ['active' => 0], ['id' => self::PARENT]);
    }

    /**
     * @param array<int, Product> $products
     * @return array<int, int>
     */
    private static function ids(array $products): array
    {
        return array_map(static fn(Product $Product): int => $Product->getId(), $products);
    }

    private static function cleanFixtures(): void
    {
        foreach (range(self::FIRST, self::CHILD) as $id) {
            self::connection()->delete(Tables::getProductCacheTableName(), ['id' => $id]);
            self::connection()->delete(Tables::getProductTableName(), ['id' => $id]);
            Products::invalidateProductCache($id);
        }
    }

    private static function connection(): Connection
    {
        return QUI::getDataBaseConnection();
    }
}
