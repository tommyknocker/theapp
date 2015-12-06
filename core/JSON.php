<?php
/**
 * Some useful json methods
 * 
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

class JSON
{

    /**
     * Read json file and return it contents. 
     * Uses DIR_DATA as root dir.
     * 
     * @param string $fileName
     * @throws Exception
     * @return object
     */
    public function read($fileName)
    {
        $filePath = DIR_DATA . $fileName . '.json.php';

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception('File ' . $filePath . ' does not exist or not readable');
        }

        $data = json_decode(file_get_contents($filePath));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Bad json file. Error code: ' . json_last_error());
        }

        return $data;
    }
    
    /**
     * Sorting helper
     * @param object $data
     * @return object
     */
    private function sort($data) {
        
        $data = (array) $data;
        
        uksort($data, function($a, $b) {
            $a = str_replace('_', '0', $a);
            $b = str_replace('_', '0', $b);
            return strcasecmp($a, $b);
        });
        
        foreach($data as $index => $elem) {
            if(is_object($elem)) {
                $data[$index] = $this->sort($elem);
            }
        }
        
        return (object) $data;
    }

    /**
     * Write data to json file. 
     * Uses DIR_DATA as root dir.
     * 
     * @param string $fileName
     * @param mixed $data
     * @return mixed Number of bytes written of false
     */
    public function write($fileName, $data)
    {
        $filePath = DIR_DATA . $fileName . '.json.php';

        if (!is_object($data)) {
            $data = (object) $data;
        }

        if (!isset($data->__configuration) || !isset($data->__configuration->read)) {
            if (!is_object($data->__configuration)) {
                $data->__configuration = new \stdClass();
            }
            $data->__configuration->read = "protected<?php die(); ?>";
        }

        // sort data before saving
        $data = $this->sort($data);

        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
