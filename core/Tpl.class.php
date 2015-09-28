<?php
/**
 * Template engine
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

use Exception,
    App,
    Assetic\AssetManager,
    Assetic\AssetWriter,
    Assetic\Asset\AssetCollection,
    Assetic\Asset\AssetCache,
    Assetic\Asset\FileAsset,
    Assetic\Asset\HttpAsset,
    Assetic\Asset\StringAsset,
    Assetic\Cache\FilesystemCache,
    Assetic\Filter\CssImportFilter,
    Assetic\Filter\JSMinFilter;

class Tpl
{

    /**
     * Asset collesionts array
     * @var array
     */
    private $assetCollections = [
        'css' => [
            'combined' => [],
            'single' => [],
            'bypass' => [],
            'bypass_script' => []
        ],
        'javascript' => [
            'combined' => [],
            'single' => [],
            'bypass' => [],
            'bypass_script' => []
        ]
    ];

    /**
     * Configuration
     * @var object
     */
    private $config = null;

    /**
     * Debug mode
     * @var bool
     */
    private $debug = false;

    public function __construct()
    {
        $this->config = App::Config()->templates;

        $this->assetCollections['javascript']['combined'] = new AssetCollection();
        $this->assetCollections['css']['combined'] = new AssetCollection();
    }

    /**
     * Array of <head> elements
     * @var array 
     */
    private $head = [];

    /**
     * Add css code to head
     * @param string $css
     * @param array $options
     */
    public function addCSS($css, $options = [])
    {
        $options = array_merge(['single' => false, 'filter' => true], $options);

        if ($this->debug || $options['bypass']) {
            $this->assetCollections['css']['bypass_script'][] = $css;
            return;
        }                
        
        $asset = new StringAsset($css);
        $asset->setTargetPath($this->getAssetName($css, ASSETS_CSS_NAMESPACE));

        if ($options['filter'] && $this->config->assets->minify->css) {
            $asset->ensureFilter(new CssImportFilter());
        }

        $asset = new AssetCache($asset, new FilesystemCache(DIR_DATA . 'cache/assets/css'));

        if ($options['single']) {
            $this->assetCollections['css']['single'][] = $asset;
        } else {
            $this->assetCollections['css']['combined']->add($asset);
        }
    }

    /**
     * Add js code to head
     * @param string $js
     * @param array $options
     */
    public function addJS($js, $options = [])
    {
        $options = array_merge(['single' => false, 'filter' => true], $options);

        if ($this->debug || $options['bypass']) {
            $this->assetCollections['javascript']['bypass_script'][] = $js;
            return;
        }        
        
        $asset = new StringAsset($js);
        $asset->setTargetPath($this->getAssetName($css, ASSETS_JAVASCRIPT_NAMESPACE));

        if ($options['filter'] && $this->config->assets->minify->javascript) {
            $asset->ensureFilter(new JSMinFilter());
        }

        $asset = new AssetCache($asset, new FilesystemCache(DIR_DATA . 'cache/assets/js'));

        if ($options['single']) {
            $this->assetCollections['javascript']['single'][] = $asset;
        } else {
            $this->assetCollections['javascript']['combined']->add($asset);
        }
    }

    /**
     * add js var helper
     * @param array $array
     */
    private function addJSHelper($array)
    {
        $result = "";

        if (is_array($array)) {

            $isNumericAndLinear = $this->arrayIsNumeric($array) && $this->arrayIsLinear($array);
            $result .= $isNumericAndLinear ? '[' : '{';

            $innerArray = array();

            foreach ($array as $key => $value) {
                $determinedValue = $this->addJSVarDetermine($value);
                $result .= $isNumericAndLinear ? $determinedValue : $this->addJSVarDetermine($key) . ':' . (!is_array($determinedValue) ? $determinedValue : '');

                if (is_array($determinedValue)) {
                    switch ($determinedValue['type']) {
                        case 'object':
                            $result .= $this->addJSHelper(get_object_vars($value));
                            break;
                        case 'array':
                            $result .= $this->addJSHelper($value);
                            break;
                    }
                }

                $result .= ',' . ($isNumericAndLinear ? '' : "\n");
            }

            $result = rtrim($result, ",\n");

            $result .= $isNumericAndLinear ? ']' : '}';
        } else {
            $result .= $this->addJSVarDetermine($array);
        }

        return $result;
    }

    /**
     * add javascript variable to head
     * @param string $name
     * @param mixed $variable
     * @param array $options
     */
    public function addJSVariable($name, $variable, $options = [])
    {
        $result = (strpos($name, '.') === false ? 'var ' : '') . $name . ' = ';
        $result .= $this->addJSHelper($variable) . ';';
        $this->addJS($result, $options);
    }

    /**
     * Returns correct javascript variable representation except to objects and arrays
     * @param mixed $variable
     * @return mixed
     */
    private function addJSVarDetermine($variable)
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
     * Add meta tag to the head
     * @param string $name
     * @param string $content
     */
    public function addMeta($name, $content)
    {
        $this->head['meta_' . $name] = '<meta name="' . $name . '" content="' . $content . '" />';
    }

    /**
     * Check if array keys is numeric only
     * @param array $array
     * @return boolean
     */
    private function arrayIsNumeric($array)
    {
        foreach (array_keys($array) as $a) {
            if ($a !== (int) $a) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if asset exists
     * @param string $name
     */
    private function assetExists($name)
    {
        return file_exists(DIR_ROOT . 'public/assets/' . $name);
    }

    /**
     * Check if array is linear
     * @param array $array
     * @return boolean
     */
    private function arrayIsLinear($array)
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get asset name
     * @param string $name
     * @param string $namespace
     * @return string
     * @throws Exception
     */
    private function getAssetName($name, $namespace)
    {
        $url = hash('sha256', $name);

        switch ($namespace) {
            case ASSETS_CSS_NAMESPACE:
                return App::UUID()->v5(ASSETS_CSS_NAMESPACE, $url)->result . '.css';
            case ASSETS_JAVASCRIPT_NAMESPACE:
                return App::UUID()->v5(ASSETS_JAVASCRIPT_NAMESPACE, $url)->result . '.js';
            default:
                throw Exception("No such asset namespace");
        }
    }

    /**
     * Get script tag
     * @param string $script
     * @param string $type
     */
    private function getTag($script, $type)
    {
        switch ($type) {
            case 'css':
                return '<link rel="stylesheet" type="text/css" href="' . $script . '"/>';
            case 'css_script':
                return '<style type="text/css">' . $script . '</style>';
            case 'javascript':
                return '<script type="text/javascript" src="' . $script . '"></script>';
            case 'javascript_script':
                return '<script type="text/javascript">' . $script . '</script>';
            default:
                throw new Exception('No such script tag');
        }
    }

    /**
     * Return head contents
     * @return string
     */
    public function head()
    {
        foreach ($this->assetCollections['javascript']['bypass'] as $asset) {
            $this->head[] = $this->getTag($asset, 'javascript');
        }

        foreach ($this->assetCollections['css']['bypass'] as $asset) {
            $this->head[] = $this->getTag($asset, 'css');
        }

        $assetManager = new AssetManager();

        $nameHash = '';
        foreach ($this->assetCollections['javascript']['combined']->all() as $asset) {
            $nameHash .= $asset->getTargetPath();
        }
        
        $this->assetCollections['javascript']['combined']->setTargetPath($this->getAssetName($nameHash, ASSETS_JAVASCRIPT_NAMESPACE));
        $this->head[] = $this->getTag('/public/assets/' . $this->assetCollections['javascript']['combined']->getTargetPath(), 'javascript');

        if (!$this->assetExists($this->assetCollections['javascript']['combined']->getTargetPath())) {
            $assetManager->set('javascript', $this->assetCollections['javascript']['combined']);
        }

        foreach ($this->assetCollections['javascript']['single'] as $index => $asset) {
            if (!$this->assetExists($asset->getTargetPath())) {
                $assetManager->set('javascript_single_' . $index, $asset);
            }
            $this->head[] = $this->getTag('/public/assets/' . $asset->getTargetPath(), 'javascript');
        }

        $nameHash = '';
        foreach ($this->assetCollections['css']['combined']->all() as $asset) {
            $nameHash .= $asset->getTargetPath();
        }

        $this->assetCollections['css']['combined']->setTargetPath($this->getAssetName($nameHash, ASSETS_CSS_NAMESPACE));
        $this->head[] = $this->getTag('/public/assets/' . $this->assetCollections['css']['combined']->getTargetPath(), 'css');

        if (!$this->assetExists($this->assetCollections['css']['combined']->getTargetPath())) {
            $assetManager->set('css', $this->assetCollections['css']['combined']);
        }

        foreach ($this->assetCollections['css']['single'] as $index => $asset) {
            if (!$this->assetExists($asset->getTargetPath())) {
                $assetManager->set('css_single_' . $index, $asset);
            }
            $this->head[] = $this->getTag('/public/assets/' . $asset->getTargetPath(), 'css');
        }

        $assetWriter = new AssetWriter('public/assets');
        $assetWriter->writeManagerAssets($assetManager);

        foreach ($this->assetCollections['javascript']['bypass_script'] as $asset) {            
            $this->head[] = $this->getTag($asset, 'javascript_script');
        }

        foreach ($this->assetCollections['css']['bypass_script'] as $asset) {
            $this->head[] = $this->getTag($asset, 'css_script');
        }        
        
        $head = implode("\n", $this->head);
                
        return $head;
    }

    /**
     * Add CSS file to template head
     * @param string $css
     * @param array $options
     */
    public function includeCSS($css, $options = [])
    {
        $options = array_merge(['single' => false, 'filter' => true, 'bypass' => false], $options);

        if ($this->debug || $options['bypass'] || strpos($css, 'http') !== false) {
            $this->assetCollections['css']['bypass'][] = $css;
            return;
        }

        $asset = new FileAsset(DIR_ROOT . $css);
        $asset->setTargetPath($this->getAssetName($css . $asset->getLastModified(), ASSETS_CSS_NAMESPACE));

        if ($options['filter'] && $this->config->assets->minify->css) {
            $asset->ensureFilter(new CssImportFilter());
        }

        if ($options['single']) {
            $this->assetCollections['css']['single'][] = $asset;
        } else {
            $this->assetCollections['css']['combined']->add($asset);
        }
    }

    /**
     * Add javascript file to template head
     * @param string $js
     * @param array $options
     */
    public function includeJS($js, $options = [])
    {
        $options = array_merge(['single' => false, 'filter' => true, 'bypass' => false], $options);

        if ($this->debug || $options['bypass'] || strpos($js, 'http') !== false) {
            $this->assetCollections['javascript']['bypass'][] = $js;
            return;
        }

        $asset = new FileAsset(DIR_ROOT . $js);
        $asset->setTargetPath($this->getAssetName($js . $asset->getLastModified(), ASSETS_JAVASCRIPT_NAMESPACE));

        if ($options['filter'] && $this->config->assets->minify->javascript) {
            $asset->ensureFilter(new JSMinFilter());
        }

        if ($options['single']) {
            $this->assetCollections['javascript']['single'][] = $asset;
        } else {
            $this->assetCollections['javascript']['combined']->add($asset);
        }
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
     * Set debug mode
     * @param bool $mode
     */
    public function setDebug($mode)
    {
        $this->debug = $mode;
    }

    /**
     * Set title
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->head['title'] = '<title> ' . $title . '</title>';
    }
}
