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
    public function __construct(array $attributes = [])
    {
        // default options
        $this->setAttributes([
            'class' => 'quiqqer-productbricks-promobox',
            'nodeName' => 'section',
            'minHeight' => 250,
            'contentPosition' => 'center',
            'image' => false,
            'url' => false,
            'target' => '_self',
            'colorScheme' => 'none',
            'template' => dirname(__FILE__) . '/PromoBox.html'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/PromoBox.css');

        parent::__construct($attributes);
    }

    public function getBody(): string
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

        $colorScheme = match ($this->getAttribute('colorScheme')) {
            'light' => 'colorScheme-light',
            'dark' => 'colorScheme-dark',
            default => 'colorScheme-none',
        };

        $Engine->assign([
            'this' => $this,
            'minHeight' => $this->getAttribute('minHeight'),
            'image' => $this->getAttribute('image'),
            'contentPosition' => $this->getAttribute('contentPosition'),
            'colorScheme' => $colorScheme
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }
}
