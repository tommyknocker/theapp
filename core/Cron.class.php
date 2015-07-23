<?php

namespace core;

/**
 * Cron helper methods
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
class Cron {

    /**
     * Pid of current process
     * @var int 
     */
    private $pid = null;

    /**
     * Try to set the lock
     * @param string $cronName
     * @return bool|int Current pid or false if already locked  
     */
    public function lock($cronName) {

        $lockFile = DIR_DATA_PID . $cronName . '.lock';

        if (file_exists($lockFile)) {
            $this->pid = file_get_contents($lockFile);

            if ($this->isRunning()) {
                return false;
            } else {
                //@todo log if not running and run
            }
        }

        $this->pid = getmypid();
        file_put_contents($lockFile, $this->pid);

        return $this->pid;
    }

    /**
     * Try to unlock (unlink pid file)
     * @param string $cronName
     * @return bool True, if unlocked
     */
    public static function unlock($cronName) {

        $lockFile = DIR_DATA_PID . $cronName . '.lock';

        if (file_exists($lockFile)) {
            return unlink($lockFile);
        }

        return false;
    }

}
