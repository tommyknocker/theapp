<?php
/**
 * Implement smart magic properties getting
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Traits;

use App;

trait Getter
{

    use \App\Traits\CallMethod;

    /**
     * Name of property, that contains all the stuff
     * @var string 
     */
    private $dataFieldName = 'data';

    public function __call($method, $args)
    {
        if (substr($method, 0, 3) === 'get') {

            if (!isset($this->{$this->dataFieldName}) || !is_array($this->{$this->dataFieldName})) {
                App::Log()->addWarning('No such field or field is not an array');
                return null;
            }

            $method = App::Format()->camelCaseToUnderScore(substr($method, 3))->result;

            return isset($this->{$this->dataFieldName}[$method]) ? $this->{$this->dataFieldName}[$method] : null;
        } else {
            return null;
        }
    }

    /**
     * Set the name of data stuff property
     * @param string $name
     */
    public function setDataFieldName($name)
    {
        $this->dataFieldName = $name;
    }
}
