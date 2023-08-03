<?php

/**
 * This file contains QUI\ProductBricks\Controls\ProductCardsDetails
 *
 * Show products with their attributes in a grid
 */

namespace QUI\ProductBricks\Controls;

use QUI;

/**
 * Product Cards
 *
 * Show products cards in a grid and show product details (attributes)
 *
 * @package QUI\Bricks\Controls
 * @author www.pcsg.de (Michael Danielczok)
 */
class ProductCardsDetails extends QUI\ProductBricks\Controls\ProductCards
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
            'class' => 'quiqqer-productbricks-productCardsDetails',
            'nodeName' => 'section',
            'hideRetailPrice' => false, // hide crossed out price
            'showPrices' => true,  // do not show prices
            'showVariantChildren' => false,  // also show variant children products
            'productIds' => '',
            'categoryIds' => '',
            'buttonAction' => 'addToBasket',
            'moreUrl' => '', // url to more products
            'order' => 'orderCount DESC', // best sellers
            'limit' => 6,
            'perRow' => 3,
            'imgBg' => true, // light background behind the images
            'showButtons' => true
        ]);

        parent::__construct($attributes);

        $this->setAttribute('cacheable', 0);

        $this->addCSSFile($this->getCSSFilePath());
    }

    /**
     * Get path to html file
     *
     * @return string
     */
    protected function getHtmlFilePath(): string
    {
        return \dirname(__FILE__) . '/ProductCardsDetails.html';
    }

    /**
     * Get path to css file
     *
     * @return string
     */
    protected function getCSSFilePath(): string
    {
        return \dirname(__FILE__) . '/ProductCardsDetails.css';
    }

    /**
     * Get products data as array
     *
     * @param array $products
     * @return array
     * @throws QUI\Exception
     */
    protected function getProductsData(array $products): array
    {
        $productsData = [];

        /* @var $Product QUI\ERP\Products\Interfaces\ProductInterface */
        foreach ($products as $Product) {
            $ProductView = $Product->getViewFrontend();

            // fields for the details
            $details = \array_filter($ProductView->getFields(), function ($Field) {
                /* @var $Field QUI\ERP\Products\Field\View */
                if (!QUI\ERP\Products\Utils\Fields::showFieldInProductDetails($Field)) {
                    return false;
                }

                return $Field->hasViewPermission();
            });

            $data = [
                'Product' => $ProductView,
                'details' => $details
            ];

            if ($this->getAttribute('showPrices')) {
                $data['Price'] = new QUI\ERP\Products\Controls\Price([
                    'Price' => $ProductView->getPrice()
                ]);

                $data['RetailPrice'] = $this->getRetailPrice($ProductView);
            }

            $productsData[] = $data;
        }

        return $productsData;
    }
}
