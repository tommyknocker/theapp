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
        $filePath = DIR_DATA . $fileName;

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
        $filePath = DIR_DATA . $fileName;

        if (!is_writable($filePath)) {
            throw new \Exception('Provied path ' . $filePath . ' is not writable');
        }

        return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
