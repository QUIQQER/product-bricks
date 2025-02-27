<?php

/**
 * This file contains QUI\ProductBricks\Controls\FeaturedProducts
 */

namespace QUI\ProductBricks\Controls;

use QUI;
use QUI\ERP\Products\Handler\Products;

use function is_array;

/**
 * Class CategoryBox
 *
 * @package QUI\Bricks\Controls
 */
class FeaturedProducts extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        // default options
        $this->setAttributes([
            'class' => 'quiqqer-productbricks-featuredProducts',
            'nodeName' => 'section',
            'limit' => 5,
            'showVariantChildren' => false,
            'order' => 'c_date DESC',
            'layout' => 'list',
            'featured1.title' => false,
            'featured1.categoryId' => false, // Featured products category ID's
            'featured2.title' => false,
            'featured2.categoryId' => false, // Featured products category ID's
            'featured3.title' => false,
            'featured3.categoryId' => false, // Featured products category ID's
            'customTemplate' => false, // Custom template (path to html file). Overwrites "layout".
            'customCss' => false  // Custom  template css (path to css file). Overwrites "layout".
        ]);

        parent::__construct($attributes);

        $this->setAttribute('cacheable', 0);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \QUI\Control::create()
     *
     */
    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        if (!$this->getAttribute('limit') || $this->getAttribute('limit') == '') {
            $this->setAttribute('limit', 5);
        }

        $featuredData = [];

        if ($this->getAttribute('featured1.categoryId')) {
            $products = $this->getProducts([
                'categoryId' => $this->getAttribute('featured1.categoryId')
            ]);

            $featuredData[] = [
                'title' => $this->getAttribute('featured1.title'),
                'products' => $this->getProductsViews($products)
            ];
        }

        if ($this->getAttribute('featured2.categoryId')) {
            $featuredData[] = [
                'title' => $this->getAttribute('featured2.title'),
                'products' => $this->getProductsViews(
                    $this->getProducts([
                        'categoryId' => $this->getAttribute('featured2.categoryId')
                    ])
                )
            ];
        }

        if ($this->getAttribute('featured3.categoryId')) {
            $featuredData[] = [
                'title' => $this->getAttribute('featured3.title'),
                'products' => $this->getProductsViews(
                    $this->getProducts([
                        'categoryId' => $this->getAttribute('featured3.categoryId')
                    ])
                )
            ];
        }

        switch ($this->getAttribute('layout')) {
            case 'gallery':
                $templateFile = dirname(__FILE__) . '/FeaturedProducts.Gallery.html';
                $cssFile = dirname(__FILE__) . '/FeaturedProducts.Gallery.css';
                break;
            case 'list':
            default:
                $templateFile = dirname(__FILE__) . '/FeaturedProducts.List.html';
                $cssFile = dirname(__FILE__) . '/FeaturedProducts.List.css';
        }

        // custom template
        if ($this->getAttribute('customTemplate')) {
            $templateFile = $this->getAttribute('customTemplate');
        }

        // custom css
        if ($this->getAttribute('customCss')) {
            $templateFile = $this->getAttribute('customCss');
        }

        $Engine->assign([
            'this' => $this,
            'featuredData' => $featuredData
        ]);

        $this->addCSSFile($cssFile);

        return $Engine->fetch($templateFile);
    }

    /**
     * Return products from the category
     *
     * @param array $params - query parameter
     *                              $queryParams['where']
     *                              $queryParams['limit']
     *                              $queryParams['order']
     *                              $queryParams['debug']
     * @return array
     */
    public function getProducts(array $params = []): array
    {
        $query = [
            'limit' => $this->getAttribute('limit'),
            'order' => $this->getAttribute('order')
        ];


        $value = '';

        if (isset($params['categoryId'])) {
            $value = $params['categoryId'];
        }

        $where = [
            'categories' => [
                'type' => '%LIKE%',
                'value' => ',' . $value . ','
            ],
            'active' => 1
        ];

        if (!$this->getAttribute('showVariantChildren')) {
            $where['type'] = [
                'type' => 'NOT',
                'value' => QUI\ERP\Products\Product\Types\VariantChild::class
            ];
        }

        if (isset($params['where'])) {
            $where = array_merge($where, $params['where']);
        }

        $query['where'] = $where;

        if (isset($params['limit'])) {
            $query['limit'] = $params['limit'];
        }

        if (isset($params['order'])) {
            $query['order'] = $params['order'];
        }

        if (isset($params['debug'])) {
            $query['debug'] = $params['debug'];
        }

        return Products::getProducts($query);
    }


    /**
     * Returns array of product views
     *
     * @param array $products - array with products
     * @return array
     */
    private function getProductsViews(array $products = []): array
    {
        if (!is_array($products) || empty($products)) {
            return [];
        }

        $productsView = [];

        foreach ($products as $Product) {
            /** @var QUI\ERP\Products\Product\Product $Product */
            try {
                $ProductView = $Product->getView();
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::addDebug($Exception->getMessage());
                continue;
            }

            $productsView[] = $ProductView;
        }

        return $productsView;
    }
}
