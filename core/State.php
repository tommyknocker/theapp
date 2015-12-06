<?php
/**
 * Objects storage
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

class State
{
    /**
     * Object storage
     * @var array
     */
    private $objects = [];

    /**
     * Instance of the State.
     * @var State
     */
    private static $instance = null;

    /**
     * Protect from creating object
     */
    private function __construct()
    {

    }

    /**
     * Delete object from storage
     * @param string $name
     */
    public static function delete($name)
    {
        $state = self::getInstance();
        if(isset($state->objects[$name])) {
            unset($state->objects[$name]);
        }
    }

    /**
     * Get object data from storage
     * @param string $name
     * @return array
     */
    public static function get($name)
    {
        $state = self::getInstance();
        return isset($state->objects[$name]) ? $state->objects[$name] : null;
    }

    /**
     * Get instance
     * @return \State
     */
    private static function getInstance()
    {

        if (!self::$instance instanceOf self) {
            self::$instance = new State();
        }

        return self::$instance;
    }

    /**
     * Set object to storage
     * @param string $name
     * @param object $object
     * @param array $params
     */
    public static function set($name, $object, $params = [])
    {
        $state = self::getInstance();
        $state->objects[$name] = [
            'instance' => $object,
            'result' => null
        ];

        if(is_array($params)) {
            $state->objects[$name] = array_merge_recursive($state->objects[$name], $params);
        }
    }

    /**
     * Set last object method execution result
     * @param string $name
     * @param mixed $result
     */
    public static function setResult($name, $result)
    {
        $state = self::getInstance();
        $state->objects[$name]['result'] = $result;
    }

}
