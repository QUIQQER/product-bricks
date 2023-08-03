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
            'height' => 120,
            'limit' => 10,
            'order' => 'username ASC'
        ]);

        parent::__construct($attributes);

        $this->setAttribute('cacheable', 0);
        $this->addCSSFile(\dirname(__FILE__) . '/ManufactureSlider.css');
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
        $limit = $this->getAttribute('limit');

        $this->setAttribute('height', false);

        if (!$height) {
            $height = 120;
        }

        if (!$limit) {
            $limit = 10;
        }

        $manufacturerUserIds = [];
        $start = 0;

        $Users = QUI::getUsers();
        $MoreLink = null;

        try {
            $userIds = ManufacturersHandler::getManufacturerUserIds(true);

            if (!empty($userIds)) {
                $result = QUI::getDataBase()->fetch([
                    'select' => ['id'],
                    'from' => $Users::table(),
                    'where' => [
                        'id' => [
                            'type' => 'IN',
                            'value' => $userIds
                        ]
                    ],
                    'order' => $this->getAttribute('order'),
                    'limit' => $start . ',' . $limit
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

        $Engine->assign([
            'this' => $this,
            'height' => $height,
            'manufacturerUsers' => $manufacturerUserIds,
            'MoreLink' => $MoreLink
        ]);

        return $Engine->fetch(\dirname(__FILE__) . '/ManufactureSlider.html');
    }
}
