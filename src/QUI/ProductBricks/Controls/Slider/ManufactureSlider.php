<?php

/**
 * This file contains QUI\ProductBricks\Controls\Slider\ManufactureSlider
 */

namespace QUI\ProductBricks\Controls\Slider;

use QUI;
use QUI\ERP\Products\Handler\Manufacturers as ManufacturersHandler;

/**
 * Class ChildrenSlider
 *
 * @author www.pcsg.de (Michael Danielczok)
 */
class ManufactureSlider extends QUI\Bricks\Controls\Children\Slider
{
    /**
     * ChildrenSlider constructor.
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        // default options
        $this->setAttributes([
            'moreLink' => false,
            'height'   => 200,
            'limit'    => 20,
            'order'    => 'username ASC'
        ]);

        parent::__construct($attributes);

        $this->addCSSFile(
            \dirname(__FILE__) . '/ManufactureSlider.css'
        );
    }

    /**
     * (non-PHPdoc)
     *
     * @see \QUI\Control::create()
     */
    public function getBody()
    {
        try {
            $Engine = QUI::getTemplateManager()->getEngine();
        } catch (QUI\Exception $Exception) {
            return '';
        }

        $height = $this->getAttribute('height');
        $this->setAttribute('height', false);

        if (!$height) {
            $height = 200;
        }

        $manufacturerUserIds = [];
        $start               = 0;
        $limit               = $this->getAttribute('limit');
        $Users               = QUI::getUsers();
        $MoreLink            = null;

        try {
            $userIds = ManufacturersHandler::getManufacturerUserIds();

            if (!empty($userIds)) {
                $result = QUI::getDataBase()->fetch([
                    'select' => ['id'],
                    'from'   => $Users::table(),
                    'where'  => [
                        'id' => [
                            'type'  => 'IN',
                            'value' => $userIds
                        ]
                    ],
                    'order'  => $this->getAttribute('order'),
                    'limit'  => $start . ',' . $limit
                ]);

                foreach ($result as $row) {
                    $manufacturerUserIds[] = $row['id'];
                }
            }
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception, QUI\System\Log::LEVEL_NOTICE);
        }

        if ($this->getAttribute('moreLink')) {
            try {
                $MoreLink = QUI\Projects\Site\Utils::getSiteByLink($this->getAttribute('moreLink'));
            } catch (QUI\Exception $Exception) {
            }
        }

        // sort alphabetically
//        \usort($manufacturerUserIds, function ($userIdA, $userIdB) {
//            /**
//             * @var int $userIdA
//             * @var int $userIdB
//             */
//            return \strnatcmp(
//                ManufacturersHandler::getManufacturerTitle($userIdA),
//                ManufacturersHandler::getManufacturerTitle($userIdB)
//            );
//        });

        $Engine->assign([
            'this'              => $this,
            'height'            => $height,
            'manufacturerUsers' => $manufacturerUserIds,
            'MoreLink'          => $MoreLink
        ]);

        return $Engine->fetch(\dirname(__FILE__) . '/ManufactureSlider.html');
    }
}
