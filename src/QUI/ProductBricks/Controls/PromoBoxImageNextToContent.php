<?php

/**
 * This file contains QUI\ProductBricks\Controls\PromoBoxImageNextToContent
 */

namespace QUI\ProductBricks\Controls;

use QUI;

/**
 * Class CategoryBox
 *
 * @package QUI\Bricks\Controls
 */
class PromoBoxImageNextToContent extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        // default options
        $this->setAttributes([
            'class' => 'quiqqer-productbricks-promoboxImageNextToContent',
            'nodeName' => 'section',
            'backgroundColor' => '#fff',
            'minHeight' => 400,
            'contentPosition' => 'center',
            'image' => false,
            'url' => false,
            'target' => '_self',
            'template' => dirname(__FILE__) . '/PromoBoxImageNextToContent.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/PromoBoxImageNextToContent.css');

        parent::__construct($attributes);
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        $Engine->assign([
            'this' => $this,
            'minHeight' => $this->getAttribute('minHeight'),
            'image' => $this->getAttribute('image'),
            'contentPosition' => $this->getAttribute('contentPosition'),
            'url' => $this->getAttribute('url'),
            'target' => $this->getAttribute('target'),
            'backgroundColor' => $this->getAttribute('backgroundColor'),
            'layout' => 'layout-' . $this->getAttribute('layout')
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }
}
