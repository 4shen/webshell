<?php

namespace MediaWiki\Tests\Revision;

use ChangeTags;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\ContributionsLookup;
use MediaWiki\Revision\ContributionsSegment;
use MediaWiki\Revision\RevisionRecord;
use MediaWikiIntegrationTestCase;
use User;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * @covers \MediaWiki\Revision\ContributionsLookup
 *
 * @group Database
 */
class ContributionsLookupTest extends MediaWikiIntegrationTestCase {

	/**
	 * @var RevisionRecord[] Associative array mapping revision number (e.g. 1 for first
	 *      revision made) to revision record
	 */
	private static $storedRevisions;

	/**
	 * @var \User
	 */
	private static $testUser;

	/**
	 * @var string[][]
	 */
	private static $storedTags;

	public function addDBDataOnce() {
		$user = $this->getTestUser()->getUser();

		$clock = (int)ConvertibleTimestamp::now( TS_UNIX );
		ConvertibleTimestamp::setFakeTime( function () use ( &$clock ) {
			return ++$clock;
		} );

		self::$testUser = $user;
		self::$storedRevisions[1] = $this->editPage( __METHOD__ . '_1', 'Lorem Ipsum 1', 'test', NS_MAIN, $user )
			->getValue()['revision-record'];
		self::$storedRevisions[2] = $this->editPage( __METHOD__ . '_2', 'Lorem Ipsum 2', 'test', NS_TALK, $user )
			->getValue()['revision-record'];
		self::$storedRevisions[3] = $this->editPage( __METHOD__ . '_2', 'Lorem Ipsum 3', 'test', NS_MAIN, $user )
			->getValue()['revision-record'];
		self::$storedRevisions[4] = $this->editPage( __METHOD__ . '_1', 'Lorem Ipsum 4', 'test', NS_TALK, $user )
			->getValue()['revision-record'];

		$tag1 = 'test-ContributionsLookup-1';
		$tag2 = 'test-ContributionsLookup-2';

		ChangeTags::defineTag( $tag1 );
		ChangeTags::defineTag( $tag2 );

		self::$storedTags = [
			1 => [],
			2 => [ $tag1, $tag2 ],
			3 => [ $tag2 ],
			4 => [],
		];

		foreach ( self::$storedTags as $idx => $tags ) {
			if ( !$tags ) {
				continue;
			}

			$revId = self::$storedRevisions[$idx]->getId();
			ChangeTags::addTags( $tags, null, $revId );
		}

		ConvertibleTimestamp::setFakeTime( false );
	}

	/**
	 * @covers \MediaWiki\Revision\ContributionsLookup::getContributions()
	 */
	public function testGetListOfRevisionsByUserIdentity() {
		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
		$contributionsLookup = new ContributionsLookup( $revisionStore );
		$performer = self::$testUser;

		$segment =
			$contributionsLookup->getContributions( self::$testUser, 2, $performer );

		// Desc order comes back from db query
		$this->assertSegmentRevisions( [ 4, 3 ], $segment );
	}

	/**
	 * @covers \MediaWiki\Revision\ContributionsLookup::getContributions()
	 */
	public function testGetSegmentChain() {
		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
		$contributionsLookup = new ContributionsLookup( $revisionStore );

		$segment1 = $contributionsLookup->getContributions( self::$testUser, 2, self::$testUser );
		$this->assertInstanceOf( ContributionsSegment::class, $segment1 );
		$this->assertCount( 2, $segment1->getRevisions() );
		$this->assertNotNull( $segment1->getAfter() );
		$this->assertNotNull( $segment1->getBefore() );
		$this->assertFalse( $segment1->isOldest() );

		$segment2 =
			$contributionsLookup->getContributions( self::$testUser, 2, self::$testUser, $segment1->getBefore() );
		$this->assertCount( 2, $segment2->getRevisions() );
		$this->assertNotNull( $segment2->getAfter() );
		$this->assertNull( $segment2->getBefore() );
		$this->assertTrue( $segment2->isOldest() );

		$segment3 =
			$contributionsLookup->getContributions( self::$testUser, 2, self::$testUser, $segment2->getAfter() );
		$this->assertInstanceOf( ContributionsSegment::class, $segment3 );
		$this->assertCount( 2, $segment3->getRevisions() );
		$this->assertNotNull( $segment3->getAfter() );
		$this->assertNotNull( $segment3->getBefore() );
		$this->assertFalse( $segment3->isOldest() );

		$this->assertSegmentRevisions( [ 4, 3 ], $segment1 );
		$this->assertSegmentRevisions( [ 2, 1 ], $segment2 );
		$this->assertSegmentRevisions( [ 4, 3 ], $segment3 );
	}

