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
 * @method \App\Core\Handler \App\Core\Handler
 * @method \App\Core\HTTP \App\Core\HTTP 
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
     * Objects stack
     * @var array 
     */
    private $stack = [];

    /**
     * Protect from creating object
     */
    private function __construct()
    {
        
    }

    /**
     * Call class method
     * @param string $method
     * @param array $params
     * @throws Exception
     */
    public function __call($method, $params)
    {
        $currentObj = $this->objects[$this->currentObject]['instance'];

        if (!is_object($currentObj)) {
            throw new Exception("Class " . $this->currentObject . " wasn't initialized");
        }

        if (in_array('result', $params, true)) {
            $params[array_search('result', $params)] = $this->objects[$this->currentObject]['result'];
        }

        $currentObject = $this->currentObject;
        $stack = $this->stack;

        if ($this->objects[$this->currentObject]['callable'] && !method_exists($currentObj, $method)) {
            try {
                $objectReflectionMethod = new ReflectionMethod($currentObj, '__call');
                $params = array_merge([$method], [$params]);
                $this->objects[$this->currentObject]['result'] = $objectReflectionMethod->invokeArgs($currentObj, $params);
            } catch (ReflectionException $e) {
                $object = $this->getFromStack();
                if ($object) {
                    $this->currentObject = $object;
                    call_user_func_array([$this, '__call'], [$method, $params]);
                } else {
                    throw new Exception("Class " . $this->currentObject . " has no method " . $method);
                }
            }
        } else {
            try {
                $objectReflectionMethod = new ReflectionMethod($currentObj, $method);
                $this->objects[$this->currentObject]['result'] = $objectReflectionMethod->invokeArgs($currentObj, $params);
            } catch (ReflectionException $e) {
                $object = $this->getFromStack();
                if ($object) {
                    $this->currentObject = $object;
                    call_user_func_array([$this, '__call'], [$method, $params]);
                } else {
                    throw new Exception("Class " . $this->currentObject . " has no method " . $method);
                }
            }
        }

        $this->stack = $stack;
        $this->currentObject = $currentObject;

        return $this;
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

        if (isset($app->objects[$name]) && $app->objects[$name]['singleton']) {
            $app->setToStack($app->currentObject);
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

        $app->setToStack($app->currentObject);
        $app->objects[$name] = [];

        $currentObject = $app->currentObject;
        $stack = $app->stack;

        $app->objects[$name]['instance'] = $obj->getConstructor() ? $obj->newInstanceArgs($args) : $obj->newInstance();

        $app->stack = $stack;
        $app->currentObject = $currentObject;


        $traits = $obj->getTraitNames();
        $app->objects[$name]['singleton'] = is_array($traits) && in_array('TNoSingleton', $traits, true) ? false : true;
        $app->objects[$name]['callable'] = is_array($traits) && in_array('TCallable', $traits, true);
        $app->objects[$name]['result'] = null;

        return $app;
    }

    /**
     *  Protect from cloning
     */
    private function __clone()
    {
        
    }

    /**
     * Get value from class
     * @param string  $param
     * @throws Exception
     * @return mixed
     */
    public function __get($param)
    {
        $currentObj = $this->objects[$this->currentObject]['instance'];

        if (!is_object($currentObj)) {
            throw new Exception("Class " . $this->currentObject . " wasn't initialized");
        }

        switch ($param) {
            case 'instance':
                $result = $currentObj;
                break;
            case 'result':
                $result = $this->objects[$this->currentObject]['result'];
                break;
            default:
                $result = $currentObj->$param;
                break;
        }

        $this->currentObject = $this->getFromStack();

        return $result;
    }

    /**
     * Set class variable
     * @param string $param
     * @param mixed $value
     * @throws \Exception
     */
    public function __set($param, $value)
    {
        $currentObj = $this->objects[$this->currentObject]['instance'];

        if (!is_object($currentObj)) {
            throw new Exception("Class " . $this->currentObject . " wasn't initialized");
        }

        switch ($param) {
            case 'instance':
                if (is_object($value)) {
                    $this->objects[$this->currentObject]['instance'] = $value;
                } else {
                    throw new Exception("Couldn't set instance of the class " . $this->currentObject);
                }
                break;
            default:
                $currentObj->$param = $value;
        }

        $this->currentObject = $this->getFromStack();
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
     * unset() overloading
     * @param striing $name
     */
    public function __unset($name)
    {
        $currentObj = $this->objects[$this->currentObject]['instance'];

        if (!is_object($currentObj)) {
            throw new Exception("Class " . $this->currentObject . " wasn't initialized");
        }

        switch ($name) {
            case 'instance':
                unset($this->objects[$this->currentObject]);
                break;
            default:
                unset($currentObj->$name);
        }

        $this->currentObject = $this->getFromStack();
    }

    /**
     * Protect from unserializing
     */
    private function __wakeup()
    {
        
    }

    /**
     * Get object from stack
     * @return string
     */
    private function getFromStack()
    {
        $object = array_pop($this->stack);
        return $object;
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
        $app->objects[$name]['singleton'] = is_array($traits) && in_array('TNoSingleton', $traits, true) ? false : true;
        $app->objects[$name]['callable'] = is_array($traits) && in_array('TCallable', $traits, true);
        $app->objects[$name]['result'] = null;

        $app->setToStack($app->currentObject);

        return $app;
    }

    /**
     * Remove current class from stack (for objects that implement their own __call/__get/__set methods)
     */
    public static function removeFromStack()
    {
        $app = self::getInstance();
        $app->getFromStack();
    }

    /**
     * Switch current object in chain
     * @return \App
     */
    public function sw($name, $args)
    {
        return self::__callStatic($name, $args);
    }

    /**
     * Set object to stack
     * @param sting $object
     */
    private function setToStack($object)
    {
        array_push($this->stack, $object);
    }
}
