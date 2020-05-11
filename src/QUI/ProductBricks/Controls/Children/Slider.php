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
            'class'           => 'quiqqer-productbricks-slider',
            'nodeName'        => 'section',
            'entryHeight'     => 400,
            'hideRetailPrice' => false, // hide crossed out price
        ]);

        $this->addCSSFile(\dirname(__FILE__) . '/Slider.css');

        $this->Slider = new ChildrenSlider();
    }

    public function getBody()
    {
        $Engine     = QUI::getTemplateManager()->getEngine();
        $productIds = $this->getAttribute('productIds');
        $products   = [];

        if ($productIds) {
            $productIds = \explode(',', $productIds);
            $products   = QUI\ERP\Products\Handler\Products::getProducts([
                'where' => [
                    'id' => [
                        'type'  => 'IN',
                        'value' => $productIds
                    ],
                ],
                'order' => 'sort ASC, c_date ASC',
                'limit' => 10
            ]);
        }

        $catIds          = $this->getAttribute('categoryIds');
        $productsFromCat = [];

        if ($catIds) {
            $catIds = \explode(',', $catIds);
            foreach ($catIds as $catId) {
                $products = QUI\ERP\Products\Handler\Products::getProducts([
                    'where' => [
                        'categories' => [
                            'type'  => '%LIKE%',
                            'value' => $catId
                        ]
                    ],
                    'order' => 'sort ASC, c_date ASC',
                    'limit' => 10
                ]);

                $productsFromCat = \array_merge($products, $productsFromCat);
            }
        }

        $products = \array_merge($products, $productsFromCat);


        if (\count($products) < 1) {
            return '';
        }

        // sort by c_date desc
        // todo implement as setting
        \usort($products, function ($a, $b) {
            return \strtotime($b->getAttribute('c_date')) - \strtotime($a->getAttribute('c_date'));
        });

        // todo implement as setting
        $products = \array_slice($products, 0, 10);

        foreach ($products as $Product) {
            $this->Slider->addProduct($Product->getViewFrontend());
        }

        $this->Slider->setAttribute('height', $this->getAttribute('entryHeight'));

        $Engine->assign([
            'this'   => $this,
            "Slider" => $this->Slider
        ]);

        return $Engine->fetch(\dirname(__FILE__) . '/Slider.html');
    }
}
