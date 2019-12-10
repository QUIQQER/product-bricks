<?php

/**
 * This file contains QUI\ProductBricks\Controls\Children\Slider
 *
 * Slider for products, witch can be horizontally scrolled,
 * if there are more items as possible to show.
 */

namespace QUI\ProductBricks\Controls\Children;

use QUI;
use QUI\ERP\Products\Handler\Fields;
use QUI\ERP\Products\Handler\Products;

/**
 * Class Slider (carousel)
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
            'class'           => 'quiqqer-productbricks-slider',
            'nodeName'        => 'section',
            'height'          => 400,
            'hideRetailPrice' => false, // hide crossed out price
            'templateHTML'    => dirname(__FILE__) . '/Slider.html',
            'templateCSS'     => dirname(__FILE__) . '/Slider.css'
        ]);
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        $productIds = $this->getAttribute('productIds');
        $productIds = explode(',', $productIds);
        $products   = [];

        foreach ($productIds as $productId) {
            try {
                $Product = Products::getProduct($productId)->getViewFrontend();

                $products[] = [
                    'Product'     => $Product,
                    'Price'       => new QUI\ERP\Products\Controls\Price([
                        'Price' => $Product->getPrice()
                    ]),
                    'RetailPrice' => $this->getRetailPrice($Product)
                ];
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        if (!$this->getAttribute('height')) {
            $this->setAttribute('height', 400);
        }

        $Engine->assign([
            'this'     => $this,
            'products' => $products
        ]);

        $this->addCSSFile($this->getAttribute('templateCSS'));

        return $Engine->fetch($this->getAttribute('templateHTML'));
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
