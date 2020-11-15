<?php
declare(strict_types=1);

namespace Kafka\Sasl;

use Kafka\CommonSocket;
use Kafka\Exception;
use Kafka\Protocol;
use Kafka\Protocol\Protocol as ProtocolTool;
use Kafka\SaslMechanism;
use function substr;

abstract class Mechanism implements SaslMechanism
{
    public function authenticate(CommonSocket $socket): void
    {
        $this->handShake($socket, $this->getName());
        $this->performAuthentication($socket);
    }

    /**
     *
     * sasl authenticate hand shake
     *
     * @access protected
     */
    protected function handShake(CommonSocket $socket, string $mechanism): void
    {
        $requestData = Protocol::encode(Protocol::SASL_HAND_SHAKE_REQUEST, [$mechanism]);
        $socket->writeBlocking($requestData);
        $dataLen = ProtocolTool::unpack(\Kafka\Protocol\Protocol::BIT_B32, $socket->readBlocking(4));

        $data          = $socket->readBlocking($dataLen);
        $correlationId = ProtocolTool::unpack(\Kafka\Protocol\Protocol::BIT_B32, substr($data, 0, 4));
        $result        = Protocol::decode(Protocol::SASL_HAND_SHAKE_REQUEST, substr($data, 4));

        if ($result['errorCode'] !== Protocol::NO_ERROR) {
            throw new Exception(Protocol::getError($result['errorCode']));
        }
    }

    abstract protected function performAuthentication(CommonSocket $socket): void;
    abstract public function getName(): string;
}
