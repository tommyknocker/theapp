<?php
/**
 * Time functions
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

class Timer
{

    private $time = null;

    /**
     * Start timer
     */
    public function start()
    {
        $this->time = microtime(true);
    }

    /**
     * Get difference in microseconds
     * @return int
     */
    public function timeMicro()
    {
        return (int) ((microtime(true) - $this->time) * 1000000);
    }

    /**
     * Get difference in milliseconds
     * @return int
     */
    public function timeMs()
    {
        return (int) ((microtime(true) - $this->time) * 1000);
    }

    /**
     * Get deffierence in seconds
     * @return int
     */
    public function timeSec()
    {
        return (int) ((microtime(true) - $this->time));
    }

    /**
     * Sleep given amount of milliseconds (1ms = 1/1000th of second)
     * 
     * @param integer $milliseconds
     */
    public function msleep($milliseconds)
    {
        usleep((int) $milliseconds * 1000);
    }
}
