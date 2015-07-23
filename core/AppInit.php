<?php
/**
 * App initialization
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */

namespace core;

require_once 'App.class.php';
require_once 'Autoload.class.php';
require_once 'ErrorsHandler.class.php';

use \App as App;

App::Autoload()->addNameSpace('core')->addPath(DIR_ROOT . 'core')->addExt('.class.php')->register()
               ->addPath(DIR_ROOT . 'abstract')->addExt('.class.php')->register()
               ->addPath(DIR_ROOT . 'interfaces')->addExt('.interface.php')->register()
               ->addPath(DIR_ROOT . 'traits')->addExt('.trait.php')->register()
               ->addPath(DIR_ROOT . 'models')->addExt('.model.php')->register()
               ->addPath(DIR_ROOT . 'classes')->addExt('.class.php')->register();

ini_set('display_errors', 'on');

if(defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    App::Log(ERR_DEBUG);
} else {
    error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);
    App::Log(ERR_WARN);
}

App::Container()->errors = [];

App::Container()->errorsTranslation = [
    ERR_EMERG => 'Emergency',
    ERR_ALERT => 'Alert',
    ERR_CRIT  => 'Critical',
    ERR_ERR   => 'Error',
    ERR_WARN  => 'Warning',
    ERR_NOTICE => 'Notice',
    ERR_DEBUG  => 'Debug'
];

register_shutdown_function(['\\core\\ErrorsHandler', 'fatalErrorHandler']);
set_error_handler(['\\core\\ErrorsHandler', 'errorHandler']);
set_exception_handler(['\\core\\ErrorsHandler', 'exceptionHandler']);

App::Config(DIR_DATA . 'config.ini.php');
App::Engine()->start();

$errors = App::Container()->get('errors')->result;
App::Event()->fire('system:errors', array($errors));