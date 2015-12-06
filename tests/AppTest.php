<?php
/**
 * App class testing
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
class AppTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        require_once '__files/TestChaining.php';
        require_once '__files/TestCallable.php';
        require_once '__files/TestNoSingleton.php';
    }

    public function testArgumentsPassedToConstruct()
    {
        $this->assertTrue(App::TestChaining(1, 2)->method1()->result === 1);
    }

    public function testResultIsPassedToAnotherMethod()
    {
        $this->assertTrue(App::TestChaining(1, 2)->method1()->method2('result')->result === 1);
    }

    public function testObjectCanBeInsertedManually()
    {
        $testChaining = new TestChaining(1, 2);
        App::ld('TestChaining2', $testChaining);
        $this->assertTrue(App::TestChaining2()->method1()->result === 1);
    }

    public function testCanSwitchToOtherObjectInChain()
    {
        $this->assertTrue(App::TestChaining()->method3(3)->sw('TestChaining2')->method1()->result === 1);
    }

    public function testResultIsPassedToOtherObject()
    {
        $this->assertTrue(App::TestChaining()->method3(4)->method1()->sw('TestChaining2')->method3('result')->method1()->result === 4);
    }

    public function testCanCallAppInChain()
    {
        App::TestChaining()->method3(5);
        $this->assertTrue(App::TestChaining2()->method3(App::TestChaining()->method1()->result)->method1()->result === 5);
    }

    public function testCanSetAndGetParamFromObject()
    {
        App::TestChaining()->param = 'value';
        App::TestChaining2()->param = 'value2';
        $this->assertFalse(App::TestChaining2()->param === 'value');
        $this->assertTrue(App::TestChaining()->param === 'value');
    }

    public function testCanUnsetObjectParam() {
        unset(App::TestChaining2()->param);
        $this->assertNull(@App::TestChaining2()->param);
    }

    public function testCanUnsetObjectIntance() {
        unset(App::TestChaining2()->instance);
        try{
            App::TestChaining2()->instance;
        } catch (Exception $ex) {
            $this->assertEquals('Class TestChaining2 does not exist', $ex->getMessage());
        }
    }

    public function testObjectWillBeInitializedInEachCallIfTNoSingletonTraitIsUsed()
    {
        $this->assertTrue(App::TestNoSingleton(1)->method1()->result === 1);
        $this->assertTrue(App::TestNoSingleton(2)->method1()->result === 2);
    }

    public function testObjectCanUseOwnMagicCallMethodifTCallableTraitIsUsed()
    {
        App::TestCallable()->testMethod('param1', 'param2');

        $this->assertTrue(App::TestCallable()->method1()->result === 'testMethod');
        $this->assertTrue(App::TestCallable()->method2()->result === ['param1', 'param2']);
    }

}
