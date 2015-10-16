<?php
/**
 * Smart autoloader
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use Exception;

class Autoload
{

    /**
     * Autoloader params storage
     * @var array
     */
    private $params = [];

    /**
     * Add namespace
     * @param string $namespace
     */
    public function addNamespace($namespace)
    {
        $this->params['namespace'] = $namespace;
    }

    /**
     * Add path
     * @param string $path Directory
     * @throws Exception
     */
    public function addPath($path)
    {
        if (!is_dir($path)) {
            throw new Exception('Path is not a directory: ' . $path);
        }

        if (!is_readable($path)) {
            throw new Exception('Directory is not readable: ' . $path);
        }

        $this->params['path'] = $path;
    }

    /**
     * Add file extension
     * @param string $ending Ending of a file
     */
    public function addExt($ending)
    {
        $this->params['ext'] = $ending;
    }

    /**
     * Register new autoload path
     * @throws Exception
     */
    public function register()
    {
        if (!array_key_exists('path', $this->params)) {
            throw new Exception('No path\'s registered. Try addPath first');
        }

        if (!array_key_exists('ext', $this->params)) {
            $this->params['ext'] = '.php';
        }

        $code = [];

        if (isset($this->params['namespace'])) {
            $this->params['namespace'] = '\\' . trim($this->params['namespace']) . '\\';
        } else {
            $this->params['namespace'] = '\\';
        }

        spl_autoload_register($this->autoloader($this->params));

        $this->params = [];
    }

    /**
     * Autoloader closure
     * @param array $params
     * @return \Closure
     */
    public function autoloader($params)
    {
        return function($class) use ($params) {
            if (strpos('\\' . $class, $params['namespace']) !== 0) {
                return false;
            }

            $class = explode('\\', $class);
            $class = array_pop($class);

            $file = $params['path'] . DS . $class . $params['ext'];

            if (file_exists($file)) {
                require_once($file);
            }
        };
    }
}
