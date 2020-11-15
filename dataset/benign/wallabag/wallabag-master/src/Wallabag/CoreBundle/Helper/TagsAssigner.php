<?php

namespace Wallabag\CoreBundle\Helper;

use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Repository\TagRepository;

class TagsAssigner
{
    /**
     * @var TagRepository
     */
    protected $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Assign some tags to an entry.
     *
     * @param array|string $tags          An array of tag or a string coma separated of tag
     * @param array        $entitiesReady Entities from the EntityManager which are persisted but not yet flushed
     *                                    It is mostly to fix duplicate tag on import @see http://stackoverflow.com/a/7879164/569101
     *
     * @return Tag[]
     */
    public function assignTagsToEntry(Entry $entry, $tags, array $entitiesReady = [])
    {
        $tagsEntities = [];

        if (!\is_array($tags)) {
            $tags = explode(',', $tags);
        }

        // keeps only Tag entity from the "not yet flushed entities"
        $tagsNotYetFlushed = [];
        foreach ($entitiesReady as $entity) {
            if ($entity instanceof Tag) {
                $tagsNotYetFlushed[$entity->getLabel()] = $entity;
            }
        }

        foreach ($tags as $label) {
            $label = trim(mb_convert_case($label, MB_CASE_LOWER));

            // avoid empty tag
            if (0 === \strlen($label)) {
                continue;
            }

            if (isset($tagsNotYetFlushed[$label])) {
                $tagEntity = $tagsNotYetFlushed[$label];
            } else {
                $tagEntity = $this->tagRepository->findOneByLabel($label);

                if (null === $tagEntity) {
                    $tagEntity = new Tag();
                    $tagEntity->setLabel($label);
                }
            }

            // only add the tag on the entry if the relation doesn't exist
            if (false === $entry->getTags()->contains($tagEntity)) {
                $entry->addTag($tagEntity);
                $tagsEntities[] = $tagEntity;
            }
        }

        return $tagsEntities;
    }
}
