<?php
/**
 * Test app no singleton support
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */

class TestNoSingleton
{

    use \App\Traits\NoSingleton;

    private $argument = null;

    public function __construct($argument)
    {
        $this->argument = $argument;
    }

    public function method1()
    {
        return $this->argument;
    }
}
