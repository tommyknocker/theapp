<?php

namespace core;

/**
 * Template engine
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
class Tpl {
    
    
    /**
     * Add javascript file to template head
     * @param string $js
     */
    public function addJS($js) {
        echo '<script type="text/javascript" src="' . $js . '"></script>';
    }
    
    /**
     * Add CSS file to template head
     * @param string $css
     */
    public function addCSS($css) {
        echo '<link rel="stylesheet" href="' . $css . '" />';
    }
    
    /**
     * Loads template
     * @param string $template
     * @throws \Exception
     */
    public function load($template, $data = []) {
        
        $filePath = DIR_TEMPLATES . $template . '.php';
        
        if(!file_exists($filePath)) {
            throw new \Exception('No such template: ' . $template);
        }
        
        if(!is_readable($filePath)) {
            throw new \Exception('Template is not readable ' . $template);
        }
        
        require $filePath;
    }
        
    /**
     * Loads template and return its content
     * @param string $template
     * @return string
     * @throws \Exception
     */
    public function preLoad($template, $data = []) {
        ob_start();
        $this->load($template, $data);        
        return ob_get_clean();
    }
}
