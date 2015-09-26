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

        // sort data to be sure that __configuration key is the first key in file
        $data = (array) $data;
        ksort($data);
        $data = (object) $data;

        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
