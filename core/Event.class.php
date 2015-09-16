<?php
/**
 * Event handling class.
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;
use App, Exception, ReflectionMethod, ReflectionClass, Sabre\Event\EventEmitter;

class Event
{

    /**
     * Sabre EventEmitter object
     * @var EventEmitter
     */
    private $eventEmitter = null;
    
    public function __construct()
    {
        $this->eventEmitter = new EventEmitter();
    }
    
    /**
     * Subscribe class method to particular event
     * Event subscription is only allowed in handler's init() function
     * 
     * @param string $event Event to register for
     * @param string $classMethod
     */
    public function subscribe($event, $classMethod)
    {
        $event = mb_strtolower($event, 'UTF-8');
        
        if(strpos($event, '/') !== false) {
            $event = rtrim($event, '/') . '/';
        }
        
        $trace = debug_backtrace();

        $initFound = false;

        while ($initMethod = array_shift($trace)) {
            if (array_key_exists('function', $initMethod) && $initMethod['function'] == 'init' && array_key_exists('class', $initMethod)) {
                $initFound = true;
                break;
            }
        }

        try {

            if (!$initFound) {
                throw new Exception('Registering events only allowed in handler\'s static init() method');
            }

            $handlerReflection = new ReflectionClass($initMethod['class']);

            if (!$handlerReflection->hasMethod($classMethod)) {
                throw new Exception('Handler ' . $initMethod['class'] . ' doen\'t have such method');
            }

            $handlerReflectionMethod = $handlerReflection->getMethod($classMethod);

            if ($handlerReflectionMethod->isStatic()) {
                throw new Exception('Method must not be static');
            }

            if ($handlerReflectionMethod->isPrivate()) {
                throw new Exception('Method must be public');
            }
            
            $this->eventEmitter->on($event, array(new $initMethod['class'], $classMethod));
                        
        } catch (Exception $e) {
            App::Log()->logError('Cannot register method ' . $classMethod . ' to event ' . $event, $e->getMessage());
        }
    }

    /**
     * Fire an event
     * 
     * @param string $event
     * @param array $arguments
     */
    public function fire($event, $arguments = array())
    {
        $event = mb_strtolower($event, 'UTF-8');
        
        if(strpos($event, '/') !== false) {
            $event = rtrim($event, '/') . '/';
        }        
                
        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }

        $isFired = $this->eventEmitter->emit($event, $arguments);
        
        if($isFired) {
            App::Container()->set('fired:' . $event, 'yes');
        }
        
        if (strpos($event, 'cli:') !== false || strpos($event, 'web:') !== false) {
            $allEvent = str_replace(array('cli:', 'web:'), 'all:', $event);
            $isFired = $this->eventEmitter->emit($allEvent, $arguments);

            // set web:|cli: and all: event state to fired
            if($isFired) { 
                App::Container()->set('fired:' . $event, 'yes');
                App::Container()->set('fired:' . $allEvent, 'yes');
            }
        }
    }

    /**
     * Check whether event is fired
     * @param string $event
     * @return bool
     */
    public function isFired($event)
    {
        $event = mb_strtolower($event, 'UTF-8');
        
        return App::Container()->get('fired:' . $event)->result === 'yes';
    }
}
