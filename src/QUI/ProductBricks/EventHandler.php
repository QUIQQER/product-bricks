<?php

/**
 * This file contains \QUI\ProductBricks\EventHandler
 */

namespace QUI\ProductBricks;

use Smarty;

/**
 * Event Class
 *
 * @author www.pcsg.de (Michael Danielczok)
 */
class EventHandler
{
    /**
     * Event: on smarty init
     *
     * @param Smarty $Smarty
     * @return void
     */
    public static function onSmartyInit(Smarty $Smarty): void
    {
        $Smarty->registerPlugin('modifier', 'strstr', 'strstr');
    }
}
