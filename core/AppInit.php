<?php
/**
 * App initialization
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use App;

require_once 'App.php';
require_once DIR_ROOT . 'vendor/autoload.php';

// Configure main logger
$output = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
$formatter = new \Monolog\Formatter\LineFormatter($output, null, false, true);
$log = new \Monolog\Logger('App');
$streamHandler = new \Monolog\Handler\StreamHandler(DIR_DATA . 'log' . DS . date('Y-m-d') . '.log', \Monolog\Logger::WARNING);
$streamHandler->setFormatter($formatter);
$log->pushHandler($streamHandler);
$log->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());
\Monolog\ErrorHandler::register($log);
App::ld('Log', $log);

App::Config('config');

$defaultLanguage = App::Config()->language->default;
if($defaultLanguage) {
    App::I18n()->setDefaultLanguage($defaultLanguage);
}

App::Engine()->start();
