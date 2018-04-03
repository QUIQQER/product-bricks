<?php

/**
 * This file contains QUI\ProductBricks\Controls\Children\Slider
 *
 * Slider for products, witch can be horizontally scrolled,
 * if there are more items as possible to show.
 */

namespace QUI\ProductBricks\Controls\Children;

use QUI;
//use QUI\ERP\Products\Controls\Products\ChildrenSlider;

use QUI\ERP\Products\Handler\Products;


/**
 * Class Slider
 *
 * @package QUI\Bricks\Controls
 * @author www.pcsg.de (Michael Danielczok)
 */
class Slider extends QUI\ERP\Products\Controls\Products\ChildrenSlider
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
            'class'        => 'quiqqer-productbricks-slider',
            'nodeName'     => 'section',
            'templateHTML' => dirname(__FILE__) . '/Slider.html',
            'templateCSS'  => dirname(__FILE__) . '/Slider.css'
        ]);

    }

    public function getBody()
    {
        /*$Engine = QUI::getTemplateManager()->getEngine();
        $Slider = new ChildrenSlider([
            'height' => $this->getAttribute('height')
        ]);


        $productIds = $this->getAttribute('productIds');
        $productIds = explode(',', $productIds);

        $products = [];

        foreach ($productIds as $productId) {
            try {
                $Product    = Products::getProduct($productId);
                $products[] = $Product->getView();
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        $Slider->addProducts($products);

        $Engine->assign([
            'this'   => $this,
            'Slider' => $Slider
        ]);*/

        $Engine = QUI::getTemplateManager()->getEngine();
//        $products = array();

        $productIds = $this->getAttribute('productIds');
        $productIds = explode(',', $productIds);
        $products   = [];

        foreach ($productIds as $productId) {
            try {
                $Product    = Products::getProduct($productId);
                $products[] = [
                    'Product' => $Product,
                    'Price'   => new QUI\ERP\Products\Controls\Price([
                        'Price' => $Product->getPrice()
                    ])
                ];
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }


        if (!$this->getAttribute('height')) {
            $this->setAttribute('height', 350);
        }


        $Engine->assign([
            'this'     => $this,
            'products' => $products
        ]);

        $this->addCSSFile($this->getAttribute('templateCSS'));

        return $Engine->fetch($this->getAttribute('templateHTML'));
    }
}
