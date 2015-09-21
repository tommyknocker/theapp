<?php

/**
 * The App. Factory. Chainable
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * 
 * IDE method helpers
 * @method \App\Core\Autoload \App\Core\Autoload
 * @method \App\Core\Config \App\Core\Config 
 * @method \App\Core\Container \App\Core\Container
 * @method \App\Core\Cron  \App\Core\Cron
 * @method \App\Core\Daemon \App\Core\Daemon
 * @method \App\Core\DB \App\Core\MysqliDb
 * @method \App\Core\Engine \App\Core\Engine
 * @method \App\Core\Event \App\Core\Event
 * @method \App\Core\Format \App\Core\Format
 * @method \App\Core\Get \App\Core\Get
 * @method \App\Core\Log \Monolog\Logger
 * @method \App\Core\JSON \App\Core\JSON
 * @method \App\Core\Timer \App\Core\Timer
 * @method \App\Core\Tpl \App\Core\Tpl
 * @method \App\Core\I18n \App\Core\I18n
 * 
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 * */
class App
{

    /**
     * Objects storage array 
     * @var array
     */
    private $objects = [];

    /**
     * Current object's name
     * @var string 
     */
    private $currentObject = null;

    /**
     * Instance of the App. For chaining support
     * @var object
     */
    private static $instance = null;

    /**
     * Protect from creating object
     */
    private function __construct()
    {
        
    }

    /**
     *  Protect from cloning
     */
    private function __clone()
    {
        
    }

    /**
     * Protect from unserializing
     */
    private function __wakeup()
    {
        
    }

    /**
     * Get instance
     * @return \App
     */
    private static function getInstance()
    {

        if (!self::$instance instanceOf App) {
            self::$instance = new App();
        }

        return self::$instance;
    }

    /**
     * Return the App version
     * @return string
     */
    public function __toString()
    {
        return 'The App. Version ' . APP_VERSION;
    }

    /**
     * Get value from class
     * @param string  $param
     * @throws Exception
     * @return mixed
     */
    public function __get($param)
    {

        $app = self::getInstance();

        $currentObj = &$app->objects[$app->currentObject]['instance'];

        if (!is_object($currentObj)) {
            throw new Exception("Class " . $app->currentObject . " wasn't initialized");
        }

        switch ($param) {
            case 'instance':
                return $currentObj;
            case 'result':
                return $app->objects[$app->currentObject]['result'];
            default:
                return $currentObj->$param;
        }
    }

    /**
     * Set class variable
     * @param string $param
     * @param mixed $value
     * @throws \Exception
     */
    public function __set($param, $value)
    {

        $app = self::getInstance();

        $currentObj = &$app->objects[$app->currentObject]['instance'];

        if (!is_object($currentObj)) {
            throw new Exception("Class " . $app->currentObject . " wasn't initialized");
        }

        switch ($param) {
            case 'instance':
                if (is_object($value)) {
                    $app->objects[$app->currentObject]['instance'] = $value;
                } else {
                    throw new Exception("Couldn't set instance of the class " . $app->currentObject);
                }
                break;
            default:
                $currentObj->$param = $value;
        }
    }

    /**
     * Call class method
     * @param string $method
     * @param array $params
     * @throws Exception
     */
    public function __call($method, $params)
    {

        $app = self::getInstance();

        $currentObj = &$app->objects[$app->currentObject]['instance'];

        if (!is_object($currentObj)) {
            throw new Exception("Class " . $app->currentObject . " wasn't initialized");
        }

        if (!$app->objects[$app->currentObject]['callable'] && !method_exists($currentObj, $method)) {
            throw new Exception("Class " . $app->currentObject . " has no method " . $method);
        }

        if (in_array('result', $params, true)) {
            $params[array_search('result', $params)] = $app->objects[$app->currentObject]['result'];
        }

        $currentObject = $app->currentObject;
        $objectReflectionMethod = new ReflectionMethod($currentObj, $method);
        $app->objects[$app->currentObject]['result'] = $objectReflectionMethod->invokeArgs($currentObj, $params);
        $app->currentObject = $currentObject;

        return $app;
    }

    /**
     * Magic __callStatic method
     * @param string $name name of a class
     * @param array $args Optional arguments
     * @return \App
     */
    public static function __callStatic($name, $args)
    {

        $app = self::getInstance();

        $app->currentObject = $name;

        if (array_key_exists($name, $app->objects)) {
            return $app;
        }


        try {
            $obj = new ReflectionClass('\\App\\Core\\' . $name);
        } catch (Exception $e) {

            if (!class_exists($name)) {
                throw new Exception('Class ' . $name . ' does not exist');
            } else {
                $obj = new ReflectionClass($name);
            }
        }

        if (!$obj->isInstantiable()) {
            throw new Exception('Cannot create object from class: ' . $name);
        }

        $app->objects[$name] = [];

        $currentObject = $app->currentObject;
        $app->objects[$name]['instance'] = $obj->getConstructor() ? $obj->newInstanceArgs($args) : $obj->newInstance();
        $app->currentObject = $currentObject;
        $traits = $obj->getTraitNames();
        $app->objects[$name]['callable'] = is_array($traits) && in_array('TCallable', $traits, true);
        $app->objects[$name]['result'] = null;

        return $app;
    }

    /**
     * Manually put object into objects storage
     * @param string $string Object's alias
     * @param object $object Object
     * @param bool $overwrite Optional Overwrite protection
     * @throws \Exception
     * @return \App
     */
    public static function ld($name, $object, $overwrite = false)
    {

        $app = self::getInstance();

        if (!is_string($name) || !is_object($object))
            throw new Exception('Wrong params passed');

        if (array_key_exists($name, $app->objects) && !$overwrite)
            throw new Exception('Object\'s already exists while overwrite is not allowerd');

        $app->currentObject = $name;

        $reflection = new ReflectionObject($object);

        $app->objects[$name] = [];
        $app->objects[$name]['instance'] = $object;
        $traits = $reflection->getTraitNames();
        $app->objects[$name]['callable'] = is_array($traits) && in_array('TCallable', $traits, true);
        $app->objects[$name]['result'] = null;

        return $app;
    }

    /**
     * Select current object in chain
     * @return \App
     */
    public function sel($name, $args)
    {
        return self::__callStatic($name, $args);
    }
}
