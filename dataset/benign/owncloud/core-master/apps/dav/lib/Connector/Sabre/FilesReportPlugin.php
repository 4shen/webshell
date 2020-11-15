<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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

namespace OCA\DAV\Connector\Sabre;

use OC\Files\View;
use OCA\DAV\Files\Xml\FilterRequest;
use OCP\Files\Folder;
use OCP\IGroupManager;
use OCP\ITagManager;
use OCP\IUserSession;
use OCP\SystemTag\ISystemTagManager;
use OCP\SystemTag\ISystemTagObjectMapper;
use OCP\SystemTag\TagNotFoundException;
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\PreconditionFailed;
use Sabre\DAV\PropFind;
use Sabre\DAV\ServerPlugin;
use Sabre\DAV\Tree;
use Sabre\DAV\Xml\Element\Response;

class FilesReportPlugin extends ServerPlugin {

	// namespace
	const NS_OWNCLOUD = 'http://owncloud.org/ns';
	const REPORT_NAME            = '{http://owncloud.org/ns}filter-files';
	const SYSTEMTAG_PROPERTYNAME = '{http://owncloud.org/ns}systemtag';

	/**
	 * Reference to main server object
	 *
	 * @var \Sabre\DAV\Server
	 */
	private $server;

	/**
	 * @var Tree
	 */
	private $tree;

	/**
	 * @var View
	 */
	private $fileView;

	/**
	 * @var ISystemTagManager
	 */
	private $tagManager;

	/**
	 * @var ISystemTagObjectMapper
	 */
	private $tagMapper;

	/**
	 * Manager for private tags
	 *
	 * @var ITagManager
	 */
	private $fileTagger;

	/**
	 * @var IUserSession
	 */
	private $userSession;

	/**
	 * @var IGroupManager
	 */
	private $groupManager;

	/**
	 * @var Folder
	 */
	private $userFolder;

	/**
	 * @param Tree $tree
	 * @param View $view
	 * @param ISystemTagManager $tagManager
	 * @param ISystemTagObjectMapper $tagMapper
	 * @param ITagManager $fileTagger manager for private tags
	 * @param IUserSession $userSession
	 * @param IGroupManager $groupManager
	 * @param Folder $userFolder
	 */
	public function __construct(Tree $tree,
								View $view,
								ISystemTagManager $tagManager,
								ISystemTagObjectMapper $tagMapper,
								ITagManager $fileTagger,
								IUserSession $userSession,
								IGroupManager $groupManager,
								Folder $userFolder
	) {
		$this->tree = $tree;
		$this->fileView = $view;
		$this->tagManager = $tagManager;
		$this->tagMapper = $tagMapper;
		$this->fileTagger = $fileTagger;
		$this->userSession = $userSession;
		$this->groupManager = $groupManager;
		$this->userFolder = $userFolder;
	}

	/**
	 * This initializes the plugin.
	 *
	 * This function is called by \Sabre\DAV\Server, after
	 * addPlugin is called.
	 *
	 * This method should set up the required event subscriptions.
	 *
	 * @param \Sabre\DAV\Server $server
	 * @return void
	 */
	public function initialize(\Sabre\DAV\Server $server) {
		$server->xml->namespaceMap[self::NS_OWNCLOUD] = 'oc';

		$server->xml->elementMap[self::REPORT_NAME] = FilterRequest::class;

		$this->server = $server;
		$this->server->on('report', [$this, 'onReport']);
	}

	/**
	 * Returns a list of reports this plugin supports.
	 *
	 * This will be used in the {DAV:}supported-report-set property.
	 *
	 * @param string $uri
	 * @return array
	 */
	public function getSupportedReportSet($uri) {
		return [self::REPORT_NAME];
	}

	/**
	 * REPORT operations to look for files
	 *
	 * @param string $reportName
	 * @param mixed $report
	 * @param string $uri
	 * @return bool
	 * @throws BadRequest
	 * @throws PreconditionFailed
	 * @internal param $ [] $report
	 */
	public function onReport($reportName, $report, $uri) {
		$reportTargetNode = $this->server->tree->getNodeForPath($uri);
		if (!$reportTargetNode instanceof Directory || $reportName !== self::REPORT_NAME) {
			return;
		}

		$requestedProps = $report->properties;
		$filterRules = $report->filters;

		// "systemtag" is always an array of tags, favorite a string/int/null
		if (empty($filterRules['systemtag']) && $filterRules['favorite'] === null) {
			// FIXME: search currently not possible because results are missing properties!
			throw new BadRequest('No filter criteria specified');
		} else {
			if (isset($report->search['pattern'])) {
				// TODO: implement this at some point...
				throw new BadRequest('Search pattern cannot be combined with filter');
			}

			// gather all file ids matching filter
			try {
				$resultFileIds = $this->processFilterRules($filterRules);
			} catch (TagNotFoundException $e) {
				throw new PreconditionFailed('Cannot filter by non-existing tag');
			}

			// pre-slice the results if needed for pagination to not waste
			// time resolving nodes that will not be returned anyway
			$resultFileIds = $this->slice($resultFileIds, $report);

			// find sabre nodes by file id, restricted to the root node path
			$results = $this->findNodesByFileIds($reportTargetNode, $resultFileIds);
		}

		$filesUri = $this->getFilesBaseUri($uri, $reportTargetNode->getPath());
		$results = $this->prepareResponses($filesUri, $requestedProps, $results);

		$xml = $this->server->generateMultiStatus($results);

		$this->server->httpResponse->setStatus(207);
		$this->server->httpResponse->setHeader('Content-Type', 'application/xml; charset=utf-8');
		$this->server->httpResponse->setBody($xml);

		return false;
	}

