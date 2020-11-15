<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\IntegrationsBundle\Sync\DAO\Sync\Request;

class ObjectDAO
{
    /**
     * @var string
     */
    private $object;

    /**
     * Date/time based on last synced date for the object or the start date/time fed through the command's arguments.
     * This value does not change between iterations.
     *
     * @var \DateTimeInterface|null
     */
    private $fromDateTime;

    /**
     * Date/Time the sync started.
     *
     * @var \DateTimeInterface|null
     */
    private $toDateTime;

    /**
     * @var \DateTimeInterface|null
     */
    private $objectLastSyncDateTime;

    /**
     * @var string[]
     */
    private $fields = [];

    /**
     * @var string[]
     */
    private $requiredFields = [];

    public function __construct(
        string $object,
        ?\DateTimeInterface $fromDateTime = null,
        ?\DateTimeInterface $toDateTime = null,
        ?\DateTimeInterface $objectLastSyncDateTime = null
    ) {
        $this->object                 = $object;
        $this->fromDateTime           = $fromDateTime;
        $this->toDateTime             = $toDateTime;
        $this->objectLastSyncDateTime = $objectLastSyncDateTime;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    /**
     * @return self
     */
    public function addField(string $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function setRequiredFields(array $fields): void
    {
        $this->requiredFields = $fields;
    }

    /**
     * @return string[]
     */
    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    public function getFromDateTime(): ?\DateTimeInterface
    {
        return $this->fromDateTime;
    }

    public function getToDateTime(): ?\DateTimeInterface
    {
        return $this->toDateTime;
    }

    public function getObjectLastSyncDateTime(): ?\DateTimeInterface
    {
        return $this->objectLastSyncDateTime;
    }
}
