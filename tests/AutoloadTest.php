<?php
/**
 * Autoload class testing
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
class AutoloadTest extends PHPUnit_Framework_TestCase
{

    public function testCanLoadClassWithNamespaceAndAnyExtensionInAnyDirecotry()
    {
        App::Autoload()->addNamespace('\\Some\\Namespace1')->addExt('.someclass.php')->addPath(dirname(__FILE__) . '/__files')->register();
        $testAutoloadNamespace = new \Some\Namespace1\TestAutoloadNamespace();
        $this->assertEquals($testAutoloadNamespace->method1(), 'tested');
    }
}
