<?php

declare(strict_types=1);

namespace QUITests\ProductBricks\Controls\Slider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ProductBricks\Controls\Slider\ManufactureSlider;
use ReflectionProperty;

class ManufactureSliderTest extends TestCase
{
    private Connection $originalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConnection = QUI::getDataBaseConnection();
        $Connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true
        ]);
        $Schema = new Schema();
        $Table = $Schema->createTable(QUI::getUsers()::table());
        $Table->addColumn('id', 'integer');
        $Table->addColumn('username', 'string');
        $Table->addColumn('c_date', 'string');
        $Table->addColumn('e_date', 'string');
        $Table->setPrimaryKey(['id']);

        foreach ($Schema->toSql($Connection->getDatabasePlatform()) as $statement) {
            $Connection->executeStatement($statement);
        }

        $Connection->insert(QUI::getUsers()::table(), [
            'id' => 1,
            'username' => 'Charlie',
            'c_date' => '2024-01-01',
            'e_date' => '2024-03-01'
        ]);
        $Connection->insert(QUI::getUsers()::table(), [
            'id' => 2,
            'username' => 'Alice',
            'c_date' => '2024-03-01',
            'e_date' => '2024-02-01'
        ]);
        $Connection->insert(QUI::getUsers()::table(), [
            'id' => 3,
            'username' => 'Bob',
            'c_date' => '2024-02-01',
            'e_date' => '2024-01-01'
        ]);

        $this->setConnection($Connection);
    }

    protected function tearDown(): void
    {
        $this->setConnection($this->originalConnection);

        parent::tearDown();
    }

    public function testFetchesOnlyManufacturersUsingConfiguredOrderAndLimit(): void
    {
        $Slider = $this->createTestSlider('username DESC');

        self::assertSame([1, 3], $Slider->getOrderedIds([1, 2, 3], 2));
        self::assertSame([3], $Slider->getOrderedIds([3], 10));
        self::assertSame([], $Slider->getOrderedIds([], 10));
        self::assertSame([], $Slider->getOrderedIds([1, 2, 3], 0));
    }

    public function testInvalidOrderFallsBackToUsernameAscending(): void
    {
        $Slider = $this->createTestSlider('username DESC; DROP TABLE users');

        self::assertSame([2, 3, 1], $Slider->getOrderedIds([1, 2, 3], 10));
    }

    /**
     * @return ManufactureSlider&object{getOrderedIds: callable(array<int, int|string>, int): list<int>}
     */
    private function createTestSlider(string $order): ManufactureSlider
    {
        return new class (['order' => $order]) extends ManufactureSlider {
            /**
             * @param array<int, int|string> $userIds
             * @return list<int>
             */
            public function getOrderedIds(array $userIds, int $limit): array
            {
                return $this->getOrderedManufacturerUserIds($userIds, $limit);
            }
        };
    }

    private function setConnection(Connection $Connection): void
    {
        $QueryBuilder = new ReflectionProperty(QUI::class, 'QueryBuilder');
        $QueryBuilder->setValue(null, $Connection);
    }
}
