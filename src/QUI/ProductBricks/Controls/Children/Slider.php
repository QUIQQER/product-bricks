<?php

/**
 * This file contains QUI\ProductBricks\Controls\Children\Slider
 *
 * Slider for products, witch can be horizontally scrolled (carousel).
 */

namespace QUI\ProductBricks\Controls\Children;

use QUI;
use QUI\ERP\Products\Controls\Products\ChildrenSlider;
use QUI\ProductBricks\Utils\ProductSearch;

use function array_merge;
use function array_slice;
use function array_values;
use function count;
use function dirname;
use function explode;

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
    protected ?ChildrenSlider $Slider = null;

    /**
     * constructor
     *
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        // default options
        $this->setAttributes([
            'class' => 'quiqqer-productbricks-slider',
            'nodeName' => 'section',
            'entryHeight' => 400,
            'hideRetailPrice' => false, // hide crossed out price
            'showPrices' => true,  // do not show prices
            'showVariantChildren' => false,  // also show variant children products
            'productIds' => '', // comma separated numbers
            'categoryIds' => '', // comma separated numbers
            'buttonAction' => 'addToBasket',
            'order' => 'orderCount DESC', // best sellers
            'limit' => 10
        ]);

        parent::__construct($attributes);

        $this->setAttribute('cacheable', 0);
        $this->addCSSFile(dirname(__FILE__) . '/Slider.css');
    }

    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();
        $productIds = $this->getAttribute('productIds');
        $products = [];
        $order = $this->getAttribute('order');
        $limit = $this->getProductLimit();

        if (!$order) {
            $order = 'orderCount DESC';
        }

        $Slider = new ChildrenSlider([
            'showPrices' => $this->getAttribute('showPrices'),
            'buttonAction' => $this->getAttribute('buttonAction')
        ]);
        $this->Slider = $Slider;

        $this->addCSSFiles($Slider->getCSSFiles());

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

        foreach ($products as $Product) {
            $Slider->addProduct($Product->getViewFrontend());
        }

        $Slider->setAttribute('height', $this->getAttribute('entryHeight'));

        foreach ($Slider->getCSSFiles() as $file) {
            $this->addCSSFile($file);
        }

        $Engine->assign([
            'this' => $this,
            "Slider" => $Slider
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/Slider.html');
    }

    protected function getProductLimit(): int
    {
        $limit = (int)$this->getAttribute('limit');

        return $limit > 0 ? $limit : 10;
    }
}
