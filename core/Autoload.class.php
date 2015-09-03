<?php
/**
 * Smart autoloader
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;

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
    public function addNameSpace($namespace)
    {
        $this->params['namespace'] = $namespace;
    }

    /**
     * Add path
     * @param string $path Directory
     * @throws \Exception
     */
    public function addPath($path)
    {

        if (!is_dir($path))
            throw new \Exception('Path is not a directory: ' . $path);

        if (!is_readable($path))
            throw new \Exception('Directory is not readable: ' . $path);

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
     * @throws \Exception
     */
    public function register()
    {

        if (!array_key_exists('path', $this->params))
            throw new \Exception('No path\'s registered. Try addPath first');

        if (!array_key_exists('ext', $this->params))
            $this->params['ext'] = '.php';

        $code = [];

        if (array_key_exists('namespace', $this->params)) {
            $code[] = '$ns = "\\\\' . $this->params['namespace'] . '\\\\"';
        } else {
            $code[] = '$ns = "\\\\"';
        }

        $code[] = 'if(strpos("\\\\" . $class, $ns) !== 0) return false';
        $code[] = '$class = explode("\\\\", $class)';
        $code[] = '$class = array_pop($class)';
        $code[] = '$file = "' . $this->params['path'] . DS . '" . $class . "' . $this->params['ext'] . '"';
        $code[] = 'if(file_exists($file)) require_once($file)';

        spl_autoload_register(create_function('$class', implode(';' . PHP_EOL, $code) . ';'));

        $this->params = [];
    }
}
