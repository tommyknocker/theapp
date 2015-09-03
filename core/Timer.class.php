<?php
/**
 * Time functions
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 */
namespace core;

class Timer
{

    private $time = null;

    /**
     * Старт таймера
     */
    public function start()
    {
        $this->time = microtime(true);
    }

    /**
     * Получение разницы в микросекундах
     * @return int
     */
    public function timeMicro()
    {
        return (int) ((microtime(true) - $this->time) * 1000000);
    }

    /**
     * Получение разницы в миллисекундах
     * @return int
     */
    public function timeMs()
    {
        return (int) ((microtime(true) - $this->time) * 1000);
    }

    /**
     * Получение разницы в секундах
     * @return int
     */
    public function timeSec()
    {
        return (int) ((microtime(true) - $this->time));
    }

    /**
     * Спать указанное число миллисекунд
     * 
     * @param integer $milliseconds Количество миллисекунд (1/1000-я секунды)
     */
    public function msleep($milliseconds = 1000)
    {
        $milliseconds = (int) $milliseconds ? (int) $milliseconds * 1000 : 1000000;
        usleep($milliseconds);
    }
}
