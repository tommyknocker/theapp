<?php
/**
 * HTTP Helpers
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace App\Core;

class HTTP
{

    /**
     * HTTP Status codes
     * @var array 
     */    
    private $statusCodes = [
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        304 => 'HTTP/1.1 304 Not Modified',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed'
    ];
    
    /**
     * Content type
     * @param string $type
     */
    public function contentType($type) {
        header('Content-Type: ' . $type);
    }
    
    /**
     * Perform redirection
     * @param string $location
     * @param bool $isSoft
     */
    public function redirect($location, $isSoft = false) {
        $this->status($isSoft ? 302 : 301);
        header('Location: ' . $location);
        die;
    }    
    
    /**
     * Send status code header
     * @param int $code
     */
    public function status($code)
    {        
        if(isset($this->statusCodes[$code])) {
            header($this->statusCodes[$code]);
        } else {
            App::Log()->addWarning('No defined status code header for code {code}', ['code' => $code]);
        }
  
    }
            
}
