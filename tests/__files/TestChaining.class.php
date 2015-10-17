<?php
/**
 * Test app chaining work
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */

class TestChaining
{
    private $argument1 = null;
    private $argument2 = null;
    
    public function __construct($argument1, $argument2) 
    {
        $this->argument1 = $argument1;
        $this->argument2 = $argument2;
    }
    
    public function method1() {
        return $this->argument1;
    }
    
    public function method2($argument) {
        return $argument;
    }
    
    public function method3($argument) {
        $this->argument1 = $argument;
    }
    
    
}
