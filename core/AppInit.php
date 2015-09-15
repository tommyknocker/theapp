<?php
/**
 * App initialization
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;
use App;

if (defined('DEBUG') && DEBUG) {
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);
    ini_set('display_errors', 'off');
}

require_once 'App.class.php';
require_once 'Autoload.class.php';
require_once 'ErrorsHandler.class.php';

App::Autoload()->addNameSpace('core')->addPath(DIR_ROOT . 'core')->addExt('.class.php')->register()
    ->addPath(DIR_ROOT . 'abstract')->addExt('.class.php')->register()
    ->addPath(DIR_ROOT . 'interfaces')->addExt('.interface.php')->register()
    ->addPath(DIR_ROOT . 'traits')->addExt('.trait.php')->register()
    ->addPath(DIR_ROOT . 'models')->addExt('.model.php')->register()
    ->addPath(DIR_ROOT . 'classes')->addExt('.class.php')->register();

App::Log(defined('DEBUG') && DEBUG ? ERR_DEBUG : ERR_WARN);
App::Container()->errors = [];

App::Container()->errorsTranslation = [
    ERR_EMERG => 'Emergency',
    ERR_ALERT => 'Alert',
    ERR_CRIT => 'Critical',
    ERR_ERR => 'Error',
    ERR_WARN => 'Warning',
    ERR_NOTICE => 'Notice',
    ERR_DEBUG => 'Debug'
];

register_shutdown_function(['\\core\\ErrorsHandler', 'fatalErrorHandler']);
set_error_handler(['\\core\\ErrorsHandler', 'errorHandler']);
set_exception_handler(['\\core\\ErrorsHandler', 'exceptionHandler']);

App::Config(DIR_DATA . 'config.json.php');

// composer support
if(file_exists(DIR_ROOT . 'vendor/autoload.php')) {
    require_once DIR_ROOT . 'vendor/autoload.php';
}

App::Engine()->start();

$errors = App::Container()->get('errors')->result;
App::Event()->fire('system:errors', array($errors));
