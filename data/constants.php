<?php
/**
 * Constants are defined here
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
/**
 * Current version of The App
 */
define('APP_VERSION', '0.3.1');

/**
 * Document root
 */
define('DIR_ROOT', dirname(dirname(__FILE__)) . DS);

/**
 * Data directory
 */
define('DIR_DATA', DIR_ROOT . 'data' . DS);

/**
 * Cron's pids directory
 */
define('DIR_DATA_PID', DIR_DATA . 'pids' . DS);

/**
 * Handlers directory
 */
define('DIR_HANDLERS', DIR_ROOT . 'handlers' . DS);

/**
 * Public directory
 */
define('DIR_PUBLIC', DIR_ROOT . 'public' . DS);

/**
 * Templates directory
 */
define('DIR_TEMPLATES', DIR_ROOT . 'templates' . DS);

/**
 * Engine modes
 */
define('ENGINE_MODE_WEB', 'web');
define('ENGINE_MODE_CLI', 'cli');
