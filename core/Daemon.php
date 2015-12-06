<?php
/**
 * Daemonize our cronjobs
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 */
namespace App\Core;
use App,
    Exception;

class Daemon
{

    /**
     * Name of current daemon
     * @var string 
     */
    private $current = '';

    public function __construct()
    {
        
    }

    /**
     * Set daemon callback function
     * @param callable $callback
     * @throws \Exception
     */
    public function setCallback($callback)
    {
        if (!$this->current) {
            throw new Exception('Try to select daemon first');
        }

        if (!is_callable($callback)) {
            throw new Exception('Daemon callback is not callable');
        }

        $this->daemons[$this->current]['callback'] = $callback;
    }    
    
    /**
     * Set time delay
     * @param int $time ms
     */
    public function setDelay($time)
    {

        if (!$this->current) {
            throw new Exception('Try to select daemon first');
        }


        if (intval($time) == 0 || intval($time) < 0) {
            throw new Exception('Incorrect delay value');
        }

        $this->daemons[$this->current]['delay'] = $time;
    }

    /**
     * Daemon initialization
     * @param string $newDaemonName
     * @param string $description
     */
    public function setName($newDaemonName, $description = '')
    {

        $this->daemons[$newDaemonName] = [
            'pid' => 0,
            'name' => $newDaemonName,
            'delay' => App::Config()->daemon_delay
        ];

        if ($description) {
            $this->daemons[$newDaemonName]['description'] = $description;
        }

        $this->current = $newDaemonName;
    }
    
    /**
     * Our main daemon process
     */
    private function runProcess()
    {
        while (true) {
            try {
                $willNotSleep = call_user_func_array($this->daemons[$this->current]['callback'], [$message]);
            } catch (Exception $e) {
                App::Log()->addError('Got Exception from callback of {daemon}: {message}', ['daemon' => $this->current, 'message' => $e->getMessage()]);
            }

            if (!$willNotSleep) {
                App::Timer()->msleep($this->daemons[$this->current]['delay']);
            }
        }
    }

    /**
     * Run it
     */
    public function start()
    {
        $this->daemons[$this->current]['pid'] = getmypid();
        $this->saveState();
        $this->runProcess();
    }
}
