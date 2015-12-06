<?php
/**
 * Index page
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 */
namespace App\Handlers;

use App;

class Index
{

    /**
     * Initialization
     */
    public static function init()
    {
        App::Event()->subscribe('web:/', 'showMain');
    }

    /**
     * Show main page
     */
    public function showMain()
    {
        echo 'TheApp. Tiny, fast and powerful microframework';
    }
}
