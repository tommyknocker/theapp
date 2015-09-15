<?php
/**
 * Configuration class
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;
use App, Exception;

class Config
{

    /**
     * Configuration file
     * @var string
     */
    private $configFile = "";

    public function __construct($configFile)
    {
        $this->configFile = $configFile;
        $this->read();
    }

    /**
     * Explodes key if dot is spotted and returns array of key path
     * @param string $key
     * @return array
     */
    private function formatKey($key)
    {

        if (strpos($key, '.') !== false) {
            $key = explode('.', $key);
        } else {
            $key = [$key];
        }

        return $key;
    }

    /**
     * Smart reciever value(s) from config storage
     * @param string $name
     * @return mixed  null on error
     */
    public function __get($name)
    {
        $key = $this->formatKey(str_replace('_', '.', $name));

        $config = App::Container()->config;

        foreach ($key as $keyPart) {
            if (!is_object($config)) {
                return null;
            }

            if (property_exists($config, $keyPart)) {
                $config = &$config->{$keyPart};
            } else {
                return null;
            }
        }

        return $config;
    }

    /**
     * Smart setter for config storage
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $key = $this->formatKey(str_replace('_', '.', $name));

        $config = App::Container()->config;
        $configPart = &$config;

        foreach ($key as $keyPart) {
            if (!is_object($configPart)) {
                $configPart = new \stdClass();
            }

            $configPart = &$configPart->{$keyPart};
        }

        $configPart = $value;

        App::Container()->config = (object) array_replace_recursive((array) App::Container()->config, (array) $config);
    }

    /**
     * Read config file and store it in Container
     * @throws Exception
     * @return mixed
     */
    public function read()
    {
        if (!file_exists($this->configFile) || !is_readable($this->configFile)) {
            throw new Exception('Config file ' . $this->configFile . ' does not exist or not readable');
        }

        $data = json_decode(file_get_contents($this->configFile));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Bad config file. Error code: ' . json_last_error());
        }

        App::Container()->config = $data;
    }

    /**
     * Write config to file
     * @return mixed Number of bytes written of false
     */
    public function write()
    {
        if (!is_writable($this->configFile)) {
            throw new Exception('Config file ' . $configFile . ' is not writable');
        }

        $config = App::Container()->config;

        return file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT));
    }
}
