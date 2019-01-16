<?php

/**
 * This file contains QUI\ProductBricks\Controls\PromoBox
 */

namespace QUI\ProductBricks\Controls;

use QUI;
use QUI\ERP\Products\Handler\Categories;


/**
 * Class CategoryBox
 *
 * @package QUI\Bricks\Controls
 */
class PromoBox extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        // default options
        $this->setAttributes([
            'class'           => 'quiqqer-productbricks-promobox',
            'nodeName'        => 'section',
            'minHeight'       => 250,
            'contentPosition' => 'center',
            'image'           => false,
            'url'             => false,
            'template'        => dirname(__FILE__) . '/PromoBox.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/PromoBox.css');
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();


        $Engine->assign([
            'this'            => $this,
            'minHeight'       => $this->getAttribute('minHeight'),
            'image'           => $this->getAttribute('image'),
            'contentPosition' => $this->getAttribute('contentPosition'),
            'url'             => $this->getAttribute('url')
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }
}
