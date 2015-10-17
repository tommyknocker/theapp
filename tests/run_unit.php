<?php
/**
 * Unit tests collection
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */

require_once('includes/config.php');

require_once(TESTSUITE_PATH . 'unit_tester.php');


class UnitTests extends TestSuite {
    function UnitTests() {
        $this->TestSuite('Unit tests');
        $this->addFile(TEST_PATH . 'app_test.php'); 
        $this->addFile(TEST_PATH . 'autoload_test.php');
    }
}
