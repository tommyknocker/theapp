<?php

/**
 * The App. Factory. Chainable
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * 
 * IDE method helpers
 * @method \core\Autoload \core\Autoload
 * @method \core\Cache \core\Cache
 * @method \core\Config \core\Config 
 * @method \core\Container \core\Container
 * @method \core\Cron  \core\Cron
 * @method \core\Daemon \core\Daemon
 * @method \core\DB \core\MysqliDb
 * @method \core\Engine \core\Engine
 * @method \core\Event \core\Event
 * @method \core\Get \core\Get
 * @method \core\Log \core\Log
 * @method \core\Timer \core\Timer
 * @method \core\Tpl \core\Tpl
 * @method \core\Tr \core\Tr
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
     * Echo the App version
     */
    public function __toString()
    {
        echo 'The App. Version ' . APP_VERSION;
    }

    /**
     * Get value from class
     * @param string  $param
     * @throws \Exception
     * @return mixed
     */
    public function __get($param)
    {

        $app = self::getInstance();

        $currentObj = &$app->objects[$app->currentObject]['instance'];

        if (!is_object($currentObj))
            throw new \Exception("Class " . $app->currentObject . " wasn't initialized");

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

        if (!is_object($currentObj))
            throw new Exception("Class " . $app->currentObject . " wasn't initialized");

        switch ($param) {
            case 'instance':
                if (is_object($value))
                    $app->objects[$app->currentObject]['instance'] = $value;
                else
                    throw new Exception("Couldn't set instance of the class " . $app->currentObject);
                break;
            default:
                $currentObj->$param = $value;
        }
    }

    /**
     * Call class method
     * @param string $method
     * @param array $params
     * @throws \Exception
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
            $obj = new ReflectionClass('\\core\\' . $name);
        } catch (Exception $e) {

            if (!class_exists($name)) {
                throw new \Exception('Class ' . $name . ' does not exist');
            } else {
                App::Log()->logDebug('App trying to load not a core namespace class: ' . $name);
                $obj = new ReflectionClass($name);
            }
        }

        if (!$obj->isInstantiable()) {
            throw new \Exception('Cannot create object from class: ' . $name);
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

    /**
     * Destruct everything correctly
     */
    public function __destruct()
    {
        unset($errors);
        $this->objects = null;
        unset($this->objects);
        $this->currentObject = null;
        unset($this->currentObject);
        self::$instance = null;
    }
}
