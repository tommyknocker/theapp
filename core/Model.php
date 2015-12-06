<?php
/**
 * Direct model methods execution
 * 
 * App::Model('ModelName')->ModelMethod();
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

class Model
{

    use \App\Traits\CallMethod,
        \App\Traits\NoSingleton;

    /**
     * Model object
     * @var object 
     */
    private $modelObject = null;

    public function __construct($modelName)
    {
        $modelName = '\\App\\Models\\' . $modelName;
        $this->modelObject = new $modelName();
    }

    /**
     * Call model method
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if(method_exists($this->modelObject, $method)) {        
            return call_user_func_array([$this->modelObject, $method], $arguments);
        }
    }
}
