<?php

/**
 * This file contains QUI\ProductBricks\Controls\FeaturedProductsListSimple
 */

namespace QUI\ProductBricks\Controls;

use function DusanKasan\Knapsack\concat;
use QUI;
use QUI\ERP\Products\Handler\Products;

/**
 * Class CategoryBox
 *
 * @package QUI\Bricks\Controls
 */
class FeaturedProductsListSimple extends QUI\Control
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
            'class'                => 'quiqqer-productbricks-featuredProductsSimpleList',
            'nodeName'             => 'section',
            'limit'                => 5,
            'order'                => 'c_date DESC',
            'featured1.title'      => false,
            'featured1.categoryId' => false, // Featured products category ID's
            'featured2.title'      => false,
            'featured2.categoryId' => false, // Featured products category ID's
            'featured3.title'      => false,
            'featured3.categoryId' => false, // Featured products category ID's
            'template'             => dirname(__FILE__) . '/FeaturedProductsListSimple.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/FeaturedProductsListSimple.css');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \QUI\Control::create()
     *
     * @throws QUI\Exception
     */
    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        if (!$this->getAttribute('limit') || $this->getAttribute('limit') == '') {
            $this->setAttribute('limit', 5);
        }
        $featured1      = false;
        $featured2      = false;
        $featured3      = false;
        $featuredTitle1 = false;
        $featuredTitle2 = false;
        $featuredTitle3 = false;

        $featuredData = [];

        if ($this->getAttribute('featured1.categoryId')) {
            $featuredData[] = [
                'title'    => $this->getAttribute('featured1.title'),
                'url' => '',
                'products' => $this->getProductsViews($this->getProducts([
                    'categoryId' => $this->getAttribute('featured1.categoryId')
                ]))
            ];
        }

        if ($this->getAttribute('featured2.categoryId')) {
            $featuredData[] = [
                'title'    => $this->getAttribute('featured2.title'),
                'products' => $this->getProductsViews($this->getProducts([
                    'categoryId' => $this->getAttribute('featured2.categoryId')
                ]))
            ];
        }

        if ($this->getAttribute('featured3.categoryId')) {
            $featuredData[] = [
                'title'    => $this->getAttribute('featured3.title'),
                'products' => $this->getProductsViews($this->getProducts([
                    'categoryId' => $this->getAttribute('featured3.categoryId')
                ]))
            ];
        }

        $Engine->assign([
            'this'           => $this,
            'featuredTitle1' => $featuredTitle1,
            'featuredTitle2' => $featuredTitle2,
            'featuredTitle3' => $featuredTitle3,
            'featured1'      => $featured1,
            'featured2'      => $featured2,
            'featured3'      => $featured3,
            'featuredData'   => $featuredData
        ]);

        return $Engine->fetch($this->getAttribute('template'));
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
    public function getProducts($params = [])
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
                'type'  => '%LIKE%',
                'value' => ',' . $value . ','
            ]
        ];

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
     * @return array|bool
     */
    private function getProductsViews ($products = [])
    {
        if (!is_array($products) || empty($products)) {
            return false;
        }

        $productsView = [];

        foreach ($products as $Product) {
            /** @var QUI\ERP\Products\Product\Product $Product */
            try {
                $ProductView = $Product->getView();
            } catch (QUI\Exception $Exception) {
                $ProductView = null;
                QUI\System\Log::writeException($Exception);
            }

            $productsView[] = $ProductView;
        }

        return $productsView;
    }
}