	private function slice($results, $report) {
		if ($report->search !== null) {
			$length = $report->search['limit'];
			$offset = $report->search['offset'];
			$results = \array_slice($results, $offset, $length);
		}
		return $results;
	}

	/**
	 * Returns the base uri of the files root by removing
	 * the subpath from the URI
	 *
	 * @param string $uri URI from this request
	 * @param string $subPath subpath to remove from the URI
	 *
	 * @return string files base uri
	 */
	private function getFilesBaseUri($uri, $subPath) {
		$uri = \trim($uri, '/');
		$subPath = \trim($subPath, '/');
		if (empty($subPath)) {
			$filesUri = $uri;
		} else {
			$filesUri = \substr($uri, 0, \strlen($uri) - \strlen($subPath));
		}
		$filesUri = \trim($filesUri, '/');
		if (empty($filesUri)) {
			return '';
		}
		return '/' . $filesUri;
	}

	/**
	 * Find file ids matching the given filter rules
	 *
	 * @param array $filterRules
	 * @return array array of unique file id results
	 *
	 * @throws TagNotFoundException whenever a tag was not found
	 */
	protected function processFilterRules($filterRules) {
		$resultFileIds = null;
		$systemTagIds = $filterRules['systemtag'];
		$favoriteFilter = $filterRules['favorite'];

		if ($favoriteFilter !== null) {
			$resultFileIds = $this->fileTagger->load('files')->getFavorites();
			if (empty($resultFileIds)) {
				return [];
			}
		}

		if (!empty($systemTagIds)) {
			$fileIds = $this->getSystemTagFileIds($systemTagIds);
			if (empty($resultFileIds)) {
				$resultFileIds = $fileIds;
			} else {
				$resultFileIds = \array_intersect($fileIds, $resultFileIds);
			}
		}

		return $resultFileIds;
	}

	private function getSystemTagFileIds($systemTagIds) {
		$resultFileIds = null;

		// check user permissions, if applicable
		if (!$this->isAdmin()) {
			// check visibility/permission
			$tags = $this->tagManager->getTagsByIds($systemTagIds);
			$unknownTagIds = [];
			foreach ($tags as $tag) {
				if (!$tag->isUserVisible()) {
					$unknownTagIds[] = $tag->getId();
				}
			}

			if (!empty($unknownTagIds)) {
				throw new TagNotFoundException('Tag with ids ' . \implode(', ', $unknownTagIds) . ' not found');
			}
		}

		// fetch all file ids and intersect them
		foreach ($systemTagIds as $systemTagId) {
			$fileIds = $this->tagMapper->getObjectIdsForTags($systemTagId, 'files');

			if (empty($fileIds)) {
				// This tag has no files, nothing can ever show up
				return [];
			}

			// first run ?
			if ($resultFileIds === null) {
				$resultFileIds = $fileIds;
			} else {
				$resultFileIds = \array_intersect($resultFileIds, $fileIds);
			}

			if (empty($resultFileIds)) {
				// Empty intersection, nothing can show up anymore
				return [];
			}
		}
		return $resultFileIds;
	}

	/**
	 * Prepare propfind response for the given nodes
	 *
	 * @param string $filesUri $filesUri URI leading to root of the files URI,
	 * with a leading slash but no trailing slash
	 * @param string[] $requestedProps requested properties
	 * @param Node[] nodes nodes for which to fetch and prepare responses
	 * @return Response[]
	 */
	public function prepareResponses($filesUri, $requestedProps, $nodes) {
		$results = [];
		foreach ($nodes as $node) {
			$propFind = new PropFind($filesUri . $node->getPath(), $requestedProps);

			$this->server->getPropertiesByNode($propFind, $node);
			// copied from Sabre Server's getPropertiesForPath
			$result = $propFind->getResultForMultiStatus();
			$result['href'] = $propFind->getPath();

			$results[] = $result;
		}
		return $results;
	}

	/**
	 * Find Sabre nodes by file ids
	 *
	 * @param Node $rootNode root node for search
	 * @param array $fileIds file ids
	 * @return Node[] array of Sabre nodes
	 */
	public function findNodesByFileIds($rootNode, $fileIds) {
		$folder = $this->userFolder;
		if (\trim($rootNode->getPath(), '/') !== '') {
			$folder = $folder->get($rootNode->getPath());
		}

		$results = [];
		foreach ($fileIds as $fileId) {
			$entries = $folder->getById($fileId, true);
			$entry = $entries[0] ?? null;
			if ($entry) {
				$entry = $entries[0];
				$node = $this->makeSabreNode($entry);
				if ($node) {
					$results[] = $node;
				}
			}
		}

		return $results;
	}

	private function makeSabreNode(\OCP\Files\Node $filesNode) {
		if ($filesNode instanceof \OCP\Files\File) {
			return new File($this->fileView, $filesNode);
		} elseif ($filesNode instanceof \OCP\Files\Folder) {
			return new Directory($this->fileView, $filesNode);
		}
		throw new \Exception('Unrecognized Files API node returned, aborting');
	}

	/**
	 * Returns whether the currently logged in user is an administrator
	 */
	private function isAdmin() {
		$user = $this->userSession->getUser();
		if ($user !== null) {
			return $this->groupManager->isAdmin($user->getUID());
		}
		return false;
	}
}
