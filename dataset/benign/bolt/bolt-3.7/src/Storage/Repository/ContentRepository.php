<?php

namespace Bolt\Storage\Repository;

use Bolt\Events\HydrationEvent;
use Bolt\Events\StorageEvents;
use Bolt\Storage\ContentLegacyService;
use Bolt\Storage\Entity\Content;
use Bolt\Storage\Mapping\ContentTypeTitleTrait;
use Bolt\Storage\Repository;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * A Repository class that handles dynamically created content tables.
 */
class ContentRepository extends Repository
{
    use ContentTypeTitleTrait;

    /** @var ContentLegacyService */
    protected $legacy;

    /**
     * Fetches details on records for select lists.
     *
     * @param array  $contentType
     * @param string $order
     * @param array  $neededFields
     *
     * @return array|false
     */
    public function getSelectList(array $contentType, $order, $neededFields = [])
    {
        $query = $this->querySelectList($contentType, $order, $neededFields);

        return $query->execute()->fetchAll();
    }

    /**
     * Build the query for a record select list.
     *
     * @param array  $contentType
     * @param string $order
     * @param array  $neededFields
     *
     * @return QueryBuilder
     */
    public function querySelectList(array $contentType, $order = null, $neededFields = [])
    {
        // Only if the first character of the string is `-`, we'll need to sort DESC.'
        if (strpos($order, '-') === 0) {
            $direction = 'DESC';
            $order = ltrim($order, '-');
        } else {
            $direction = 'ASC';
        }

        array_unshift($neededFields, 'id', $this->getTitleColumnName($contentType) . ' as title');

        $qb = $this->createQueryBuilder($contentType['tablename']);
        $qb->select(implode(', ', $neededFields));

        if ($order !== null) {
            $qb->orderBy($order, $direction);
        }

        return $qb;
    }

    /**
     * Set the legacy Content service object.
     *
     * @param ContentLegacyService $service
     */
    public function setLegacyService(ContentLegacyService $service)
    {
        $this->legacy = $service;
        $this->event()->addListener(StorageEvents::PRE_HYDRATE, [$this, 'hydrateLegacyHandler']);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias = 'content')
    {
        return parent::createQueryBuilder($alias);
    }

    /**
     * Hydration handler for the legacy object.
     *
     * @param HydrationEvent $event
     */
    public function hydrateLegacyHandler(HydrationEvent $event)
    {
        $entity = $event->getArgument('entity');
        if ($entity instanceof Content) {
            $entity->setLegacyService($this->legacy);
        }
    }
}
