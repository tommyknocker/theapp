<?php
/**
 * Event handling class.
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use App,
    Exception,
    ReflectionClass,
    Sabre\Event\EventEmitter;

class Event
{

    /**
     * Sabre EventEmitter object
     * @var EventEmitter
     */
    private $eventEmitter = null;

    /**
     * Event chains storage (for supporting pcre expressions in event)
     * @var array
     */
    private $chains = [];

    /**
     * Matched elements for pcre event expressions
     * @var array
     */
    private $matched = null;

    /**
     * Randomly generated chain replacer
     * @var string
     */
    private $chainReplacer = "";

    public function __construct()
    {
        $this->eventEmitter = new EventEmitter();
        $this->chainReplacer = '{' . App::UUID()->v4()->result . '}';
    }

    /**
     * Determine and proceed with event expressions
     * @param string $event
     * @return string
     */
    private function proceedExpressions($event)
    {
        $this->matched = null;

        $eventChain = explode('/', $event);

        if (!$this->chains) {
            return $event;
        }

        foreach ($this->chains as $block) {
            $key = 0;
            $matched = [];

            foreach ($block as $chain) {

                if (preg_match('/^\(.*\)$/', $chain) && preg_match('/^' . $chain . '$/', $eventChain[$key])) {
                    $matched[] = $eventChain[$key];
                    $key++;
                } elseif (mb_strtolower($eventChain[$key], 'UTF-8') === mb_strtolower($chain, 'UTF-8')) {
                    $key++;
                } else {
                    break;
                }

                if ($key == count($block) && $key == count($eventChain)) {
                    $this->matched = $matched;
                    return implode('/', $block);
                }
            }
        }

        return $event;
    }

    /**
     * Determine and set event expressions
     * @param string $event
     * @return string
     */
    private function setExpressions($event)
    {
        $eventChain = explode('/', $event);

        $containsExpression = false;

        foreach ($eventChain as $chainIndex => $chain) {
            if (preg_match('/^\(.*\)$/', $chain)) {
                $containsExpression = true;
            }
        }

        if ($containsExpression) {
            $this->chains[] = $eventChain;
        }

        return $event;
    }

    /**
     * Subscribe class method to particular event
     * Event subscription is only allowed in handler's init() function
     *
     * @param string $event Event to register for
     * @param string $classMethod
     * @param int $priority
     */
    public function subscribe($event, $classMethod, $priority = 100)
    {
        if (strpos($event, '/') !== false) {
            $event = rtrim($event, '/') . '/';
            $event = $this->setExpressions($event);
        }

        $event = mb_strtolower($event, 'UTF-8');

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

            $wrapper = function() use ($initMethod, $classMethod) {
                return call_user_func_array([new $initMethod['class'], $classMethod], func_get_args());
            };
            $this->eventEmitter->on($event, $wrapper, $priority);

        } catch (Exception $e) {
            App::Log()->addError('Cannot register method {method} to event {event}: {message}', ['method' => $classMethod, 'event' => $event, 'message' => $e->getMessage()]);
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
        $calledEvent = $event;

        if (strpos($event, '/') !== false) {
            $event = rtrim($event, '/') . '/';
            $event = $this->proceedExpressions($event);
        }

        $event = mb_strtolower($event, 'UTF-8');
        $calledEvent = mb_strtolower($calledEvent, 'UTF-8');

        if (!is_array($arguments)) {
            $arguments = array($arguments);
        }

        $listeners = $this->eventEmitter->listeners($event);
        $isFired = $this->eventEmitter->emit($event, $arguments);

        if ($listeners && $isFired) {
            App::Container()->{'fired:' . $calledEvent} = 'yes';
        }

        if (strpos($event, 'cli:') !== false || strpos($event, 'web:') !== false) {
            $allEvent = str_replace(array('cli:', 'web:'), 'all:', $event);
            $calledAllEvent = str_replace(array('cli:', 'web:'), 'all:', $calledEvent);

            $listeners = $this->eventEmitter->listeners($allEvent);
            $isFired = $this->eventEmitter->emit($allEvent, $arguments);

            // set web:|cli: and all: event state to fired
            if ($listeners && $isFired) {
                App::Container()->{'fired:' . $calledEvent} = 'yes';
                App::Container()->{'fired:' . $calledAllEvent} = 'yes';
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
        return App::Container()->{'fired:' . $event} === 'yes';
    }

    /**
     * Retrive matched array
     * @return array
     */
    public function getMatched()
    {
        return $this->matched;
    }
}
