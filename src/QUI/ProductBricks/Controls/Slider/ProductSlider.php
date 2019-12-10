<?php

/**
 * This file contains QUI\ProductBricks\Controls\Slider\ProductSlider
 *
 * This slider can be used in page header.
 */

namespace QUI\ProductBricks\Controls\Slider;

use QUI;
use QUI\ERP\Products\Handler\Fields;
use QUI\ERP\Products\Handler\Products;

/**
 * Class ProductSlider (promo slider)
 *
 *
 * @package QUI\Bricks\Controls
 */
class ProductSlider extends QUI\Control
{
    /**
     * @var QUI\ERP\Products\Utils\Calc
     */
    private $Calc;

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
            'title'          => '',
            'text'           => '',
            'class'          => 'quiqqer-productbricks-productslider',
            'showPrice'      => false,
            'nodeName'       => 'section',
            'autostart'      => false,
            'delay'          => 7000,
            'dotsAppearance' => 'dark', // slider navigation dots
            'template'       => dirname(__FILE__) . '/ProductSlider.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/ProductSlider.css');
    }

    public function getBody()
    {
        $this->Calc = QUI\ERP\Products\Utils\Calc::getInstance(QUI::getUserBySession());

        $Engine = QUI::getTemplateManager()->getEngine();
        $Slider = new QUI\Bricks\Controls\Slider\Promoslider([
            'shownavigation' => true,
            'showarrows'     => 'showHoverScale',
            'autostart'      => $this->getAttribute('autostart'),
            'delay'          => $this->getAttribute('delay'),
            'imageSize'      => 400
        ]);

        $this->setStyle('background-color', $this->getAttribute('bgColor'));
        $this->setStyle('background-image', 'url(' . $this->getAttribute('bgImage') . ')');
        $this->setAttribute('data-dots-appearance', $this->getAttribute('dotsAppearance'));

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

        $slideTemplate = dirname(__FILE__) . '/ProductSlider.Slide.html';

        /* @var $Product QUI\ERP\Products\Product\ViewFrontend */
        foreach ($products as $Product) {

            $EngineSlide = QUI::getTemplateManager()->getEngine();

            $priceHtml = '';

            if ($this->getAttribute('showPrice')) {
                $priceHtml = $this->getPriceHtml($Product);
            }

            $EngineSlide->assign([
                'Product'   => $Product,
                'priceHtml' => $priceHtml
            ]);

            $Slider->addSlide(
                false,
                false,
                $EngineSlide->fetch($slideTemplate),
                'left',
                $Product->getUrl()
            );

            $Slider->addMobileSlide(
                false,
                false,
                $EngineSlide->fetch($slideTemplate),
                'left',
                $Product->getUrl()
            );
        }

        $Engine->assign([
            'Slider' => $Slider
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }

    /**
     * Get parsed html with price and retail price.
     *
     * @param $Product QUI\ERP\Products\Product\ViewFrontend
     * @return string HTML
     *
     * @throws QUI\Exception
     */
    private function getPriceHtml($Product)
    {
        $html            = '<div class="quiqqer-productbricks-productslider-slide-left-description-prices">';
        $retailPriceHtml = '';
        $CrossedOutPrice = null;
        $Price           = $Product->getPrice();
        $PriceDisplay    = $Product->getPriceDisplay();

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

            if ($CrossedOutPrice) {
                $retailPriceHtml = '<div class="quiqqer-productbricks-productslider-slide-left-description-prices-retail text-muted">';
                $retailPriceHtml .= $CrossedOutPrice->create() . '</div>';
            }
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
        }

        $html .= $retailPriceHtml;
        $html .= '<div class="quiqqer-productbricks-productslider-slide-left-description-prices-price">' . $PriceDisplay->create();
        $html .= '</div></div>';

        return $html;
    }
}
