<?php

/**
 * This file contains QUI\ProductBricks\Controls\Slider\ProductSlider
 */

namespace QUI\ProductBricks\Controls\Slider;

use QUI;
use QUI\ERP\Products\Handler\Products;


/**
 * Class ProductSlider
 *
 * @package QUI\Bricks\Controls
 */
class ProductSlider extends QUI\Control
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
            'title'     => '',
            'text'      => '',
            'class'     => 'quiqqer-productbricks-productslider',
            'showPrice' => false,
            'nodeName'  => 'section',
            'autostart' => false,
            'delay'     => 5000,
            'template'  => dirname(__FILE__) . '/ProductSlider.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/ProductSlider.css');
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();
        $Slider = new QUI\Bricks\Controls\Slider\Promoslider([
            'shownavigation' => true,
            'showarrows'     => $this->getAttribute('showHoverScale'),
            'autostart'      => $this->getAttribute('autostart'),
            'delay'          => $this->getAttribute('delay'),
            'template'       => dirname(__FILE__) . '/ProductSlider.Template.html'
        ]);

        $this->setStyle('background-color', $this->getAttribute('bgColor'));
        $this->setStyle('background-image', 'url(' . $this->getAttribute('bgImage') . ')');

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

        /* @var $Product QUI\ERP\Products\Product\Product */
        foreach ($products as $Product) {

            $btnLabel = QUI::getLocale()->get(
                'quiqqer/product-bricks',
                'brick.control.productSlider.buyNow'
            );

            $priceHtml = '';

            if ($this->getAttribute('showPrice')) {
                $priceHtml = $this->getPriceHtml($Product);
            }

            $text = '<p class="slide-product-description">' . $Product->getDescription() . '</p>';
            $text .= $priceHtml;
            $text .= '<p><button class="btn btn-primary btn-large">' . $btnLabel . '</button>';

            $Slider->addSlide(
                $Product->getImage()->getUrl(),
                $Product->getTitle(),
                $text,
                'left',
                $Product->getUrl()
            );

            $Slider->addMobileSlide(
                $Product->getImage()->getUrl(),
                $Product->getTitle(),
                $text,
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
     * @param $Product QUI\ERP\Products\Product\Product
     * @return string HTML
     */
    private function getPriceHtml($Product)
    {
        $html            = '<p class="slide-product-prices">';
        $retailPriceHtml = '';

        if ($Product->getFieldValue(QUI\ERP\Products\Handler\Fields::FIELD_PRICE_RETAIL)) {
            // todo UVP mit Peat's hilfe. Frage an Hen: ist getDefaultCurrency() ok?
            $RetailPrice = new QUI\ERP\Money\Price(
                $Product->getFieldValue(QUI\ERP\Products\Handler\Fields::FIELD_PRICE_RETAIL),
                QUI\ERP\Currency\Handler::getDefaultCurrency()
            );

            $retailPriceHtml = '<span class="slide-product-prices-retail">';
            $retailPriceHtml .= $RetailPrice->getDisplayPrice() . '</span>';
        }

        $html .= $retailPriceHtml;
        $html .= '<span class="slide-product-prices-price">' . $Product->getPrice()->getDisplayPrice();
        $html .= '</span></p>';

        return $html;
    }
}
