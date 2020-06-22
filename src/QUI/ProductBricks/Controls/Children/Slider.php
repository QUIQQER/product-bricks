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
            'class'               => 'quiqqer-productbricks-slider',
            'nodeName'            => 'section',
            'entryHeight'         => 400,
            'hideRetailPrice'     => false, // hide crossed out price
            'showPrices'          => true,  // do not show prices
            'showVariantChildren' => false,  // also show VariantChildren products
            'buttonAction'        => 'addToBasket'

        ]);

        $this->addCSSFile(\dirname(__FILE__) . '/Slider.css');
    }

    public function getBody()
    {
        $Engine     = QUI::getTemplateManager()->getEngine();
        $productIds = $this->getAttribute('productIds');
        $products   = [];

        $this->Slider = new ChildrenSlider([
            'showPrices'   => $this->getAttribute('showPrices'),
            'buttonAction' => $this->getAttribute('buttonAction')
        ]);

        $allowedProductClasses = [
            '', // fix for old products
            QUI\ERP\Products\Product\Types\Product::class,
            QUI\ERP\Products\Product\Types\VariantParent::class
        ];

        if ($this->getAttribute('showVariantChildren')) {
            $allowedProductClasses[] = QUI\ERP\Products\Product\Types\VariantChild::class;
        }

        if ($productIds) {
            $productIds = \explode(',', $productIds);
            $products   = QUI\ERP\Products\Handler\Products::getProducts([
                'where' => [
                    'active' => 1,
                    'id'     => [
                        'type'  => 'IN',
                        'value' => $productIds
                    ],
                    'type'   => [
                        'type'  => 'IN',
                        'value' => $allowedProductClasses
                    ]
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
                $catProducts = QUI\ERP\Products\Handler\Products::getProducts([
                    'where' => [
                        'active'     => 1,
                        'categories' => [
                            'type'  => '%LIKE%',
                            'value' => ',' . $catId . ','
                        ],
                        'type'       => [
                            'type'  => 'IN',
                            'value' => $allowedProductClasses
                        ]
                    ],
                    'order' => 'sort ASC, c_date ASC',
                    'limit' => 10
                ]);

                $productsFromCat = \array_merge($catProducts, $productsFromCat);
            }
        }

        $products = \array_merge($products, $productsFromCat);

        // Remove duplicates
        $checked = [];

        /** @var QUI\ERP\Products\Product\Product $Product */
        foreach ($products as $k => $Product) {
            if (isset($checked[$Product->getId()])) {
                unset($products[$k]);
                continue;
            }

            $checked[$Product->getId()] = true;
        }

        $products = \array_values($products);

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
