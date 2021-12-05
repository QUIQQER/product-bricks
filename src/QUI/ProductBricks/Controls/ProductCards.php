<?php

/**
 * This file contains QUI\ProductBricks\Controls\ProductCards
 *
 * Show products in a grid
 */

namespace QUI\ProductBricks\Controls;

use QUI;
use QUI\ERP\Products\Controls\Products\ChildrenSlider;
use QUI\ERP\Products\Handler\Fields;
use QUI\ERP\Products\Handler\Products;

/**
 * Product Cards
 *
 * Show products cards in a grid
 *
 * @package QUI\Bricks\Controls
 * @author www.pcsg.de (Michael Danielczok)
 */
class ProductCards extends QUI\Control
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
            'class'               => 'quiqqer-productbricks-productCards',
            'nodeName'            => 'section',
            'hideRetailPrice'     => false, // hide crossed out price
            'showPrices'          => true,  // do not show prices
            'showVariantChildren' => false,  // also show variant children products
            'productIds'          => '',
            'categoryIds'         => '',
            'buttonAction'        => 'addToBasket',
            'order'               => 'orderCount DESC', // best sellers
            'limit'               => 6,
            'perRow'              => 3,
            'imgBg'               => true, // light background behind the images
            'showButtons'         => true
        ]);

        parent::__construct($attributes);

        $this->setAttribute('cacheable', 0);

        $this->addCSSFile(\dirname(__FILE__).'/ProductCards.css');
    }

    public function getBody()
    {
        $Engine     = QUI::getTemplateManager()->getEngine();
        $productIds = $this->getAttribute('productIds');
        $products   = [];
        $order      = $this->getAttribute('order');
        $limit      = $this->getAttribute('limit');
        $perRow     = $this->getAttribute('perRow');

        if (!$order) {
            $order = 'orderCount DESC';
        }

        if (!$limit || $limit < 1) {
            $limit = 10;
        }

        if (!$perRow || $perRow < 1) {
            $perRow = 3;
        }

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

                'order' => $order,
                'limit' => $limit
            ]);
        }

        $catIds          = $this->getAttribute('categoryIds');
        $productsFromCat = [];

        if (is_string($catIds) && strlen($catIds) > 0) {
            $catIds = \explode(',', $catIds);

            foreach ($catIds as $catId) {
                $Category = QUI\ERP\Products\Handler\Categories::getCategory($catId);

                $query = [
                    'where' => [
                        'active' => 1,
                    ],
                    'limit' => $limit,
                    'order' => $order
                ];

                // do not show variant children
                if (!$this->getAttribute('showVariantChildren')) {
                    $query['where']['type'] = [
                        'type'  => 'NOT',
                        'value' => QUI\ERP\Products\Product\Types\VariantChild::class
                    ];
                }

                $catProducts = $Category->getProducts($query);

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

        // limit / max
        $products = \array_slice($products, 0, $limit);

        $productsData = [];

        /* @var $Product QUI\ERP\Products\Interfaces\ProductInterface */
        foreach ($products as $Product) {
            $ProductView = $Product->getViewFrontend();

            $details = [
                'Product' => $ProductView
            ];

            if ($this->getAttribute('showPrices')) {
                $details['Price'] = new QUI\ERP\Products\Controls\Price([
                    'Price' => $ProductView->getPrice()
                ]);

                $details['RetailPrice'] = $this->getRetailPrice($ProductView);
            }

            $productsData[] = $details;
        }

        $Engine->assign([
            'this'         => $this,
            'productsData' => $productsData
        ]);

        return $Engine->fetch(\dirname(__FILE__).'/ProductCards.html');
    }

    /**
     * Get retail price object
     *
     * @param $Product QUI\ERP\Products\Product\ViewFrontend
     * @return QUI\ERP\Products\Controls\Price | null
     *
     * @throws QUI\Exception
     */
    public function getRetailPrice($Product)
    {
        if ($this->getAttribute('hideRetailPrice')) {
            return null;
        }

        $CrossedOutPrice = null;
        $Price           = $Product->getPrice();

        try {
            // Offer price (Angebotspreis) - it has higher priority than retail price
            if ($Product->hasOfferPrice()) {
                $CrossedOutPrice = new QUI\ERP\Products\Controls\Price([
                    'Price'       => new QUI\ERP\Money\Price(
                        $Product->getOriginalPrice()->getValue(),
                        QUI\ERP\Currency\Handler::getDefaultCurrency()
                    ),
                    'withVatText' => false
                ]);
            } else {
                // retail price (UVP)
                if ($Product->getFieldValue('FIELD_PRICE_RETAIL')) {
                    $PriceRetail = $Product->getCalculatedPrice(Fields::FIELD_PRICE_RETAIL)->getPrice();

                    if ($Price->getPrice() < $PriceRetail->getPrice()) {
                        $CrossedOutPrice = new QUI\ERP\Products\Controls\Price([
                            'Price'       => $PriceRetail,
                            'withVatText' => false
                        ]);
                    }
                }
            }

            return $CrossedOutPrice;
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
        }
    }
}