<?php

/**
 * This file contains QUI\ProductBricks\Controls\CategoryBox
 */

namespace QUI\ProductBricks\Controls;

use QUI;
use QUI\ERP\Products\Handler\Categories;
use QUI\Projects\Site;

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
    public function __construct(array $attributes = [])
    {
        // default options
        $this->setAttributes([
            'class' => 'quiqqer-productbricks-categorybox',
            'nodeName' => 'section',
            'bgColor' => '#fff',
            'imageAsBackground' => false,
            'order' => 'manuell',
            'limit' => false,
            'allCategoriesUrl' => false,
            'template' => dirname(__FILE__) . '/CategoryBox.html'
        ]);

        parent::__construct($attributes);

        $this->setAttribute('cacheable', 0);
        $this->addCSSFile(dirname(__FILE__) . '/CategoryBox.css');
    }

    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        $this->setStyle('background-color', $this->getAttribute('bgColor'));

        $imageAsBackground = $this->getAttribute('imageAsBackground');

        $limit = false;

        if ($this->getAttribute('limit')) {
            $limit = intval($this->getAttribute('limit'));
            $limit = $limit >= 0 ? $limit : false;
        }

        $sites = QUI\Projects\Site\Utils::getSitesByInputList(
            $this->getProject(),
            $this->getAttribute('site'),
            [
                'order' => $this->getAttribute('order'),
                'limit' => $limit
            ]
        );

        $entries = [];

        /** @var $Entry Site */
        foreach ($sites as $Site) {
            // no assigned category? then skip and go to next element
            if (!$this->checkAssignedCategory($Site)) {
                continue;
            }

            $entries[] = $this->setCategoryAttributes($Site);
        }

        $Engine->assign([
            'this' => $this,
            'imageAsBackground' => $imageAsBackground,
            'entries' => $entries
        ]);


        return $Engine->fetch($this->getAttribute('template'));
    }

    /**
     * Set entry attributes to use it in html template
     *
     * @param $Site Site
     * @return array
     * @throws QUI\Exception
     */
    public function setCategoryAttributes(Site $Site): array
    {
        $title = $Site->getAttribute('title');
        $desc = $Site->getAttribute('short');
        $url = $Site->getUrl();
        $image = $Site->getAttribute('image_site');

        // site has no short description? try by category
        if (!$desc) {
            $Category = $this->getCategoryFromSite($Site);
            $desc = $Category->getDescription();
        }

        // set placeholder if no image available
        /*if (!$image) {
            $image = $this->getProject()->getMedia()->getPlaceholderImage()->getSizeCacheUrl();
        }*/

        return [
            'Site' => $Site,
            'title' => $title,
            'desc' => $desc,
            'url' => $url,
            'image' => $image
        ];
    }

    /**
     * Check if site has assigned category
     *
     * @param Site $Site
     * @return bool
     */
    public function checkAssignedCategory(Site $Site): bool
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
                        'siteId' => $Site->getAttribute('id'),
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
     * @param $Site Site
     * @return QUI\ERP\Products\Interfaces\CategoryInterface
     * @throws QUI\Exception
     */
    public function getCategoryFromSite(Site $Site): QUI\ERP\Products\Interfaces\CategoryInterface
    {
        return Categories::getCategory(
            $Site->getAttribute('quiqqer.products.settings.categoryId')
        );
    }
}
