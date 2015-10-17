<?php
/**
 * Autoload class testing
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
require_once('includes/config.php');

class TestAutoload extends UnitTestCase {
    
    public function testCanLoadClassWithNamespaceAndAnyExtensionInAnyDirecotry() {
        App::Autoload()->addNamespace('\\Some\\Namespace1')->addExt('.someclass.php')->addPath(TEST_PATH . 'includes')->register();        
        $testAutoloadNamespace = new \Some\Namespace1\TestAutoloadNamespace();        
        $this->assertEqual($testAutoloadNamespace->method1(), 'tested');
    }
    
}