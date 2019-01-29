<?php

/**
 * This file contains QUI\ProductBricks\Controls\CategoryBox
 */

namespace QUI\ProductBricks\Controls;

use QUI;
use QUI\ERP\Products\Handler\Categories;

/**
 * Class CategoryBox
 *
 * @package QUI\Bricks\Controls
 */
class CategoryBox extends QUI\Control
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
            'class'             => 'quiqqer-productbricks-categorybox',
            'nodeName'          => 'section',
            'bgColor'           => '#fff',
            'imageAsBackground' => false,
            'order'             => 'name ASC',
            'template'          => dirname(__FILE__) . '/CategoryBox.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/CategoryBox.css');
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        $this->setStyle('background-color', $this->getAttribute('bgColor'));

        $imageAsBackground = $this->getAttribute('imageAsBackground');

        $sites = QUI\Projects\Site\Utils::getSitesByInputList(
            $this->getProject(),
            $this->getAttribute('site'),
            [
                'order' => $this->getAttribute('order'),
                'limit' => 20
            ]
        );

        $entries = [];

        /** @var $Entry \QUI\Projects\Site */
        foreach ($sites as $Site) {
            // no assigned category? then skip and go to next element
            if (!$this->checkAssignedCategory($Site)) {
                continue;
            }

            $entries[] = $this->setCategoryAttributes($Site);
        }

        $Engine->assign([
            'this'              => $this,
            'imageAsBackground' => $imageAsBackground,
            'entries'           => $entries
        ]);


        return $Engine->fetch($this->getAttribute('template'));
    }

    /**
     * Set entry attributes to use it in html template
     *
     * @param $Site \QUI\Projects\Site
     * @return array
     * @throws QUI\Exception
     */
    public function setCategoryAttributes($Site)
    {
        $title = $Site->getAttribute('title');
        $desc  = $Site->getAttribute('short');
        $url   = $Site->getUrl();
        $image = $Site->getAttribute('image_site');

        // site has no short description? try by category
        if (!$desc) {
            $Category = $this->getCategoryFromSite($Site);
            $desc     = $Category->getDescription();
        }

        // set placeholder if no image available
        /*if (!$image) {
            $image = $this->getProject()->getMedia()->getPlaceholderImage()->getSizeCacheUrl();
        }*/

        return [
            'Site'  => $Site,
            'title' => $title,
            'desc'  => $desc,
            'url'   => $url,
            'image' => $image
        ];
    }

    /**
     * Check if site has assigned category
     *
     * @param $Site \QUI\Projects\Site
     * @return bool|QUI\ERP\Products\Interfaces\CategoryInterface
     */
    public function checkAssignedCategory($Site)
    {
        $id = (int)$Site->getAttribute('quiqqer.products.settings.categoryId');

        try {
            if (!$id) {
                throw new QUI\Exception(
                    [
                        'quiqqer/product-bricks',
                        'exception.site.has.no.category'
                    ],
                    404,
                    [
                        'siteId'    => $Site->getAttribute('id'),
                        'siteTitle' => $Site->getAttribute('title')
                    ]
                );
            }

            $this->getCategoryFromSite($Site);

            return true;

        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }
        return false;
    }

    /**
     * Get assigned category to the site
     *
     * @param $Site \QUI\Projects\Site
     * @return QUI\ERP\Products\Interfaces\CategoryInterface
     * @throws QUI\Exception
     */
    public function getCategoryFromSite($Site)
    {
        return Categories::getCategory(
            $Site->getAttribute('quiqqer.products.settings.categoryId')
        );
    }
}
