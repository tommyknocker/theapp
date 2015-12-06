<?php
/**
 * Data container for current session
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

class Container
{

    /**
     * Container
     * @var array 
     */
    private $storage = [];

    /**
     * Get data from container
     * @param string $name Variable name
     * @return mixed Data
     */
    public function __get($name)
    {
        return array_key_exists($name, $this->storage) ? $this->storage[$name] : null;
    }

    /**
     * isset() overloading
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->storage[$name]);
    }

    /**
     * Put data to container
     * @param string $name Variable name
     * @param mixed $value Value
     */
    public function __set($name, $value)
    {
        $this->storage[$name] = $value;
    }

    /**
     * unset() overloading
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->storage[$name])) {
            unset($this->storage[$name]);
        }
    }

    /**
     * Append value to a container array
     * @param string $name
     * @param mixed $value
     */
    public function append($name, $value)
    {
        if (!array_key_exists($name, $this->storage)) {
            $this->storage[$name] = [];
        }

        $this->storage[$name][] = $value;
    }    
    
    /**
     * Get data by pattern
     * @param string $pattern Valid PCRE pattern
     * @return array
     */
    public function pattern($pattern)
    {
        $result = [];

        foreach ($this->storage as $key => $value) {
            if (preg_match($pattern, $key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
