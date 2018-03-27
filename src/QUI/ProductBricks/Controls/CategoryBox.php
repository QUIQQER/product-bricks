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
            'title'    => '',
            'text'     => '',
            'class'    => 'quiqqer-productbricks-categorybox',
            'nodeName' => 'section',
            'template' => dirname(__FILE__) . '/CategoryBox.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/CategoryBox.css');
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

//        $this->setStyle('background-color', $this->getAttribute('bgColor'));

        $categoryIds = $this->getAttribute('categoryIds');
        $categoryIds = explode(',', $categoryIds);

        $categories = [];

        foreach ($categoryIds as $categoryId) {
            try {
                $Category     = Categories::getCategory($categoryId);
                $categories[] = $Category;
                $Category->getSite()->getAttribute('');
//                $categories[]['desc'] = $Category->getDescription();
//                $categories[]['url'] = $Category->getUrl();
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        $Engine->assign([
            'this'       => $this,
            'categories' => $categories
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }
}
