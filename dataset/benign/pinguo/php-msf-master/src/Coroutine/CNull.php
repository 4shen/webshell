<?php
/**
 * 协程NULL结果
 *
 * @author camera360_server@camera360.com
 * @copyright Chengdu pinguo Technology Co.,Ltd.
 */

namespace PG\MSF\Coroutine;

/**
 * Class CNull
 * @package PG\MSF\Coroutine
 */
class CNull
{
    /**
     * @var CNull 单例
     */
    private static $instance;

    /**
     * CNull constructor.
     */
    public function __construct()
    {
        self::$instance = &$this;
    }

    /**
     * 获取NULL实例
     *
     * @return CNull
     */
    public static function &getInstance()
    {
        if (self::$instance == null) {
            new CNull();
        }
        return self::$instance;
    }
}
