<?php
/**
 * Variable filtering class
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;

class Get
{

    /**
     * Get variable from enviroment superglobal
     * @param string $variable
     * @param int $filter
     * @param int|array $options
     * @return mixed
     * @see http://www.php.net/manual/en/function.filter-input.php
     */
    public function env($variable, $filter = FILTER_DEFAULT, $options = null)
    {
        return filter_input(INPUT_ENV, $variable, $filter, $options);
    }

    /**
     * Get variable from enviroment superglobal
     * @param string $variable
     * @param int $filter
     * @param int|array $options
     * @return mixed
     * @see http://www.php.net/manual/en/function.filter-input.php
     */
    public function get($variable, $filter = FILTER_DEFAULT, $options = null)
    {
        return filter_input(INPUT_GET, $variable, $filter, $options);
    }

    /**
     * Get variable from enviroment superglobal
     * @param string $variable
     * @param int $filter
     * @param int|array $options
     * @return mixed
     * @see http://www.php.net/manual/en/function.filter-input.php
     */
    public function post($variable, $filter = FILTER_DEFAULT, $options = null)
    {
        return filter_input(INPUT_POST, $variable, $filter, $options);
    }

    /**
     * Get variable from enviroment superglobal
     * @param string $variable
     * @param int $filter
     * @param int|array $options
     * @return mixed
     * @see http://www.php.net/manual/en/function.filter-input.php
     */
    public function request($variable, $filter = FILTER_DEFAULT, $options = null)
    {
        return filter_input(INPUT_REQUEST, $variable, $filter, $options);
    }

    /**
     * Get variable from enviroment superglobal
     * @param string $variable
     * @param int $filter
     * @param int|array $options
     * @return mixed
     * @see http://www.php.net/manual/en/function.filter-input.php
     */
    public function session($variable, $filter = FILTER_DEFAULT, $options = null)
    {
        return filter_input(INPUT_SESSION, $variable, $filter, $options);
    }

    /**
     * Get variable from enviroment superglobal
     * @param string $variable
     * @param int $filter
     * @param int|array $options
     * @return mixed
     * @see http://www.php.net/manual/en/function.filter-input.php
     */
    public function server($variable, $filter = FILTER_DEFAULT, $options = null)
    {
        return filter_input(INPUT_SERVER, $variable, $filter, $options);
    }

    /**
     * Get variable from enviroment superglobal
     * @param string $variable
     * @param int $filter
     * @param int|array $options
     * @return mixed
     */
    public function cookie($variable, $filter = FILTER_DEFAULT, $options = null)
    {
        return filter_input(INPUT_COOKIE, $variable, $filter, $options);
    }

    /**
     * Get variables from POST superglobal with definition's map
     * @param array $definition
     * @return mixed
     * @see http://www.php.net/manual/en/function.filter-input-array.php
     */
    public function postArray($definition = array())
    {
        return filter_input_array(INPUT_POST, $definition);
    }

    /**
     * Get variables from GET superglobal with definition's map
     * @param array $definition
     * @return mixed
     * @see http://www.php.net/manual/en/function.filter-input-array.php
     */
    public function getArray($definition = array())
    {
        return filter_input_array(INPUT_GET, $definition);
    }
}
