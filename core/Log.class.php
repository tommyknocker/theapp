<?php
/**
 * Logger
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;
use App;

class Log
{

    /**
     * Current minimum logging threshold
     * @var int
     */
    private $_severityThreshold = ERR_OFF;
    private $currentTag = LOG_DEFAULT_TAG;

    /**
     * Class constructor
     *
     * @param int $severity     One of the pre-defined severity constants
     */
    public function __construct($severity)
    {
        if ($severity === ERR_OFF) {
            return;
        }

        if ($severity) {
            $this->_severityThreshold = $severity;
        }
    }

    /**
     * Stops script execution
     */
    public function stop()
    {
        exit();
    }

    /**
     * Writes a $line to the log with a severity level of INFO. Any information
     * can be used here, or it could be used with E_STRICT errors
     *
     * @param string $line Information to log
     * @param mixed $args Optional. Arguments to dump for
     */
    public function logDebug($line, $args = ERR_NO_ARGUMENTS)
    {
        $this->__log($line, ERR_DEBUG, $args);
    }

    /**
     * Writes a $line to the log with a severity level of NOTICE. Generally
     * corresponds to E_STRICT, E_NOTICE, or E_USER_NOTICE errors
     *
     * @param string $line Information to log
     * @param mixed $args Optional. Arguments to dump for
     */
    public function logNotice($line, $args = ERR_NO_ARGUMENTS)
    {
        $this->__log($line, ERR_NOTICE, $args);
    }

    /**
     * Writes a $line to the log with a severity level of WARN. Generally
     * corresponds to E_WARNING, E_USER_WARNING, E_CORE_WARNING, or 
     * E_COMPILE_WARNING
     *
     * @param string $line Information to log
     * @param mixed $args Optional. Arguments to dump for
     */
    public function logWarn($line, $args = ERR_NO_ARGUMENTS)
    {
        $this->__log($line, ERR_WARN, $args);
    }

    /**
     * Writes a $line to the log with a severity level of ERR. Most likely used
     * with E_RECOVERABLE_ERROR
     *
     * @param string $line Information to log
     * @param mixed $args Optional. Arguments to dump for
     */
    public function logError($line, $args = ERR_NO_ARGUMENTS)
    {
        $this->__log($line, ERR_ERR, $args);
    }

    /**
     * Writes a $line to the log with a severity level of ALERT.
     *
     * @param string $line Information to log
     * @param mixed $args Optional. Arguments to dump for
     */
    public function logAlert($line, $args = ERR_NO_ARGUMENTS)
    {
        $this->__log($line, ERR_ALERT, $args);
    }

    /**
     * Writes a $line to the log with a severity level of CRIT.
     *
     * @param string $line Information to log
     * @param mixed $args Optional. Arguments to dump for
     * @param bool $needDie Optional. Die after logging
     */
    public function logCrit($line, $args = ERR_NO_ARGUMENTS)
    {
        $this->__log($line, ERR_CRIT, $args);
    }

    /**
     * Writes a $line to the log with a severity level of EMERG.
     *
     * @param string $line Information to log
     * @param mixed $args Optional. Arguments to dump for
     * @param bool $needDie Optional. Die after logging
     */
    public function logEmerg($line, $args = ERR_NO_ARGUMENTS)
    {
        $this->__log($line, ERR_EMERG, $args);
    }

    /**
     * Set tag
     * @param string $tag
     */
    public function tag($tag)
    {
        $this->currentTag = $tag;
    }

    /**
     * Writes a $line to the log with the given severity
     *
     * @param string $line Information to log
     * @param integer $severity Severity level of log message (use constants)
     * @param mixed $args Optional. Arguments to dump for
     */
    private function __log($line, $severity, $args = ERR_NO_ARGUMENTS)
    {

        if ($this->_severityThreshold >= $severity) {

            $backtrace = debug_backtrace();

            $skipClasses = ['core\\Log'];

            foreach ($backtrace as $trace) {

                if (array_key_exists('class', $trace) && in_array($trace['class'], $skipClasses)) {
                    continue;
                } elseif ($trace['file'] == DIR_ROOT . 'core' . DS . 'App.class.php') {
                    continue;
                }

                break;
            }

            $log = [
                'tag' => $this->currentTag,
                'name' => $line,
                'file' => $trace ? $trace['file'] : 0,
                'line' => $trace ? $trace['line'] : 0,
                'date_create' => date('Y-m-d H:i:s'),
                'type' => $severity,
                'data' => print_r($args, true)
            ];

            $this->tag(LOG_DEFAULT_TAG);

            App::Container()->add('errors', $log);
        }
    }
}
