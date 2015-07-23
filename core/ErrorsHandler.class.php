<?php

namespace core;

/**
 * Errors handler
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
class ErrorsHandler {

    /**
     * Fatal errors handler
     */
    public static function fatalErrorHandler() {
        $errfile = "unknown file";
        $errstr = "fatal";
        $errno = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();

        if ($error !== NULL) {
            $errno = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr = $error["message"];

            self::errorHandler($errno, $errstr, $errfile, $errline);
        }
    }

    /**
     * Standart Errors Handler
     * @param integer $errno Error num
     * @param string $errstr Error description
     * @param string $errfile File, where error was occured
     * @param integer $errline Num of string of file
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline) {

       
        switch ($errno) {

            case E_NOTICE:
            case E_USER_NOTICE:
                $severity = ERR_NOTICE;
                break;

            case E_STRICT:
            case E_DEPRECATED:
            case E_COMPILE_WARNING:
            case E_WARNING:
                $severity = ERR_WARN;
                break;

            case E_COMPILE_ERROR:
            case E_CORE_ERROR:
                $severity = ERR_CRIT;                
                break;

            case E_ERROR:
            case E_USER_ERROR:
            default:
                $severity = ERR_ERR;
                break;
        }
        
        $log = [ 
            'name'          => 'PHP_ERROR ' . $errstr,
            'tag'           => 'Error',
            'file'          => $errfile,
            'line'          => $errline,
            'date_create'   => date('Y-m-d H:i:s'),
            'type'          => $severity,
            'variable'      => ERR_NO_ARGUMENTS
        ];
        
        \App::Container()->add('errors', $log);
    }

    /**
     * Exceptions handler
     * @param Exception $e Exception object
     */
    public static function exceptionHandler($e) {

        if (\App::Container()->transactionInProgress) {
            \App::DB()->rollback();
        }
        
        $stack = debug_backtrace();
        
        $exception = $stack[0]['args'][0];

        $line = 0;
        $file = 0;
        
        if($exception instanceof \Exception) {
            $file = $exception->getFile();
            $line = $exception->getLine();
        }
        
        $log = [ 
            'name'          => 'exception ' . $e->getMessage(),
            'tag'           => 'exception',
            'file'          => $file,
            'line'          => $line,
            'date_create'   => date('Y-m-d H:i:s'),
            'type'          => ERR_CRIT,
            'variable'      => ERR_NO_ARGUMENTS
        ];        

        \App::Container()->add('errors', $log);
    }
}