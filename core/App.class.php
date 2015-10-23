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
 * @method \App\Core\UUID \App\Core\UUID
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 * */
class App
{

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
     * Objects storage array 
     * @var array
     */
    private $objects = [];

    /**
     * Objects stack
     * @var array 
     */
    private $stack = [
        'object' => [],
        'state' => []
    ];

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

        if ($this->objects[$this->currentObject]['callable'] && !method_exists($currentObj, $method)) {
            $params = array_merge([$method], [$params]);
            $method = '__call';
        }

        $this->pushToStack('state', [$this->currentObject, $this->stack['object']]);

        try {
            $objectReflectionMethod = new ReflectionMethod($currentObj, $method);
            $this->objects[$this->currentObject]['result'] = $objectReflectionMethod->invokeArgs($currentObj, $params);
        } catch (ReflectionException $e) {
            $object = $this->popFromStack('object');
            if ($object) {
                $this->currentObject = $object;
                call_user_func_array([$this, '__call'], [$method, $params]);
            } else {
                throw new Exception("Class " . $this->currentObject . " has no method " . $method);
            }
        }

        list($this->currentObject, $this->stack['object']) = $this->popFromStack('state');

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
            $app->pushToStack('object', $name);
            return $app;
        }

        try {
            $object = new ReflectionClass('\\App\\Core\\' . $name);
        } catch (ReflectionException $e) {
            if (!class_exists($name)) {
                throw new Exception('Class ' . $name . ' does not exist');
            } else {
                $object = new ReflectionClass($name);
            }
        }

        if (!$object->isInstantiable()) {
            throw new Exception('Cannot create object from class: ' . $name);
        }

        $app->pushToStack('object', $name);
        $app->pushToStack('state', [$name, $app->stack['object']]);
        $app->initObject($name, $object);

        $app->objects[$name]['instance'] = $object->getConstructor() ? $object->newInstanceArgs($args) : $object->newInstance();

        list($app->currentObject, $app->stack['object']) = $app->popFromStack('state');

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
        $currentObj = $this->getCurrentObjectIntance();

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

        $this->currentObject = $this->popFromStack('object');

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
        $currentObj = $this->getCurrentObjectIntance();

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

        $this->currentObject = $this->popFromStack('object');
    }

    /**
     * Return the App version
     * @return string
     */
    public function __toString()
    {
        return 'The App. Tiny, fast & powerful microframework. Version ' . APP_VERSION;
    }

    /**
     * unset() overloading
     * @param striing $name
     */
    public function __unset($name)
    {
        $currentObj = $this->getCurrentObjectIntance();

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

        $this->currentObject = $this->popFromStack('object');
    }

    /**
     * Protect from unserializing
     */
    private function __wakeup()
    {
        
    }

    /**
     * Get current object instance
     * @return object
     */
    private function getCurrentObjectIntance()
    {
        return $this->objects[$this->currentObject]['instance'];
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
     * Collect all trait names from object
     * @param \ReflectionClass $reflectionObject
     * @return array
     */
    private function getTraitNamesRecursive($reflectionObject)
    {
        $names = [];
        foreach ($reflectionObject->getTraits() as $trait) {
            $names[] = $trait->name;
            $names = array_merge($names, $this->getTraitNamesRecursive($trait));
        }
        return $names;
    }

    /**
     * Store object's params in local storate
     * @param string $name
     * @param object $object
     */
    private function initObject($name, $object)
    {
        $this->objects[$name] = [];
        $traits = $this->getTraitNamesRecursive($object);
        
        $this->objects[$name]['singleton'] = is_array($traits) && in_array('App\Traits\NoSingleton', $traits, true) ? false : true;
        $this->objects[$name]['callable'] = is_array($traits) && in_array('App\Traits\CallMethod', $traits, true);
        $this->objects[$name]['result'] = null;
    }

    /**
     * Manually put object into objects storage
     * @param string $name Object's alias
     * @param object $object Object
     * @param bool $overwrite Optional Overwrite protection
     * @throws \Exception
     * @return \App
     */
    public static function ld($name, $object, $overwrite = false)
    {

        $app = self::getInstance();

        if (!is_string($name) || !is_object($object)) {
            throw new Exception('Wrong params passed');
        }

        if (array_key_exists($name, $app->objects) && !$overwrite) {
            throw new Exception('Object is already exists while overwrite is not allowed');
        }

        $app->currentObject = $name;

        $reflection = new ReflectionObject($object);

        $app->initObject($name, $reflection);
        $app->objects[$name]['instance'] = $object;

        return $app;
    }

    /**
     * Pop data from stack
     * @param string $type
     * @return mixed
     */
    private function popFromStack($type)
    {
        return array_pop($this->stack[$type]);
    }

    /**
     * Set data to stack
     * @param sting $type
     * @param mixed $data
     */
    private function pushToStack($type, $data)
    {
        array_push($this->stack[$type], $data);
    }

    /**
     * Remove current class from stack (for objects that implement their own __call/__get/__set methods)
     */
    public static function removeFromStack()
    {
        $app = self::getInstance();
        $app->popFromStack('object');
    }

    /**
     * Switch current object in chain
     * @return \App
     */
    public function sw($name, $args = [])
    {
        $previousObjectResult = $this->objects[$this->currentObject]['result'];
        self::__callStatic($name, $args);
        $this->objects[$name]['result'] = $previousObjectResult;
        return $this;
    }
}
