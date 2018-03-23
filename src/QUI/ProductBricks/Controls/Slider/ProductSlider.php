<?php

/**
 * This file contains QUI\ProductBricks\Controls\Slider\ProductSlider
 */

namespace QUI\ProductBricks\Controls\Slider;

use QUI;
use QUI\Projects\Media\Utils;

/**
 * Class ProductSlider
 *
 * @package QUI\Bricks\Controls
 */
class ProductSlider extends QUI\Bricks\Controls\Slider\PromosliderWallpaper
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        // default options
        $this->setAttributes(array(
            'title'          => '',
            'text'           => '',
            'class'          => 'quiqqer-bricks-promoslider-wallpaper2Content',
            'nodeName'       => 'section',
            'data-qui'       => 'package/quiqqer/bricks/bin/Controls/Slider/PromosliderWallpaper',
            'role'           => 'listbox',
            'shownavigation' => true,
            'showarrows'     => 'showHoverScale',
            'autostart'      => false,
            'delay'          => 5000,
            'template'       => dirname(__FILE__) . '/ProductSlider.html'
        ));

        $this->addCSSFile(dirname(__FILE__) . '/ProductSlider.css');

        $this->addCSSClass('grid-100');
        $this->addCSSClass('mobile-grid-100');
        $this->addCSSClass('quiqqer-bricks-promoslider-wallpaper');
    }

    /**
     * Add a slide for the desktop view
     *
     * @param string $image - image.php URL to an image
     * @param string $left - optional, left text
     * @param string $right - optional, right text
     * @param string|bool $type - optional, not exists, but we are from PromosliderWallpaper and AbstractPromoslider
     * @param string $url - index.php? or extern url
     */
    public function addSlide($image, $left = '', $right = '', $type = false, $url = '')
    {
        $this->desktopSlides[] = $this->checkSlideParams($image, $left, $right, $url);
    }

    /**
     * Add a slide for the mobile view
     *
     * @param string $image - image.php URL to an image
     * @param string $left - optional, left text
     * @param string $right - optional, right text
     * @param string $url - index.php? or extern url
     */
    public function addMobileSlide($image, $left = '', $right = '', $url = '')
    {
        $this->mobileSlides[] = $this->checkSlideParams($image, $left, $right, $url);
    }

    /**
     * Add a slide for the mobile view
     *
     * @param string $image - image.php URL to an image
     * @param string $left - Left text
     * @param string $right - Right text
     * @param string $url - index.php? or extern url
     * @return array
     */
    protected function checkSlideParams($image, $left = '', $right = '', $url = '')
    {
        if (Utils::isMediaUrl($image)) {
            try {
                $Image = Utils::getMediaItemByUrl($image);

                if (Utils::isImage($Image)) {
                    $image = $Image;
                } else {
                    $image = false;
                }
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::addDebug($Exception->getMessage());
                $image = false;
            }
        } else {
            $image = false;
        }

        return array(
            'image' => $image,
            'left'  => $left,
            'right' => $right,
            'url'   => $url
        );
    }

    /**
     * Parse slide params and add the slide
     *
     * @param mixed $slides
     * @param string $type
     */
    protected function parseSlides($slides, $type = 'desktop')
    {
        if (empty($slides)) {
            return;
        }

        // desktop slides
        if (is_string($slides)) {
            $slides = json_decode($slides, true);
        }

        if (!is_array($slides)) {
            return;
        }

        $attributes = array('image', 'left', 'right', 'url');

        foreach ($slides as $slide) {
            foreach ($attributes as $attribute) {
                if (!isset($slide[$attribute])) {
                    $slide[$attribute] = false;
                }
            }

            switch ($type) {
                case 'desktop':
                    $this->addSlide(
                        $slide['image'],
                        $slide['left'],
                        $slide['right'],
                        'desktop',
                        $slide['url']
                    );
                    break;

                case 'mobile':
                    $this->addMobileSlide(
                        $slide['image'],
                        $slide['left'],
                        $slide['right'],
                        $slide['url']
                    );
                    break;
            }
        }
    }
}