	/**
	 * @param int[] $expectedRevisions A list of indexes into self::$storedRevisions
	 * @param ContributionsSegment $segmentObject
	 */
	private function assertSegmentRevisions( $expectedRevisions, $segmentObject ) {
		$revisions = $segmentObject->getRevisions();

		$this->assertSameSize( $expectedRevisions, $revisions );

		foreach ( $expectedRevisions as $idx => $editNumber ) {
			$expected = self::$storedRevisions[$editNumber];
			$actual = $revisions[$idx];
			$this->assertSame( $expected->getId(), $actual->getId() );
			$this->assertSame( $expected->getPageId(), $actual->getPageId() );
			$this->assertSame(
				$expected->getPageAsLinkTarget()->getPrefixedDBkey(),
				$actual->getPageAsLinkTarget()->getPrefixedDBkey()
			);

			$expectedUser = $expected->getUser( RevisionRecord::RAW )->getName();
			$actualUser = $actual->getUser( RevisionRecord::RAW )->getName();
			$this->assertSame( $expectedUser, $actualUser );

			$expectedTags = self::$storedTags[ $editNumber ];
			$this->assertRevisionTags( $expectedTags, $segmentObject, $actual );
		}
	}

	public function provideBadSegmentMarker() {
		yield [ '' ];
		yield [ '|' ];
		yield [ '0' ];
		yield [ '9' ];
		yield [ 'x|0' ];
		yield [ 'x|9' ];
		yield [ 'x|x' ];
	}

	/**
	 * @dataProvider provideBadSegmentMarker
	 */
	public function testBadSegmentMarkerReturnsLatestSegment( $segment ) {
		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
		$contributionsLookup = new ContributionsLookup( $revisionStore );

		$segment = $contributionsLookup->getContributions( self::$testUser, 2, self::$testUser, $segment );
		$this->assertSegmentRevisions( [ 4, 3 ], $segment );
	}

	public function testPermissionChecksAreApplied() {
		$editingUser = self::$testUser;
		$sysop = $this->getTestUser( [ 'sysop', 'suppress' ] )->getUser();
		$anon = User::newFromId( 0 );

		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
		$contributionsLookup = new ContributionsLookup( $revisionStore );

		$revIds = [ self::$storedRevisions[1]->getId(), self::$storedRevisions[2]->getId() ];
		$this->db->update(
			'revision',
			[ 'rev_deleted' => RevisionRecord::DELETED_USER ],
			[ 'rev_id' => $revIds ],
			__METHOD__
		);

		// sanity
		$this->assertSame( 2, $this->db->affectedRows() );

		// anons should not see suppressed contribs
		$segment = $contributionsLookup->getContributions( $editingUser, 10, $anon );
		$this->assertSegmentRevisions( [ 4, 3 ], $segment );

		// sysop also gets suppressed contribs
		$segment = $contributionsLookup->getContributions( $editingUser, 10, $sysop );
		$this->assertSegmentRevisions( [ 4, 3, 2, 1 ], $segment );
	}

	/**
	 * @param array $expectedTags
	 * @param ContributionsSegment $segmentObject
	 * @param RevisionRecord $actual
	 */
	private function assertRevisionTags(
		array $expectedTags,
		ContributionsSegment $segmentObject,
		RevisionRecord $actual
	): void {
		// FIXME: fails under postgres, see T195807
		if ( $this->db->getType() !== 'postgres' ) {
			$actualTags = $segmentObject->getTagsForRevision( $actual->getId() );
			$this->assertArrayEquals( $expectedTags, $actualTags );
		}
	}

}
