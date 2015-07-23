<?php

namespace core;

/**
 * Configuration class
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
class Config {
  
    public function __construct($mainConfigFile) {
        $parsedData = $this->parse($mainConfigFile);
        
        if(!array_key_exists('default', $parsedData)) {
            throw new \Exception($mainConfigFile . ' must contain default section.');
        }
               
        $preparedServerName = str_replace('.', '_', filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING));

        $config = $parsedData['default'];
        
        if($preparedServerName && array_key_exists($preparedServerName, $parsedData)) {
            $config = array_replace_recursive($config, $parsedData[$preparedServerName]);
        }
        
        \App::Container()->config = $config;
    }
    
    /**
     * Read config file and store it in Container
     * @param string $configFile          Path to a config file
     * @param string|false $configStorage If string, parsed array will be added to Container
     *                                    config storage. If false, it will be returned
     * @throws Exception
     * @return array|true
     */
    public function parse($configFile, $configStorage = false) {
        
        if(!file_exists($configFile) || !is_readable($configFile)) {
            throw new \Exception('Config file ' . $configFile . ' does not exist or not readable');
        }
       
        
        $data = parse_ini_file($configFile, true, false);
        
        if(!is_array($data)) {
            throw new \Exception('Bad config file ' . $configFile);
        }
        
        if($configStorage) {
           
            \App::Container()->config = array_replace_recursive(\App::Container()->config, 
                                                               $this->parseHelper([$configStorage => $data])
                                                              );
            return true;
        } else {
            return $this->parseHelper($data);
        }
    }   
    
    /**
     * Config parse helper function. It breaks keys that contains dots into arrays
     * @param array $data Data recieved through parse_ini_file function
     * @return array Parsed data
     */
    private function parseHelper($data) {
        $return = [];
        
        foreach($data as $key => $value) {

            $currentPart = &$return; 
            $currentKey = $this->formatKey($key);
            
            foreach($currentKey as $keyPart) {
               
               if(!array_key_exists($keyPart, $currentPart)) {
                   $currentPart[$keyPart] = [];
               } else if(!is_array($currentPart[$keyPart])) {
                   throw new Exception('Something went wrong, key already exists and not an array - ' . $keyPart);
               }
               
               $currentPart = &$currentPart[$keyPart];
            }            
            
            $currentPart = is_array($value) ? $this->parseHelper($value) : $value;
        }
        
        return $return;
    }
    
    /**
     * Explodes key if dot is spotted and returns array of key path
     * @param string $key
     * @return array
     */
    private function formatKey($key) {
 
        if(strpos($key, '.') !== false) {
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
    public function __get($name) {
        $key = $this->formatKey(str_replace('_', '.', $name));
                
        $config = \App::Container()->config;
  
        foreach($key as $keyPart) {
            if(array_key_exists($keyPart, $config)) {
                $config = &$config[$keyPart];
            } else {
                return null;
            }
        }
        
        return $config;
    }
}