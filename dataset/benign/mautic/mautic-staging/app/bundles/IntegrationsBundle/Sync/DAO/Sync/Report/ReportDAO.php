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

namespace Mautic\IntegrationsBundle\Sync\DAO\Sync\Report;

use Mautic\IntegrationsBundle\Sync\DAO\Mapping\RemappedObjectDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\RelationsDAO;
use Mautic\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use Mautic\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;

class ReportDAO
{
    /**
     * @var string
     */
    private $integration;

    /**
     * @var array
     */
    private $objects = [];

    /**
     * @var array
     */
    private $remappedObjects = [];

    /**
     * @var RelationsDAO
     */
    private $relationsDAO;

    /**
     * @param $integration
     */
    public function __construct($integration)
    {
        $this->integration     = $integration;
        $this->relationsDAO    = new RelationsDAO();
    }

    /**
     * @return string
     */
    public function getIntegration()
    {
        return $this->integration;
    }

    /**
     * @return $this
     */
    public function addObject(ObjectDAO $objectDAO)
    {
        if (!isset($this->objects[$objectDAO->getObject()])) {
            $this->objects[$objectDAO->getObject()] = [];
        }

        $this->objects[$objectDAO->getObject()][$objectDAO->getObjectId()] = $objectDAO;

        return $this;
    }

    /**
     * @param mixed  $oldObjectId
     * @param string $oldObjectName
     * @param string $newObjectName
     * @param mixed  $newObjectId
     */
    public function remapObject($oldObjectName, $oldObjectId, $newObjectName, $newObjectId = null): void
    {
        if (null === $newObjectId) {
            $newObjectId = $oldObjectId;
        }

        $this->remappedObjects[$oldObjectId] = new RemappedObjectDAO($this->integration, $oldObjectName, $oldObjectId, $newObjectName, $newObjectId);
    }

    /**
     * @param $objectName
     * @param $objectId
     * @param $fieldName
     *
     * @return InformationChangeRequestDAO
     *
     * @throws ObjectNotFoundException
     * @throws FieldNotFoundException
     */
    public function getInformationChangeRequest($objectName, $objectId, $fieldName)
    {
        if (empty($this->objects[$objectName][$objectId])) {
            throw new ObjectNotFoundException($objectName.':'.$objectId);
        }

        /** @var ObjectDAO $reportObject */
        $reportObject             = $this->objects[$objectName][$objectId];
        $reportField              = $reportObject->getField($fieldName);
        $informationChangeRequest = new InformationChangeRequestDAO(
            $this->integration,
            $objectName,
            $objectId,
            $fieldName,
            $reportField->getValue()
        );

        $informationChangeRequest->setPossibleChangeDateTime($reportObject->getChangeDateTime())
            ->setCertainChangeDateTime($reportField->getChangeDateTime());

        return $informationChangeRequest;
    }

    /**
     * @return ObjectDAO[]
     */
    public function getObjects(?string $objectName)
    {
        $returnedObjects = [];
        if (null === $objectName) {
            foreach ($this->objects as $objects) {
                foreach ($objects as $object) {
                    $returnedObjects[] = $object;
                }
            }

            return $returnedObjects;
        }

        return isset($this->objects[$objectName]) ? $this->objects[$objectName] : [];
    }

    /**
     * @return RemappedObjectDAO[]
     */
    public function getRemappedObjects(): array
    {
        return $this->remappedObjects;
    }

    /**
     * @param int $objectId
     */
    public function getObject(string $objectName, $objectId): ?ObjectDAO
    {
        if (!isset($this->objects[$objectName])) {
            return null;
        }

        if (!isset($this->objects[$objectName][$objectId])) {
            return null;
        }

        return $this->objects[$objectName][$objectId];
    }

    /**
     * @return bool
     */
    public function shouldSync()
    {
        return !empty($this->objects);
    }

    public function getRelations(): RelationsDAO
    {
        return $this->relationsDAO;
    }
}
