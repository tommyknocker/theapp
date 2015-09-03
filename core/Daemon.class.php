<?php
/**
 * Daemonize our cronjobs
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 */
namespace core;

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
     * Set time delay
     * @param int $time ms
     */
    public function setDelay($time)
    {

        if (!$this->current) {
            throw new \Exception('Try to select daemon first');
        }


        if (intval($time) == 0 || intval($time) < 0) {
            throw new \Exception('Incorrect delay value');
        }

        $this->daemons[$this->current]['delay'] = $time;
    }

    /**
     * Set daemon callback function
     * @param callable $callback
     * @throws \Exception
     */
    public function setCallback($callback)
    {

        if (!$this->current) {
            throw new \Exception('Try to select daemon first');
        }


        if (!is_callable($callback)) {
            throw new \Exception('Daemon callback is not callable');
        }

        $this->daemons[$this->current]['callback'] = $callback;
    }

    /**
     * Our main daemon process
     */
    private function runProcess()
    {

        while (true) {
            try {
                $willNotSleep = call_user_func_array($this->daemons[$this->current]['callback'], [$message]);
            } catch (\Exception $e) {
                \App::Log()->tag('daemon' . $this->current)->LogError('Got \Exception from callback of ' . $this->current, $e->getMessage());
            }

            if (!$willNotSleep) {
                \App::Timer()->msleep($this->daemons[$this->current]['delay']);
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
