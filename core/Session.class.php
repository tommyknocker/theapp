<?php
/**
 * Session class
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

class Session
{

    private $started = false;

    public function __construct()
    {
        $this->started = ini_get('session.auto_start');
    }

    public function start()
    {
        if (!$this->started) {
            session_start();
        }
    }

    public function __destruct()
    {
        session_write_close();
    }
}
