<?php
/**
 * Events class.
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;

class Event
{

    /**
     * Register class method to particular event
     * 
     * @param string $event Event to register for
     * @param string $classMethod
     */
    public function register($eventPath, $classMethod)
    {

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
                throw new \Exception('Registering events only allowed in handler\'s static init() method');
            }

            $handlerReflection = new \ReflectionClass($initMethod['class']);

            if (!$handlerReflection->hasMethod($classMethod)) {
                throw new \Exception('Handler ' . $initMethod['class'] . ' doen\'t have such method');
            }

            $handlerReflectionMethod = $handlerReflection->getMethod($classMethod);

            if ($handlerReflectionMethod->isStatic()) {
                throw new \Exception('Method must not be static');
            }

            if ($handlerReflectionMethod->isPrivate()) {
                throw new \Exception('Method must be public');
            }

            \App::Container()->add('event:' . $eventPath, array($initMethod['class'], $classMethod));
        } catch (\Exception $e) {
            \App::Log()->logError('Cannot register method ' . $classMethod . ' to event ' . $eventPath, $e->getMessage());
        }
    }

    /**
     * Fire an event
     * 
     * @param string $event
     */
    public function fire($event, $args = array())
    {
        $methods = \App::Container()->get('event:' . $event)->result;

        if (!is_array($methods)) {
            $methods = array();
        }

        if (!is_array($args)) {
            $args = array('arg' => $args);
        }

        if (strpos($event, 'cli:') !== false || strpos($event, 'web:') !== false) {
            $allMethods = \App::Container()->get('event:' . str_replace(array('cli:', 'web:'), 'all:', $event))->result;

            if ($allMethods) {
                $methods = array_merge($methods, $allMethods);
            }
        }

        if (!$methods) {
            return;
        }

        \App::Container()->set('fired:' . $event, 'yes');

        foreach ($methods as $method) {
            $reflectionMethod = new \ReflectionMethod($method[0], $method[1]);
            $reflectionMethod->invokeArgs(new $method[0], $args);
        }
    }

    /**
     * Check whether event is fired
     * @param string $event
     * @return bool
     */
    public function isFired($event)
    {
        return \App::Container()->get('fired:' . $event)->result === 'yes';
    }
}
