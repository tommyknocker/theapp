<?php
/**
 * Handler interface
 * @author tommyknocker <me@mysrv.ru>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Interfaces;

interface Handler {

    /**
     * Handler event subscription
     */
    public static function init();
}
