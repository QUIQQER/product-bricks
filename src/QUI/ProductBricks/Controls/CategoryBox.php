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
            'class'     => 'quiqqer-productbricks-categorybox',
            'nodeName'  => 'section',
            'template'  => dirname(__FILE__) . '/CategoryBox.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/CategoryBox.css');
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        $this->setStyle('background-color', $this->getAttribute('bgColor'));

        $categoryIds = $this->getAttribute('categoryIds');
        $categoryIds = explode(',', $categoryIds);

        $entries = [];

        foreach ($categoryIds as $categoryId) {
            try {

                $Category  = Categories::getCategory($categoryId);
                $entries[] = $Category;

            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        $Engine->assign([
            'this'    => $this,
            'entries' => $entries
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }
}
