<?php

/**
 * This file contains QUI\ProductBricks\Controls\FeaturedProductsListSimple
 */

namespace QUI\ProductBricks\Controls;

use QUI;

/**
 * Class CategoryBox
 *
 * @package QUI\Bricks\Controls
 */
class FeaturedProductsListSimple extends QUI\Control
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
            'box1.categoryId' => false,
            'template'        => dirname(__FILE__) . '/PromoBox.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/PromoBox.css');
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();


        $Engine->assign([
            'this' => $this
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }
}
