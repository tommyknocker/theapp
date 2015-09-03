<?php
/**
 * Framework's engine
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;

class Engine
{

    /**
     * Current engine mode
     * @var string 
     */
    private $mode = ENGINE_MODE_WEB;

    /**
     * Current path
     * @var string
     */
    private $path = '';

    /**
     * Start engine
     */
    public function start()
    {
        $this->setMode();
        \App::Session()->start();
        $this->loadDBClass();
        $this->initHandlers();
        $this->process();
    }

    /**
     * Loads \App::DB if db.enabled is true in config
     */
    private function loadDBClass()
    {
        $db = \App::Config()->db;
        
        if ($db->enabled) {
            \App::ld('DB', new MysqliDb($db->host, $db->login, $db->password, $db->name, $db->port));
        }
    }

    /**
     * Process the request
     */
    private function process()
    {
        \App::Event()->fire($this->getMode() . ':before');

        $path = $this->getPath();

        if ($path) {
            \App::Event()->fire($this->getMode() . ':' . $path . ':before')
                ->fire($this->getMode() . ':' . $path)
                ->fire($this->getMode() . ':' . $path . ':after');
        }


        if (!\App::Event()->isFired($this->getMode() . ':' . $path)->result) {
            if ($this->getMode() == ENGINE_MODE_WEB) {
                header('HTTP/1.1 404 Not Found');
            }
            \App::Event()->fire($this->getMode() . ':404');
        }

        \App::Event()->fire($this->getMode() . ':after');
    }

    /**
     * Get path (or arguments if mode is cli)
     * @return string
     */
    public function getPath()
    {

        if (!$this->path) {
            switch ($this->getMode()) {
                case ENGINE_MODE_CLI:
                    $path = $_SERVER['argc'] > 1 ? $_SERVER['argv'][1] : null;
                    break;
                case ENGINE_MODE_WEB:
                    $path = explode('?', \App::Get()->server('REQUEST_URI')->result)[0];
                    break;
            }

            $this->path = rtrim($path, '/') . '/';
        }

        return $this->path;
    }

    /**
     * Sets current engine mode
     */
    private function setMode()
    {
        $this->mode = $this->isCli() ? ENGINE_MODE_CLI : ENGINE_MODE_WEB;
        define('EOL', $this->mode == ENGINE_MODE_CLI ? "\n" : '<br />');
    }

    /**
     * Returns current engine mode
     * @return string Current engine mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Check whether is cli or web mode
     * @return boolean true, if mode is cli
     */
    private function isCli()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Initialize handlers (call each handler's static init function)
     * @throws \Exception
     */
    private function initHandlers()
    {

        if (!is_readable(DIR_HANDLERS)) {
            throw new \Exception('Handlers directory is not readable');
        }

        $files = glob(DIR_HANDLERS . '*.handler.php');

        if (!$files) {
            throw new \Exception('No handlers found. Engine stopped');
        }

        foreach ($files as $file) {

            if (!is_readable($file)) {
                \App::Log()->logWarn('Cannot read handler', $file);
                continue;
            }

            require_once($file);

            $filename = basename($file);
            $filePart = explode('.', $filename);
            $handler = array_shift($filePart);

            try {

                $handlerReflection = new \ReflectionClass($handler);

                if (!$handlerReflection->hasMethod('init')) {
                    throw new \Exception('No init method found');
                }

                $handlerReflectionMethod = $handlerReflection->getMethod('init');

                if (!$handlerReflectionMethod->isStatic()) {
                    throw new \Exception('Handler\'s init() method must be static');
                }

                $handlerReflectionMethod->invoke('init');

                unset($handlerReflection);
                unset($handlerReflectionMethod);
            } catch (\Exception $ex) {
                \App::Log()->logWarn('Cannot init handler ' . $handler, $ex->getMessage());
            }
        }
    }
}
