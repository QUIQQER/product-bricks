<?php

/**
 * This file contains QUI\ProductBricks\Controls\ProductCards
 *
 * Show products in a grid
 */

namespace QUI\ProductBricks\Controls;

use QUI;
use QUI\ERP\Products\Handler\Fields;
use QUI\ProductBricks\Utils\ProductSearch;

use function array_merge;
use function array_slice;
use function array_values;
use function count;
use function dirname;
use function explode;

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
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        // default options
        $this->setAttributes([
            'class' => 'quiqqer-productbricks-productCards',
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

    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();
        $productIds = $this->getAttribute('productIds');
        $products = [];
        $order = $this->getAttribute('order');
        $limit = $this->getAttribute('limit');
        $perRow = $this->getAttribute('perRow');
        $moreUrl = $this->getAttribute('moreUrl');

        if (!$order) {
            $order = 'orderCount DESC';
        }

        if (!$limit || $limit < 1) {
            $limit = 10;
        }

        if (!$perRow || $perRow < 1) {
            $perRow = 3;
        }

        if ($productIds) {
            $products = ProductSearch::getProducts(
                ['productIds' => explode(',', $productIds)],
                (bool)$this->getAttribute('showVariantChildren'),
                (int)$limit,
                $order
            );
        }

        $catIds = $this->getAttribute('categoryIds');
        $productsFromCat = [];

        if (is_string($catIds) && strlen($catIds) > 0) {
            $catIds = explode(',', $catIds);

            foreach ($catIds as $catId) {
                $catProducts = ProductSearch::getProducts(
                    ['categories' => [(int)$catId]],
                    (bool)$this->getAttribute('showVariantChildren'),
                    (int)$limit,
                    $order
                );

                $productsFromCat = array_merge($catProducts, $productsFromCat);
            }
        }

        $products = array_merge($products, $productsFromCat);

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

        $products = array_values($products);

        if (count($products) < 1) {
            return '';
        }

        // limit / max
        $products = array_slice($products, 0, $limit);

        $Engine->assign([
            'this' => $this,
            'productsData' => $this->getProductsData($products),
            'moreUrl' => $moreUrl
        ]);

        return $Engine->fetch($this->getHtmlFilePath());
    }

    /**
     * Get path to html file
     *
     * @return string
     */
    protected function getHtmlFilePath(): string
    {
        return dirname(__FILE__) . '/ProductCards.html';
    }

    /**
     * Get path to css file
     *
     * @return string
     */
    protected function getCSSFilePath(): string
    {
        return dirname(__FILE__) . '/ProductCards.css';
    }

    /**
     * Get products data as array
     *
     * @param array<int, QUI\ERP\Products\Product\Product> $products
     * @return array<int, array<string, mixed>>
     * @throws QUI\Exception
     */
    protected function getProductsData(array $products): array
    {
        $productsData = [];

        /* @var $Product QUI\ERP\Products\Interfaces\ProductInterface */
        foreach ($products as $Product) {
            $ProductView = $Product->getViewFrontend();

            $data = [
                'Product' => $ProductView
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

    /**
     * Get retail price object
     *
     * @param $Product QUI\ERP\Products\Product\ViewFrontend
     * @return QUI\ERP\Products\Controls\Price | null
     *
     * @throws QUI\Exception
     */
    public function getRetailPrice(QUI\ERP\Products\Product\ViewFrontend $Product): ?QUI\ERP\Products\Controls\Price
    {
        if ($this->getAttribute('hideRetailPrice')) {
            return null;
        }

        $CrossedOutPrice = null;
        $Price = $Product->getPrice();

        try {
            // Offer price (Angebotspreis) - it has higher priority than retail price
            if ($Product->hasOfferPrice()) {
                $OriginalPrice = $Product->getOriginalPrice();
                $Currency = QUI\ERP\Currency\Handler::getDefaultCurrency();

                if (
                    (
                        $OriginalPrice instanceof QUI\ERP\Money\Price
                        || $OriginalPrice instanceof QUI\ERP\Products\Interfaces\UniqueFieldInterface
                    )
                    && $Currency !== null
                ) {
                    $CrossedOutPrice = new QUI\ERP\Products\Controls\Price([
                        'Price' => new QUI\ERP\Money\Price(
                            $OriginalPrice->getValue(),
                            $Currency
                        ),
                        'withVatText' => false
                    ]);
                }
            } else {
                // retail price (UVP)
                if ($Product->getFieldValue('FIELD_PRICE_RETAIL')) {
                    $PriceRetailField = $Product->getCalculatedPrice(Fields::FIELD_PRICE_RETAIL);

                    if ($PriceRetailField === null) {
                        return null;
                    }

                    $PriceRetail = $PriceRetailField->getPrice();

                    if ($Price->getPrice() < $PriceRetail->getPrice()) {
                        $CrossedOutPrice = new QUI\ERP\Products\Controls\Price([
                            'Price' => $PriceRetail,
                            'withVatText' => false
                        ]);
                    }
                }
            }

            return $CrossedOutPrice;
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
        }

        return null;
    }
}
