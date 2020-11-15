<?php
/**
 * MSF Console Request
 *
 * @author camera360_server@camera360.com
 * @copyright Chengdu pinguo Technology Co.,Ltd.
 */

namespace PG\MSF\Console;

/**
 * Class Request
 * @package PG\MSF\Console
 */
class Request
{
    /**
     * @var array 运行环境参数
     */
    public $server;

    /**
     * @var array 用于兼容和Web一样的方式获取参数
     */
    public $get;

    /**
     * @var array 用于兼容和Web一样的方式获取参数
     */
    public $post;

    /**
     * @var array 用于兼容和Web一样的方式获取参数
     */
    public $header;

    /**
     * 获取服务器相关变量（兼容Web模式）
     *
     * @return array|null
     */
    public function getServer()
    {
        if ($this->server === null) {
            if (isset($_SERVER['argv'])) {
                $this->server = $_SERVER['argv'];
                array_shift($this->server);
            } else {
                $this->server = [];
            }
        }

        return $this->server;
    }

    /**
     * 设置服务器相关变量（兼容Web模式）
     *
     * @param array $params 参数列表
     * @return $this
     */
    public function setServer($params)
    {
        $this->server = $params;
        return $this;
    }

    /**
     * 解析命令行参数
     *
     * @return array
     */
    public function resolve()
    {
        $rawParams = $this->getServer();
        if (isset($rawParams[0])) {
            $route = $rawParams[0];
            array_shift($rawParams);
        } else {
            $route = '';
        }

        $params = [];
        foreach ($rawParams as $param) {
            if (preg_match('/^--(\w+)(?:=(.*))?$/', $param, $matches) || preg_match('/^-(\w+)(?:=(.*))?$/', $param, $matches)) {
                $name = $matches[1];
                $params[$name] = isset($matches[2]) ? $matches[2] : true;
            } else {
                $params[] = $param;
            }
        }

        $this->server['path_info'] = $route;
        $this->get                 = $params;
        $this->post                = $params;

        return [$route, $params];
    }
}
