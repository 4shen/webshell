<?php

/**
 * Button module.
 *
 * This file is part of MadelineProto.
 * MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU General Public License along with MadelineProto.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPLv3
 *
 * @link https://docs.madelineproto.xyz MadelineProto documentation
 */

namespace danog\MadelineProto\TL\Types;

use danog\MadelineProto\MTProto;
use danog\MadelineProto\Tools;

class Button implements \JsonSerializable, \ArrayAccess
{
    /**
     * Button data.
     */
    private array $button;
    /**
     * MTProto instance.
     */
    private MTProto $API;
    /**
     * Message ID.
     */
    private int $id;
    /**
     * Peer ID.
     *
     * @var array|int
     */
    private $peer;
    /**
     * Constructor function.
     *
     * @param MTProto $API     API instance
     * @param array   $message Message
     * @param array   $button  Button info
     */
    public function __construct(MTProto $API, array $message, array $button)
    {
        $this->button = $button;
        $this->peer = $message['to_id'] === ['_' => 'peerUser', 'user_id' => $API->authorization['user']['id']] ? $message['from_id'] : $message['to_id'];
        $this->id = $message['id'];
        $this->API = $API;
    }
    /**
     * Sleep function.
     *
     * @return array
     */
    public function __sleep(): array
    {
        return ['button', 'peer', 'id', 'API'];
    }
    /**
     * Click on button.
     *
     * @param boolean $donotwait Whether to wait for the result of the method
     *
     * @return mixed
     */
    public function click(bool $donotwait = true)
    {
        $async = isset($this->API->wrapper) ? $this->API->wrapper->isAsync() : true;
        $method = $donotwait ? 'methodCallAsyncWrite' : 'methodCallAsyncRead';
        switch ($this->button['_']) {
            default:
                return false;
            case 'keyboardButtonUrl':
                return $this->button['url'];
            case 'keyboardButton':
                $res = $this->API->methodCallAsyncRead('messages.sendMessage', ['peer' => $this->peer, 'message' => $this->button['text'], 'reply_to_msg_id' => $this->id], ['datacenter' => $this->API->datacenter->curdc]);
                break;
            case 'keyboardButtonCallback':
                $res = $this->API->{$method}('messages.getBotCallbackAnswer', ['peer' => $this->peer, 'msg_id' => $this->id, 'data' => $this->button['data']], ['datacenter' => $this->API->datacenter->curdc]);
                break;
            case 'keyboardButtonGame':
                $res = $this->API->{$method}('messages.getBotCallbackAnswer', ['peer' => $this->peer, 'msg_id' => $this->id, 'game' => true], ['datacenter' => $this->API->datacenter->curdc]);
                break;
        }
        return $async ? $res : Tools::wait($res);
    }
    /**
     * Get debug info.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        $res = \get_object_vars($this);
        unset($res['API']);
        return $res;
    }
    /**
     * Serialize button.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->button;
    }
    /**
     * Set button info.
     *
     * @param $name  Offset
     * @param mixed  $value Value
     *
     * @return void
     */
    public function offsetSet($name, $value): void
    {
        if ($name === null) {
            $this->button[] = $value;
        } else {
            $this->button[$name] = $value;
        }
    }
    /**
     * Get button info.
     *
     * @param $name Field name
     *
     * @return void
     */
    public function offsetGet($name)
    {
        return $this->button[$name];
    }
    /**
     * Unset button info.
     *
     * @param $name Offset
     *
     * @return void
     */
    public function offsetUnset($name): void
    {
        unset($this->button[$name]);
    }
    /**
     * Check if button field exists.
     *
     * @param $name Offset
     *
     * @return boolean
     */
    public function offsetExists($name): bool
    {
        return isset($this->button[$name]);
    }
}
