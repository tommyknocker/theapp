<?php
/**
 * App initialization
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use App,
    Monolog\Formatter\LineFormatter,
    Monolog\ErrorHandler,
    Monolog\Handler\StreamHandler,
    Monolog\Processor\PsrLogMessageProcessor,
    Monolog\Logger;

require_once 'App.class.php';
require_once 'Autoload.class.php';
require_once DIR_DATA . 'shortcuts.php';

// composer support
if (file_exists(DIR_ROOT . 'vendor/autoload.php')) {
    require_once DIR_ROOT . 'vendor/autoload.php';
}

// register our own autoloader
App::Autoload()->addNamespace('App\\Core')->addPath(DIR_ROOT . 'core')->addExt('.class.php')->register()
    ->addNamespace('App\\Abstracts')->addPath(DIR_ROOT . 'abstract')->addExt('.class.php')->register()
    ->addNamespace('App\\Interfaces')->addPath(DIR_ROOT . 'interfaces')->addExt('.interface.php')->register()
    ->addNamespace('App\\Traits')->addPath(DIR_ROOT . 'traits')->addExt('.trait.php')->register()
    ->addNamespace('App\\Models')->addPath(DIR_ROOT . 'models')->addExt('.model.php')->register()
    ->addPath(DIR_ROOT . 'classes')->addExt('.class.php')->register();

// Configure main logger
$output = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
$formatter = new LineFormatter($output, null, false, true);
$log = new Logger('App');
$streamHandler = new StreamHandler(DIR_DATA . 'log' . DS . date('Y-m-d') . '.log', Logger::WARNING);
$streamHandler->setFormatter($formatter);
$log->pushHandler($streamHandler);
$log->pushProcessor(new PsrLogMessageProcessor());
ErrorHandler::register($log);
App::ld('Log', $log);

App::Config('config');

$defaultLanguage = App::Config()->language_default;
if($defaultLanguage) {
    App::I18n()->setDefaultLanguage($defaultLanguage);
}

App::Engine()->start();
