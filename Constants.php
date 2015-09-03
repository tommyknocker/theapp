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
define('APP_VERSION', '0.0.9');


/**
 * Debug mode
 */
define('DEBUG', true);

/**
 * Directory separator
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * Document root
 */
define('DIR_ROOT', dirname(__FILE__) . DS);

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
 * Templates directory
 */
define('DIR_TEMPLATES', DIR_ROOT . 'templates' . DS);

/**
 * Errors constants
 */
define('ERR_OFF', 0);
define('ERR_EMERG', 1);
define('ERR_ALERT', 2);
define('ERR_CRIT', 3);
define('ERR_ERR', 4);
define('ERR_WARN', 5);
define('ERR_NOTICE', 6);
define('ERR_DEBUG', 7);
define('ERR_NO_ARGUMENTS', 'No arguments');

/**
 * Engine modes
 */
define('ENGINE_MODE_WEB', 'web');
define('ENGINE_MODE_CLI', 'cli');

/**
 * Log
 */
define('LOG_DEFAULT_TAG', 'Debug');
