<?php

/**
 * Test app callable support
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
class TestCallable
{

    use TCallable;
    
    private $calledName = null;
    private $calledArguments = null;
    
    public function __call($name, $arguments)
    {
        $this->calledName = $name;
        $this->calledArguments = $arguments;
    }
    
    public function method1() {
        return $this->calledName;
    }
    
    public function method2() {
        return $this->calledArguments;
    }
}
