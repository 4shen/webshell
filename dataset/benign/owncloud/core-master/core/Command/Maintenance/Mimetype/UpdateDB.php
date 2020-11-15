<?php
/**
 * @author Robin McCorkell <robin@mccorkell.me.uk>
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

namespace OC\Core\Command\Maintenance\Mimetype;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use OCP\Files\IMimeTypeDetector;
use OCP\Files\IMimeTypeLoader;

class UpdateDB extends Command {
	const DEFAULT_MIMETYPE = 'application/octet-stream';

	/** @var IMimeTypeDetector */
	protected $mimetypeDetector;

	/** @var IMimeTypeLoader */
	protected $mimetypeLoader;

	public function __construct(
		IMimeTypeDetector $mimetypeDetector,
		IMimeTypeLoader $mimetypeLoader
	) {
		parent::__construct();
		$this->mimetypeDetector = $mimetypeDetector;
		$this->mimetypeLoader = $mimetypeLoader;
	}

	protected function configure() {
		$this
			->setName('maintenance:mimetype:update-db')
			->setDescription('Update database mimetypes and file cache.')
			->addOption(
				'repair-filecache',
				null,
				InputOption::VALUE_NONE,
				'Repair the file cache for all mimetypes, not just the new ones.'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		'@phan-var \OC\Files\MimetypeDetector $this->mimetypeDetector';
		$mappings = $this->mimetypeDetector->getAllMappings();

		$totalFilecacheUpdates = 0;
		$totalNewMimetypes = 0;

		foreach ($mappings as $ext => $mimetypes) {
			if ($ext[0] === '_') {
				// comment
				continue;
			}
			$mimetype = $mimetypes[0];
			$existing = $this->mimetypeLoader->exists($mimetype);
			// this will add the mimetype if it didn't exist
			$mimetypeId = $this->mimetypeLoader->getId($mimetype);

			if (!$existing) {
				$output->writeln('Added mimetype "'.$mimetype.'" to database');
				$totalNewMimetypes++;
			}

			if (!$existing || $input->getOption('repair-filecache')) {
				/* @phan-suppress-next-line PhanUndeclaredMethod */
				$touchedFilecacheRows = $this->mimetypeLoader->updateFilecache($ext, $mimetypeId);
				if ($touchedFilecacheRows > 0) {
					$output->writeln('Updated '.$touchedFilecacheRows.' filecache rows for mimetype "'.$mimetype.'"');
				}
				$totalFilecacheUpdates += $touchedFilecacheRows;
			}
		}

		$output->writeln('Added '.$totalNewMimetypes.' new mimetypes');
		$output->writeln('Updated '.$totalFilecacheUpdates.' filecache rows');
	}
}
