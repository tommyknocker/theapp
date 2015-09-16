<?php
/**
 * VALID RFC 4211 COMPLIANT Universally Unique IDentifiers (UUID) version 3, 4 and 5
 * @author Andrew Moore
 * @author Tommyknocker
 */
namespace App\Core;

class UUID {
  
    /**
     * Generates version 3 UUID: MD5 hash of URL
     * @param string $namespace
     * @param string $url
     * @return string|false
     */     
    public function v3($namespace, $url) {        
        
        if(!$this->isValid($namespace)) {
            return false;
        }

        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }

        // Calculate hash value
        $hash = md5($nstr . $url);

        return sprintf('%08s-%04s-%04x-%04x-%12s',

            // 32 bits for "time_low"
            substr($hash, 0, 8),

            // 16 bits for "time_mid"
            substr($hash, 8, 4),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 3
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }

    /**
     * Generates version 4 UUID: random
     * @return string
     */
    public function v4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );        
    }

    /**
     * Generates version 5 UUID: SHA-1 hash of URL
     * @param string $namespace
     * @param string $url
     * @return string|false
     */
    public function v5($namespace, $url) {
        
        if(!$this->isValid($namespace)) {
            return false;
        }        
        
        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-','{','}'), '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for($i = 0; $i < strlen($nhex); $i+=2) {
            $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
        }

        // Calculate hash value
        $hash = sha1($nstr . $url);

        return sprintf('%08s-%04s-%04x-%04x-%12s',
            // 32 bits for "time_low"
            substr($hash, 0, 8),

            // 16 bits for "time_mid"
            substr($hash, 8, 4),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 5
            (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

            // 48 bits for "node"
            substr($hash, 20, 12)
        );
    }
  
    /**
     * Pack UUID
     * @param string $uuid 
     * @return mixed
     */
    public function pack($uuid) {
        return $this->isValid($uuid) ? pack("h*", str_replace('-', '', $uuid)) : false;        
    }

    /**
     * Format to SQL insert
     * @param type $uuid
     * @return type
     */
    public function packSQL($uuid) {
        return $this->isValid($uuid) ? '0x' . bin2hex(self::pack($uuid))  : false;
    }
    
    /**
     * Unpack UUID
     * @param string $uuid
     * @return mixed
     */
    public function unpack($uuid) {             
        $uuid = unpack("h*", $uuid);                
        $uuid = preg_replace("/([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12})/", "$1-$2-$3-$4-$5", $uuid);
        return $this->isValid($uuid[1]) ? $uuid[1] : false;
    }
    
    
    /**
     * Check UUID for validness
     * @param string $uuid
     * @return bool
     */
    public function isValid($uuid) {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }
}