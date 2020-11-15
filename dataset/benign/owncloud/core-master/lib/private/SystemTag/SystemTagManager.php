<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC\SystemTag;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ManagerEvent;
use OCP\SystemTag\TagAlreadyExistsException;
use OCP\SystemTag\TagNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OCP\IGroupManager;
use OCP\SystemTag\ISystemTag;
use OCP\IUser;

/**
 * Manager class for system tags
 */
class SystemTagManager implements ISystemTagManager {
	const TAG_TABLE = 'systemtag';
	const TAG_GROUP_TABLE = 'systemtag_group';

	/** @var IDBConnection */
	protected $connection;

	/** @var EventDispatcherInterface */
	protected $dispatcher;

	/** @var IGroupManager */
	protected $groupManager;

	/**
	 * Prepared query for selecting tags directly
	 *
	 * @var \OCP\DB\QueryBuilder\IQueryBuilder
	 */
	private $selectTagQuery;

	/**
	 * Constructor.
	 *
	 * @param IDBConnection $connection database connection
	 * @param EventDispatcherInterface $dispatcher
	 */
	public function __construct(
		IDBConnection $connection,
		IGroupManager $groupManager,
		EventDispatcherInterface $dispatcher
	) {
		$this->connection = $connection;
		$this->groupManager = $groupManager;
		$this->dispatcher = $dispatcher;

		$query = $this->connection->getQueryBuilder();
		$this->selectTagQuery = $query->select('*')
			->from(self::TAG_TABLE)
			->where($query->expr()->eq('name', $query->createParameter('name')))
			->andWhere($query->expr()->eq('visibility', $query->createParameter('visibility')))
			->andWhere($query->expr()->eq('editable', $query->createParameter('editable')))
			->andWhere($query->expr()->eq('assignable', $query->createParameter('assignable')));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTagsByIds($tagIds) {
		if (!\is_array($tagIds)) {
			$tagIds = [$tagIds];
		}

		$tags = [];

		// note: not all databases will fail if it's a string or starts with a number
		foreach ($tagIds as $tagId) {
			if (!\is_numeric($tagId)) {
				throw new \InvalidArgumentException('Tag id must be integer');
			}
		}

		$query = $this->connection->getQueryBuilder();
		$query->select('*')
			->from(self::TAG_TABLE)
			->where($query->expr()->in('id', $query->createParameter('tagids')))
			->addOrderBy('name', 'ASC')
			->addOrderBy('visibility', 'ASC')
			->addOrderBy('editable', 'ASC')
			->addOrderBy('assignable', 'ASC')
			->setParameter('tagids', $tagIds, IQueryBuilder::PARAM_INT_ARRAY);

		$result = $query->execute();
		while ($row = $result->fetch()) {
			$tags[$row['id']] = $this->createSystemTagFromRow($row);
		}

		$result->closeCursor();

		if (\count($tags) !== \count($tagIds)) {
			throw new TagNotFoundException(
				'Tag id(s) not found', 0, null, \array_diff($tagIds, \array_keys($tags))
			);
		}

		return $tags;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllTags($visibilityFilter = null, $nameSearchPattern = null) {
		$tags = [];

		$query = $this->connection->getQueryBuilder();
		$query->select('*')
			->from(self::TAG_TABLE);

		if ($visibilityFilter !== null) {
			$query->andWhere($query->expr()->eq('visibility', $query->createPositionalParameter((int)$visibilityFilter)));
		}

		if (!empty($nameSearchPattern)) {
			$query->andWhere(
				$query->expr()->like(
					'name',
					$query->createPositionalParameter('%' . $this->connection->escapeLikeParameter($nameSearchPattern). '%')
				)
			);
		}

		$query
			->addOrderBy('name', 'ASC')
			->addOrderBy('visibility', 'ASC')
			->addOrderBy('editable', 'ASC')
			->addOrderBy('assignable', 'ASC');

		$result = $query->execute();
		while ($row = $result->fetch()) {
			$tags[$row['id']] = $this->createSystemTagFromRow($row);
		}

		$result->closeCursor();

		return $tags;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTag($tagName, $userVisible, $userAssignable, $userEditable = null) {
		$userVisible = (int)$userVisible;
		$userAssignable = (int)$userAssignable;

		/**
		 * Change made inorder to make sure this API should not be broken,
		 * if variable $userEditable is not passed to this method.
		 */
		if ($userEditable === null) {
			$userEditable = $userAssignable;
		} elseif (\is_bool($userEditable)) {
			$userEditable = (int)$userEditable;
		}

		$result = $this->selectTagQuery
			->setParameter('name', $tagName)
			->setParameter('visibility', $userVisible)
			->setParameter('editable', $userAssignable)
			->setParameter('assignable', $userEditable)
			->execute();

		$row = $result->fetch();
		$result->closeCursor();
		if (!$row) {
			throw new TagNotFoundException(
				'Tag ("' . $tagName . '", '. $userVisible . ', ' . $userAssignable . ') does not exist'
			);
		}

		return $this->createSystemTagFromRow($row);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createTag($tagName, $userVisible, $userAssignable, $userEditable = null) {
		$userVisible = (int)$userVisible;
		$userAssignable = (int)$userAssignable;

		/**
		 * Change made inorder to make sure this API should not be broken,
		 * if variable $userEditable is not passed to this method.
		 */
		if ($userEditable === null) {
			$userEditable = 1;
		} elseif (\is_bool($userEditable)) {
			$userEditable = (int)$userEditable;
		}

		if ($userEditable === 1) {
			$editable = $userAssignable;
		} else {
			$editable = 0;
			$userAssignable = 1;
		}

		$query = $this->connection->getQueryBuilder();
		$query->insert(self::TAG_TABLE)
			->values([
				'name' => $query->createNamedParameter($tagName),
				'visibility' => $query->createNamedParameter($userVisible),
				'editable' => $query->createNamedParameter($editable),
				'assignable' => $query->createNamedParameter($userAssignable)
			]);

		try {
			$query->execute();
		} catch (UniqueConstraintViolationException $e) {
			throw new TagAlreadyExistsException(
				'Tag ("' . $tagName . '", '. $userVisible . ', ' . $userAssignable . ') already exists',
				0,
				$e
			);
		}

		$tagId = $query->getLastInsertId();

		$tag = new SystemTag(
			(int)$tagId,
			$tagName,
			(bool)$userVisible,
			(bool)$userAssignable,
			(bool)$editable
		);

		$this->dispatcher->dispatch(
			new ManagerEvent(ManagerEvent::EVENT_CREATE, $tag),
			ManagerEvent::EVENT_CREATE
		);

		return $tag;
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateTag($tagId, $tagName, $userVisible, $userAssignable, $userEditable = null) {
		$userVisible = (int)$userVisible;
		$userAssignable = (int)$userAssignable;

		/**
		 * Change made inorder to make sure this API should not be broken,
		 * if variable $userEditable is not passed to this method.
		 */
		if ($userEditable === null) {
			$userEditable = $userAssignable;
		} elseif (\is_bool($userEditable)) {
			$userEditable = (int)$userEditable;
		}

		try {
			$tags = $this->getTagsByIds($tagId);
		} catch (TagNotFoundException $e) {
			throw new TagNotFoundException(
				'Tag does not exist', 0, null, [$tagId]
			);
		}

		$beforeUpdate = \array_shift($tags);
		$afterUpdate = new SystemTag(
			(int) $tagId,
			$tagName,
			(bool) $userVisible,
			(bool) $userAssignable,
			(bool) $userEditable
		);

		$query = $this->connection->getQueryBuilder();
		$query->update(self::TAG_TABLE)
			->set('name', $query->createParameter('name'))
			->set('visibility', $query->createParameter('visibility'))
			->set('editable', $query->createParameter('editable'))
			->set('assignable', $query->createParameter('assignable'))
			->where($query->expr()->eq('id', $query->createParameter('tagid')))
			->setParameter('name', $tagName)
			->setParameter('visibility', $userVisible)
			->setParameter('editable', $userEditable)
			->setParameter('assignable', $userAssignable)
			->setParameter('tagid', $tagId);

		try {
			if ($query->execute() === 0) {
				throw new TagNotFoundException(
					'Tag does not exist', 0, null, [$tagId]
				);
			}
		} catch (UniqueConstraintViolationException $e) {
			throw new TagAlreadyExistsException(
				'Tag ("' . $tagName . '", '. $userVisible . ', ' . $userAssignable . ') already exists',
				0,
				$e
			);
		}

		$this->dispatcher->dispatch(
			new ManagerEvent(ManagerEvent::EVENT_UPDATE, $afterUpdate, $beforeUpdate),
			ManagerEvent::EVENT_UPDATE
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function deleteTags($tagIds) {
		if (!\is_array($tagIds)) {
			$tagIds = [$tagIds];
		}

		$tagNotFoundException = null;
		$tags = [];
		try {
			$tags = $this->getTagsByIds($tagIds);
		} catch (TagNotFoundException $e) {
			$tagNotFoundException = $e;

			// Get existing tag objects for the hooks later
			$existingTags = \array_diff($tagIds, $tagNotFoundException->getMissingTags());
			if (!empty($existingTags)) {
				try {
					$tags = $this->getTagsByIds($existingTags);
				} catch (TagNotFoundException $e) {
					// Ignore further errors...
				}
			}
		}

		// delete relationships first
		$query = $this->connection->getQueryBuilder();
		$query->delete(SystemTagObjectMapper::RELATION_TABLE)
			->where($query->expr()->in('systemtagid', $query->createParameter('tagids')))
			->setParameter('tagids', $tagIds, IQueryBuilder::PARAM_INT_ARRAY)
			->execute();

		$query = $this->connection->getQueryBuilder();
		$query->delete(self::TAG_TABLE)
			->where($query->expr()->in('id', $query->createParameter('tagids')))
			->setParameter('tagids', $tagIds, IQueryBuilder::PARAM_INT_ARRAY)
			->execute();

		foreach ($tags as $tag) {
			$this->dispatcher->dispatch(
				new ManagerEvent(ManagerEvent::EVENT_DELETE, $tag),
				ManagerEvent::EVENT_DELETE
			);
		}

		if ($tagNotFoundException !== null) {
			throw new TagNotFoundException(
				'Tag id(s) not found', 0, $tagNotFoundException, $tagNotFoundException->getMissingTags()
			);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function canUserAssignTag(ISystemTag $tag, IUser $user) {
		// early check to avoid unneeded group lookups
		if ($tag->isUserAssignable() && $tag->isUserVisible()) {
			return true;
		}

		if ($this->groupManager->isAdmin($user->getUID())) {
			return true;
		}

		if (!$tag->isUserVisible()) {
			return false;
		}

		$groupIds = $this->groupManager->getUserGroupIds($user);
		if (!empty($groupIds)) {
			$matchingGroups = \array_intersect($groupIds, $this->getTagGroups($tag));
			if (!empty($matchingGroups)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function canUserSeeTag(ISystemTag $tag, IUser $user) {
		if ($tag->isUserVisible()) {
			return true;
		}

		if ($this->groupManager->isAdmin($user->getUID())) {
			return true;
		}

		return false;
	}

	private function createSystemTagFromRow($row) {
		return new SystemTag((int)$row['id'], $row['name'], (bool)$row['visibility'], (bool)$row['assignable'], (bool)$row['editable']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTagGroups(ISystemTag $tag, $groupIds) {
		// delete relationships first
		$this->connection->beginTransaction();
		try {
			$query = $this->connection->getQueryBuilder();
			$query->delete(self::TAG_GROUP_TABLE)
				->where($query->expr()->eq('systemtagid', $query->createNamedParameter($tag->getId())))
				->execute();

			// add each group id
			$query = $this->connection->getQueryBuilder();
			$query->insert(self::TAG_GROUP_TABLE)
				->values([
					'systemtagid' => $query->createNamedParameter($tag->getId()),
					'gid' => $query->createParameter('gid'),
				]);
			foreach ($groupIds as $groupId) {
				if ($groupId === '') {
					continue;
				}
				$query->setParameter('gid', $groupId);
				$query->execute();
			}

			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollback();
			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTagGroups(ISystemTag $tag) {
		$groupIds = [];
		$query = $this->connection->getQueryBuilder();
		$query->select('gid')
			->from(self::TAG_GROUP_TABLE)
			->where($query->expr()->eq('systemtagid', $query->createNamedParameter($tag->getId())))
			->orderBy('gid');

		$result = $query->execute();
		while ($row = $result->fetch()) {
			$groupIds[] = $row['gid'];
		}

		$result->closeCursor();

		return $groupIds;
	}

	/**
	 * {@inheritdoc}
	 */
	public function canUserUseStaticTagInGroup(ISystemTag $tag, IUser $user) {
		if ($this->groupManager->isAdmin($user->getUID())) {
			return true;
		}
		if ($tag->isUserEditable() === false) {
			$groupIds = $this->groupManager->getUserGroupIds($user);
			if (!empty($groupIds)) {
				$matchingGroups = \array_intersect($groupIds, $this->getTagGroups($tag));
				if (!empty($matchingGroups)) {
					return true;
				}
			}
		}
		return false;
	}
}
