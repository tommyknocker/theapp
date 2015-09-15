<?php
/**
 * Cache class
 *
 * @author Tommyknocker <tommyknocker@theapp.pro>
 * @license http://www.gnu.org/licenses/lgpl.txt LGPLv3
 */
namespace core;
use App;

class Cache
{

    use \TCallable;

    /**
     * Cache Handler
     */
    private $cacheHandler = null;

    public function __construct()
    {

        App::Autoload()->addNameSpace('cache')->addPath(DIR_ROOT . 'classes' . DS . 'cache')->addExt('.class.php')->register();
        App::Autoload()->addNameSpace('cache')->addPath(DIR_ROOT . 'interfaces' . DS . 'cache')->addExt('.interface.php')->register();

        switch (App::Config()->cache_type) {
            case 'redis':
                $this->cacheHandler = new \cache\Redis();
                break;
            case 'memcached':
                $this->cacheHandler = new \cache\Memcached();
                break;
            default:
                $this->cacheHandler = new \cache\Session();
                break;
        }
    }

    public function __call($method, $arguments)
    {
        if (!is_object($this->cacheHandler)) {
            return false;
        }

        return call_user_func_array([$this->cacheHandler, $method], $arguments);
    }
}
