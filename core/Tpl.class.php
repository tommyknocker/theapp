<?php
/**
 * Template engine
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use Exception;

class Tpl
{

    /**
     * Array of <head> elements
     * @var array 
     */
    private $head = [];

    /**
     * Add css code to head
     * @param string $js
     */
    public function addCSS($js)
    {
        $this->head[] = '<style type="text/css">' . $js . '</style>';
    }

    /**
     * Add js code to head
     * @param string $js
     */
    public function addJS($js)
    {
        $this->head[] = '<script type="text/javascript">' . $js . '</script>';
    }

    /**
     * add javascript variable to head
     * @param string $name
     * @param mixed $variable
     */
    public function addJsVariable($name, $variable)
    {
        $result = (strpos($name, '.') === false ? 'var ' : '') . $name . ' = ';
        $result .= $this->addJsHelper($variable) . ';';
        $this->addJS($result);
    }

    /**
     * Returns correct javascript variable representation except to objects and arrays
     * @param mixed $variable
     * @return mixed
     */
    private function addJsVarDetermine($variable)
    {
        switch (gettype($variable)) {
            case 'string':
                return '"' . $variable . '"';
            case 'boolean':
                return $variable ? 'true' : 'false';
            case 'integer':
            case 'double':
                return $variable;
            case 'NULL':
                return 'null';
            case 'array':
                return array('type' => 'array');
            case 'object':
                return array('type' => 'object');
        }

        return false;
    }

    /**
     * add js var helper
     * @param array $array
     */
    private function addJsHelper($array)
    {
        $result = "";

        if (is_array($array)) {

            $isNumericAndLinear = $this->arrayIsNumeric($array) && $this->arrayIsLinear($array);
            $result .= $isNumericAndLinear ? '[' : '{';

            $innerArray = array();

            foreach ($array as $key => $value) {
                $determinedValue = $this->addJsVarDetermine($value);
                $result .= $isNumericAndLinear ? $determinedValue : $this->addJsVarDetermine($key) . ':' . (!is_array($determinedValue) ? $determinedValue : '');

                if (is_array($determinedValue)) {
                    switch ($determinedValue['type']) {
                        case 'object':
                            $result .= $this->addJsHelper(get_object_vars($value));
                            break;
                        case 'array':
                            $result .= $this->addJsHelper($value);
                            break;
                    }
                }

                $result .= ',' . ($isNumericAndLinear ? '' : "\n");
            }

            $result = rtrim($result, ",\n");

            $result .= $isNumericAndLinear ? ']' : '}';
        } else {
            $result .= $this->addJsVarDetermine($array);
        }

        return $result;
    }

    /**
     * Add meta tag to the head
     * @param string $name
     * @param string $content
     */
    public function addMeta($name, $content)
    {
        $this->head['meta_' . $name] = '<meta name="' . $name . '" content="' . $content . '" />';
    }

    /**
     * Return head contents
     * @param boolean $flush
     * @return string
     */
    public function head($flush = true)
    {
        $head = implode("\n", $this->head);
        if ($flush) {
            $this->head = [];
        }
        return $head;
    }

    /**
     * Add javascript file to template head
     * @param string $js
     * @param string $name
     */
    public function includeJS($js, $name = "")
    {
        $this->head[$name ? 'js_' . $name : count($this->head)] = '<script type="text/javascript" src="' . $js . '"></script>';
    }

    /**
     * Add CSS file to template head
     * @param string $css
     */
    public function includeCSS($css, $name = "")
    {
        $this->head[$name ? 'css_' . $name : count($this->head)] = '<link rel="stylesheet" href="' . $css . '" />';
    }
    
    /**
     * Check if array keys is numeric only
     * @param array $array
     * @return boolean
     */
    private function arrayIsNumeric($array) {
        foreach (array_keys($array) as $a) {
            if ($a !== (int) $a) {
                return false;
            } 
        }
        return true;
    }
    
    /**
     * Check if array is linear
     * @param array $array
     * @return boolean
     */
    private function arrayIsLinear($array) {
        foreach($array as $value) {
            if(is_array($value)) {
                return false;
            }
        }
        return true;
    }    

    /**
     * Loads template
     * @param string $template
     * @throws Exception
     */
    public function load($template, $data = [])
    {

        $filePath = DIR_TEMPLATES . $template . '.php';

        if (!file_exists($filePath)) {
            throw new Exception('No such template: ' . $template);
        }

        if (!is_readable($filePath)) {
            throw new Exception('Template is not readable ' . $template);
        }

        require $filePath;
    }

    /**
     * Loads template and return its content
     * @param string $template
     * @return string
     * @throws Exception
     */
    public function preLoad($template, $data = [])
    {
        ob_start();
        $this->load($template, $data);
        return ob_get_clean();
    }
    
    /**
     * Set title
     * @param string $title
     */
    public function setTitle($title) {
        $this->head['title'] = '<title> ' . $title . '</title>';
    }
}
