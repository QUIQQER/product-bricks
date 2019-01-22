<?php

/**
 * This file contains QUI\ProductBricks\Controls\PromoBox
 */

namespace QUI\ProductBricks\Controls;

use QUI;

/**
 * Class CategoryBox
 *
 * @package QUI\Bricks\Controls
 */
class PromoBox extends QUI\Control
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
            'class'           => 'quiqqer-productbricks-promobox',
            'nodeName'        => 'section',
            'minHeight'       => 250,
            'contentPosition' => 'center',
            'image'           => false,
            'url'             => false,
            'target'          => '_self',
            'colorScheme'     => 'none',
            'template'        => dirname(__FILE__) . '/PromoBox.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/PromoBox.css');
    }

    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        // set qui java script control
        if ($this->getAttribute('url')) {
            $this->setJavaScriptControl('package/quiqqer/product-bricks/bin/controls/PromoBox');

            $this->setJavaScriptControlOption('url', $this->getAttribute('url'));

            if ($this->getAttribute('target')) {
                $this->setJavaScriptControlOption('target', $this->getAttribute('target'));
            }
        }

        switch ($this->getAttribute('colorScheme')) {
            case 'light':
                $colorScheme = 'colorScheme-light';
                break;
            case 'dark':
                $colorScheme = 'colorScheme-dark';
                break;
            default:
                $colorScheme = 'colorScheme-none';
                break;
        }

        $Engine->assign([
            'this'            => $this,
            'minHeight'       => $this->getAttribute('minHeight'),
            'image'           => $this->getAttribute('image'),
            'contentPosition' => $this->getAttribute('contentPosition'),
            'colorScheme'     => $colorScheme
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }
}
