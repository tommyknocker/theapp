<?php
/**
 * Direct handler execution
 * 
 * App::Handler('HandlerName')->HandlerMethod();
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use App\Handlers;

class Handler
{

    use \App\Traits\CallMethod,
        \App\Traits\NoSingleton;

    /**
     * Handler object
     * @var object 
     */
    private $handlerObject = null;

    public function __construct($handlerName)
    {
        $handlerName = '\\App\\Handlers\\' . $handlerName;
        $this->handlerObject = new $handlerName();
    }

    /**
     * Call handler method
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->handlerObject, $method], $arguments);
    }
}
