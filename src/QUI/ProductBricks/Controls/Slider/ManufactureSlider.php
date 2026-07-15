<?php

/**
 * This file contains QUI\ProductBricks\Controls\Slider\ManufactureSlider
 */

namespace QUI\ProductBricks\Controls\Slider;

use Doctrine\DBAL\ArrayParameterType;
use Exception;
use QUI;
use QUI\ERP\Products\Handler\Manufacturers as ManufacturersHandler;

use function array_map;
use function dirname;

/**
 * Class ChildrenSlider
 *
 * @author www.pcsg.de (Michael Danielczok)
 */
class ManufactureSlider extends QUI\Bricks\Controls\Children\Slider
{
    /**
     * ChildrenSlider constructor.
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        // default options
        $this->setAttributes([
            'moreLink' => false,
            'height' => 120,
            'limit' => 10,
            'order' => 'username ASC'
        ]);

        parent::__construct($attributes);

        $this->setAttribute('cacheable', 0);
        $this->addCSSFile(dirname(__FILE__) . '/ManufactureSlider.css');
    }

    /**
     * @see \QUI\Control::create()
     */
    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();
        $height = $this->getAttribute('height');
        $limit = $this->getAttribute('limit');

        $this->setAttribute('height', false);

        if (!$height) {
            $height = 120;
        }

        if (!$limit) {
            $limit = 10;
        }

        $manufacturerUserIds = [];
        $MoreLink = null;

        try {
            $userIds = ManufacturersHandler::getManufacturerUserIds(true);

            if (!empty($userIds)) {
                $manufacturerUserIds = $this->getOrderedManufacturerUserIds($userIds, (int)$limit);
            }
        } catch (Exception $Exception) {
            QUI\System\Log::writeException($Exception, QUI\System\Log::LEVEL_NOTICE);
        }

        if ($this->getAttribute('moreLink')) {
            try {
                $MoreLink = QUI\Projects\Site\Utils::getSiteByLink($this->getAttribute('moreLink'));
            } catch (QUI\Exception) {
            }
        }

        $Engine->assign([
            'this' => $this,
            'height' => $height,
            'manufacturerUsers' => $manufacturerUserIds,
            'MoreLink' => $MoreLink
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/ManufactureSlider.html');
    }

    /**
     * @param array<int, int|string> $userIds
     * @return list<int>
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getOrderedManufacturerUserIds(array $userIds, int $limit): array
    {
        if ($userIds === [] || $limit < 1) {
            return [];
        }

        $allowedOrders = [
            'username ASC' => ['username', 'ASC'],
            'username DESC' => ['username', 'DESC'],
            'c_date ASC' => ['c_date', 'ASC'],
            'c_date DESC' => ['c_date', 'DESC'],
            'e_date ASC' => ['e_date', 'ASC'],
            'e_date DESC' => ['e_date', 'DESC']
        ];
        $order = (string)$this->getAttribute('order');
        [$orderField, $orderDirection] = $allowedOrders[$order] ?? $allowedOrders['username ASC'];

        $Connection = QUI::getDataBaseConnection();
        $Platform = $Connection->getDatabasePlatform();
        $idField = $Platform->quoteSingleIdentifier('id');
        $QueryBuilder = $Connection->createQueryBuilder();
        $result = $QueryBuilder
            ->select($idField)
            ->from($Platform->quoteSingleIdentifier(QUI::getUsers()::table()))
            ->where($QueryBuilder->expr()->in($idField, ':userIds'))
            ->setParameter('userIds', array_map('intval', $userIds), ArrayParameterType::INTEGER)
            ->orderBy($Platform->quoteSingleIdentifier($orderField), $orderDirection)
            ->setFirstResult(0)
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchFirstColumn();

        return array_map(static fn(mixed $id): int => (int)$id, $result);
    }
}
