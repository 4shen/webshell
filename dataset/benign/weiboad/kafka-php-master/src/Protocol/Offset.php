<?php
declare(strict_types=1);

namespace Kafka\Protocol;

use Kafka\Exception\Protocol as ProtocolException;
use function substr;

class Offset extends Protocol
{
    /**
     * @param mixed[] $payloads
     */
    public function encode(array $payloads = []): string
    {
        if (! isset($payloads['data'])) {
            throw new ProtocolException('given offset data invalid. `data` is undefined.');
        }

        if (! isset($payloads['replica_id'])) {
            $payloads['replica_id'] = -1;
        }

        $header = $this->requestHeader('kafka-php', self::OFFSET_REQUEST, self::OFFSET_REQUEST);
        $data   = self::pack(self::BIT_B32, (string) $payloads['replica_id']);
        $data  .= self::encodeArray($payloads['data'], [$this, 'encodeOffsetTopic']);
        $data   = self::encodeString($header . $data, self::PACK_INT32);

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function decode(string $data): array
    {
        $offset = 0;

        $version = $this->getApiVersion(self::OFFSET_REQUEST);
        $topics  = $this->decodeArray(substr($data, $offset), [$this, 'offsetTopic'], $version);
        $offset += $topics['length'];

        return $topics['data'];
    }

    /**
     * @param mixed[] $values
     */
    protected function encodeOffsetPartition(array $values): string
    {
        if (! isset($values['partition_id'])) {
            throw new ProtocolException('given offset data invalid. `partition_id` is undefined.');
        }

        if (! isset($values['time'])) {
            $values['time'] = -1; // -1
        }

        if (! isset($values['max_offset'])) {
            $values['max_offset'] = 100000;
        }

        $data  = self::pack(self::BIT_B32, (string) $values['partition_id']);
        $data .= self::pack(self::BIT_B64, (string) $values['time']);

        if ($this->getApiVersion(self::OFFSET_REQUEST) === self::API_VERSION0) {
            $data .= self::pack(self::BIT_B32, (string) $values['max_offset']);
        }

        return $data;
    }

    /**
     * @param mixed[] $values
     */
    protected function encodeOffsetTopic(array $values): string
    {
        if (! isset($values['topic_name'])) {
            throw new ProtocolException('given offset data invalid. `topic_name` is undefined.');
        }

        if (! isset($values['partitions']) || empty($values['partitions'])) {
            throw new ProtocolException('given offset data invalid. `partitions` is undefined.');
        }

        $topic      = self::encodeString($values['topic_name'], self::PACK_INT16);
        $partitions = self::encodeArray($values['partitions'], [$this, 'encodeOffsetPartition']);

        return $topic . $partitions;
    }

    /**
     * @return mixed[]
     */
    protected function offsetTopic(string $data, int $version): array
    {
        $offset    = 0;
        $topicInfo = $this->decodeString(substr($data, $offset), self::BIT_B16);
        $offset   += $topicInfo['length'];

        $partitions = $this->decodeArray(substr($data, $offset), [$this, 'offsetPartition'], $version);
        $offset    += $partitions['length'];

        return [
            'length' => $offset,
            'data'   => [
                'topicName'  => $topicInfo['data'],
                'partitions' => $partitions['data'],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    protected function offsetPartition(string $data, int $version): array
    {
        $offset      = 0;
        $partitionId = self::unpack(self::BIT_B32, substr($data, $offset, 4));
        $offset     += 4;
        $errorCode   = self::unpack(self::BIT_B16_SIGNED, substr($data, $offset, 2));
        $offset     += 2;
        $timestamp   = 0;

        if ($version !== self::API_VERSION0) {
            $timestamp = self::unpack(self::BIT_B64, substr($data, $offset, 8));
            $offset   += 8;
        }

        $offsets = $this->decodePrimitiveArray(substr($data, $offset), self::BIT_B64);
        $offset += $offsets['length'];

        return [
            'length' => $offset,
            'data'   => [
                'partition' => $partitionId,
                'errorCode' => $errorCode,
                'timestamp' => $timestamp,
                'offsets'   => $offsets['data'],
            ],
        ];
    }
}
