<?php
/**
 * MsgPack
 *
 * @author camera360_server@camera360.com
 * @copyright Chengdu pinguo Technology Co.,Ltd.
 */

namespace PG\MSF\Pack;

/**
 * Class MsgPack
 * @package PG\MSF\Pack
 */
class MsgPack implements IPack
{
    /**
     * msgpack打包
     *
     * @param mixed $data 待打包数据
     * @return mixed
     */
    public function pack($data)
    {
        return msgpack_pack($data);
    }

    /**
     * msgpack解包
     *
     * @param string $data 待解包数据
     * @return mixed
     */
    public function unPack($data)
    {
        return msgpack_unpack($data);
    }
}
