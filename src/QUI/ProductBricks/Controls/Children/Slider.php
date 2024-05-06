<?php

/**
 * This file contains QUI\ProductBricks\Controls\Children\Slider
 *
 * Slider for products, witch can be horizontally scrolled (carousel).
 */

namespace QUI\ProductBricks\Controls\Children;

use QUI;
use QUI\ERP\Products\Controls\Products\ChildrenSlider;

use function array_filter;
use function array_merge;
use function array_slice;
use function array_values;
use function array_walk;
use function count;
use function dirname;
use function explode;
use function is_a;
use function ltrim;

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
     * @param array $attributes
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
        $limit = $this->getAttribute('limit');

        if (!$order) {
            $order = 'orderCount DESC';
        }

        if (!$limit) {
            $order = 10;
        }

        $this->Slider = new ChildrenSlider([
            'showPrices' => $this->getAttribute('showPrices'),
            'buttonAction' => $this->getAttribute('buttonAction')
        ]);

        $this->addCSSFiles($this->Slider->getCSSFiles());

        $allowedProductClasses = QUI\ERP\Products\Utils\ProductTypes::getInstance()->getProductTypes();

        // If variant children are not allowed, filter them out
        if (empty($this->getAttribute('showVariantChildren'))) {
            $allowedProductClasses = array_filter($allowedProductClasses, function ($productClass) {
                return !is_a($productClass, QUI\ERP\Products\Product\Types\VariantChild::class, true);
            });
        }

        // Remove leading slashes from classes
        array_walk($allowedProductClasses, function (&$productClass) {
            $productClass = ltrim($productClass, '\\');
        });

        $allowedProductClasses[] = ''; // fix for old products

        if ($productIds) {
            $productIds = explode(',', $productIds);
            $products = QUI\ERP\Products\Handler\Products::getProducts([
                'where' => [
                    'active' => 1,
                    'id' => [
                        'type' => 'IN',
                        'value' => $productIds
                    ],
                    'type' => [
                        'type' => 'IN',
                        'value' => $allowedProductClasses
                    ]
                ],

                'order' => $order,
                'limit' => $limit
            ]);
        }

        $catIds = $this->getAttribute('categoryIds');
        $productsFromCat = [];

        if (is_string($catIds) && strlen($catIds) > 0) {
            $catIds = explode(',', $catIds);

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
                        'type' => 'NOT',
                        'value' => QUI\ERP\Products\Product\Types\VariantChild::class
                    ];
                }

                $catProducts = $Category->getProducts($query);

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
            $this->Slider->addProduct($Product->getViewFrontend());
        }

        $this->Slider->setAttribute('height', $this->getAttribute('entryHeight'));

        foreach ($this->Slider->getCSSFiles() as $file) {
            $this->addCSSFile($file);
        }

        $Engine->assign([
            'this' => $this,
            "Slider" => $this->Slider
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/Slider.html');
    }
}
