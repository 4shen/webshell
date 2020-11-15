<?php
/**
 * SerializePack
 *
 * @author camera360_server@camera360.com
 * @copyright Chengdu pinguo Technology Co.,Ltd.
 */

namespace PG\MSF\Pack;

/**
 * Class SerializePack
 * @package PG\MSF\Pack
 */
class SerializePack implements IPack
{
    /**
     * serialize打包
     *
     * @param mixed $data 待打包数据
     * @return string
     */
    public function pack($data)
    {
        return serialize($data);
    }

    /**
     * serialize解包
     *
     * @param mixed $data 待解包数据
     * @return mixed
     */
    public function unPack($data)
    {
        return unserialize($data);
    }
}
