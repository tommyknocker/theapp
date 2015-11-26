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
 * @method \App\Core\MysqliDb \App\Core\DB
 * @method \App\Core\Engine \App\Core\Engine
 * @method \App\Core\Event \App\Core\Event
 * @method \App\Core\Format \App\Core\Format
 * @method \App\Core\Get \App\Core\Get
 * @method \App\Core\Handler \App\Core\Handler
 * @method \App\Core\HTTP \App\Core\HTTP 
 * @method \App\Core\Log \Monolog\Logger
 * @method \App\Core\Model \App\Core\Model
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
     * Protect from creating object
     */
    private function __construct($currentObject)
    {
        $this->currentObject = $currentObject;
    }

    /**
     * Call class method
     * @param string $method
     * @param array $params
     * @throws Exception
     */
    public function __call($method, $params)
    {        
        $objectData = State::get($this->currentObject);

        if (in_array('result', $params, true)) {
            $params[array_search('result', $params)] = $objectData['result'];
        }

        if ($objectData['callable'] && !method_exists($objectData['instance'], $method)) {
            $params = array_merge([$method], [$params]);
            $method = '__call';
        }
        
        $objectReflectionMethod = new ReflectionMethod($objectData['instance'], $method);
        State::setResult($this->currentObject, $objectReflectionMethod->invokeArgs($objectData['instance'], $params));

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

        $app = new self($name);

        $objectData = State::get($name);

        if ($objectData && $objectData['singleton']) {
            return $app;
        }

        try {
            $objectInstance = new ReflectionClass('\\App\\Core\\' . $name);
        } catch (ReflectionException $e) {
            if (!class_exists($name)) {
                throw new Exception('Class ' . $name . ' does not exist');
            } else {
                $objectInstance = new ReflectionClass($name);
            }
        }

        if (!$objectInstance->isInstantiable()) {
            throw new Exception('Cannot create object from class: ' . $name);
        }

        State::set($name, 
            $objectInstance->getConstructor() ? $objectInstance->newInstanceArgs($args) : $objectInstance->newInstance(), 
            self::getObjectParams($name, $objectInstance));

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
     * @return mixed
     */
    public function __get($param)
    {
        $objectData = State::get($this->currentObject);

        switch ($param) {
            case 'instance':
                return $objectData['instance'];
            case 'result':
                return $objectData['result'];
            default:
                return $objectData['instance']->$param;
        }
    }

    /**
     * Set class variable
     * @param string $param
     * @param mixed $value
     */
    public function __set($param, $value)
    {
        $objectData = State::get($this->currentObject);
        $objectData['instance']->$param = $value;
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
        $objectData = State::get($this->currentObject);

        switch ($name) {
            case 'instance':
                State::delete($name);
                break;
            default:
                unset($objectData['instance']->$name);
        }
    }

    /**
     * Protect from unserializing
     */
    private function __wakeup()
    {
        
    }

    /**
     * Collect all trait names from object
     * @param \ReflectionClass $reflectionObject
     * @return array
     */
    private static function getTraitNamesRecursive($reflectionObject)
    {
        $names = [];
        foreach ($reflectionObject->getTraits() as $trait) {
            $names[] = $trait->name;
            $names = array_merge($names, self::getTraitNamesRecursive($trait));
        }
        return $names;
    }

    /**
     * Get object options
     * @param string $name
     * @param ReflectionObject $object
     */
    private static function getObjectParams($name, $object)
    {           
        $traits = self::getTraitNamesRecursive($object);        
        
        return [
            'singleton' => is_array($traits) && in_array('App\Traits\NoSingleton', $traits, true) ? false : true,
            'callable' => is_array($traits) && in_array('App\Traits\CallMethod', $traits, true)
        ];
    }

    /**
     * Manually put object into objects storage
     * @param string $name Object's alias
     * @param object $object Object
     * @param bool $overwrite Optional Overwrite protection
     * @throws \Exception
     */
    public static function ld($name, $object, $overwrite = false)
    {
        if (!is_string($name) || !is_object($object)) {
            throw new Exception('Wrong params passed');
        }

        $objectData = State::get($name);        
        
        if ($objectData && !$overwrite) {
            throw new Exception('Object is already exists while overwrite is not allowed');
        }

        $reflection = new ReflectionObject($object);
        
        State::set($name, $object, self::getObjectParams($name, $reflection));
    }
   
    /**
     * Switch current object in chain
     * @return \App
     */
    public function sw($name, $args = [])
    {
        $objectData = State::get($this->currentObject);
        $previousObjectResult = $objectData['result'];
        $app = self::__callStatic($name, $args);
        State::setResult($name, $previousObjectResult);
        return $app;
    }
}
