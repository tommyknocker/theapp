<?php

namespace App\Handlers;
use \App;

/**
 * The App.Pro site class
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 */
class System
{

    /**
     * Initialization
     */
    public static function init()
    {
        App::Event()->subscribe('system:errors', 'showErrors');
    }

    /**
     * Show logged errors
     * @param array $errors
     */
    public function showErrors($errors)
    {
        if (is_array($errors)) {
            foreach ($errors as $error) {
                echo EOL . '[' . $error['date_create'] . '] ' . $container->errorsTranslation[$error['type']] . ' in file ' . $error['file'] . ' on line ' . $error['line'] . ': ' . $error['name'] . ', ' . $error['variable'];
            }
        }
    }    
}
