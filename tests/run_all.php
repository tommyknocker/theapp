<?php
/**
 * Run all tests for TheApp
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
require_once('includes/config.php');

class AllTests extends TestSuite
{

    function AllTests()
    {
        $this->TestSuite('Tests for TheApp microframework');
        $this->addFile(TEST_PATH . 'run_unit.php');
//        $this->addFile(dirname(__FILE__) . '/shell_test.php');
//        $this->addFile(dirname(__FILE__) . '/live_test.php');
//        $this->addFile(dirname(__FILE__) . '/acceptance_test.php');
    }
}

?>