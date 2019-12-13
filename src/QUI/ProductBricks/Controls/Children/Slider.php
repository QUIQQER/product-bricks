<?php

/**
 * This file contains QUI\ProductBricks\Controls\Children\Slider
 *
 * Slider for products, witch can be horizontally scrolled (carousel).
 */

namespace QUI\ProductBricks\Controls\Children;

use QUI;
use QUI\ERP\Products\Controls\Products\ChildrenSlider;
use QUI\ERP\Products\Handler\Fields;
use QUI\ERP\Products\Handler\Products;

/**
 * Class Slider (carousel)
 *
 * @package QUI\Bricks\Controls
 * @author www.pcsg.de (Michael Danielczok)
 */
class Slider extends QUI\Control
{
    /**
     * @var null|ChildrenSlider
     */
    protected $Slider = null;

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
            'class'             => 'quiqqer-productbricks-slider',
            'nodeName'          => 'section',
            'entryHeight'       => 400,
            'hideRetailPrice'   => false, // hide crossed out price
        ]);

        $this->addCSSFile(\dirname(__FILE__) . '/Slider.css');

        $this->Slider = new ChildrenSlider();

    }

    public function getBody()
    {
        $Engine     = QUI::getTemplateManager()->getEngine();
        $productIds = $this->getAttribute('productIds');
        $productIds = explode(',', $productIds);

        foreach ($productIds as $productId) {
            try {
                $this->Slider->addProduct(Products::getProduct($productId)->getViewFrontend());
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        $this->Slider->setAttribute('height', $this->getAttribute('entryHeight'));

        $Engine->assign([
            'this'   => $this,
            "Slider" => $this->Slider
        ]);

        return $Engine->fetch(\dirname(__FILE__) . '/Slider.html');
    }
}
