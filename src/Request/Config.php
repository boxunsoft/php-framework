<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2018/12/27
 * Time: 下午4:08
 */

namespace Alf\Request;

use Alf\Exception\WarningException;
use Ali\InstanceTrait;

/**
 * Class Config
 * @package All
 * @example default.php
 *      return [
 *          'master' => [
 *              'host' => '127.0.0.1'
 *          ]
 *      ];
 */
class Config
{
    use InstanceTrait;

    private $path;
    private $data;

    public function __construct()
    {
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return mixed
     * @throws WarningException
     */
    public function getPath()
    {
        if (!$this->path) {
            throw new WarningException('Config path is not configured');
        }
        return $this->path;
    }

    /**
     * @param string $key example: db/default.master.host
     * @return array|mixed|null
     * @throws \Exception
     */
    public function get($key)
    {
        list($file, $keys) = $this->parseKey($key);
        $data = $this->getData($file);
        if (!$keys || !$data) {
            return $data;
        }
        foreach ($keys as $key) {
            if (!is_array($data) || !isset($data[$key])) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

    /**
     * @param $file
     * @return array|mixed|null
     * @throws WarningException
     */
    private function getData($file)
    {
        if (isset($this->data[$file])) {
            return $this->data[$file];
        }
        $filePath = $this->getPath() . DIRECTORY_SEPARATOR . $file . '.php';
        if (!is_file($filePath) || !is_readable($filePath)) {
            return null;
        }
        $data = include $filePath;
        $data = $data && is_array($data) ? $data : [];
        return $this->data[$file] = $data;
    }

    private function parseKey($key)
    {
        $keys = explode('.', $key);
        $file = trim(array_shift($keys), '/');
        return [$file, $keys];
    }
}