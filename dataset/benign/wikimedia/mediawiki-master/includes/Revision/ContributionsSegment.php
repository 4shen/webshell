<?php

namespace MediaWiki\Revision;

/**
 * @newable
 * @since 1.35
 */
class ContributionsSegment {

	/**
	 * @var RevisionRecord[]
	 */
	private $revisions;

	/**
	 * @var string[][]
	 */
	private $tags;

	/**
	 * @var string|null
	 */
	private $before;

	/**
	 * @var string|null
	 */
	private $after;

	/**
	 * @var array
	 */
	private $flags;

	/**
	 * ContributionsSegment constructor.
	 *
	 * @param RevisionRecord[] $revisions
	 * @param string[][] $tags An associative array mapping revision IDs to lists of tag names.
	 * @param string|null $before
	 * @param string|null $after
	 * @param array $flags Is an associative array, known fields are:
	 *  - newest: bool indicating whether this segment is the newest in time
	 *  - oldest: bool indicating whether this segment is the oldest in time
	 */
	public function __construct(
		array $revisions,
		array $tags,
		?string $before,
		?string $after,
		array $flags = []
	) {
		$this->revisions = $revisions;
		$this->tags = $tags;
		$this->before = $before;
		$this->after = $after;
		$this->flags = $flags;
	}

	/**
	 * Returns an associative array mapping revision IDs to lists of tag names.
	 *
	 * @param int $revId a revision ID
	 *
	 * @return string[]
	 */
	public function getTagsForRevision( $revId ): array {
		return $this->tags[$revId] ?? [];
	}

	/**
	 * @return RevisionRecord[]
	 */
	public function getRevisions(): array {
		return $this->revisions;
	}

	/**
	 * @return string|null
	 */
	public function getBefore(): ?string {
		return $this->before;
	}

	/**
	 * @return string|null
	 */
	public function getAfter(): ?string {
		return $this->after;
	}

	/**
	 * The value of the 'newest' field of the flags passed to the constructor, or false
	 * if that field was not set.
	 *
	 * @return bool
	 */
	public function isNewest() {
		return $this->flags['newest'] ?? false;
	}

	/**
	 * The value of the 'oldest' field of the flags passed to the constructor, or false
	 * if that field was not set.
	 *
	 * @return bool
	 */
	public function isOldest() {
		return $this->flags['oldest'] ?? false;
	}

}
