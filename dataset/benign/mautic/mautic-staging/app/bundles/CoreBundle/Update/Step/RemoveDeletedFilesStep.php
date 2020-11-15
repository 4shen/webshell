<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://www.mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Update\Step;

use Mautic\CoreBundle\Helper\PathsHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class RemoveDeletedFilesStep implements StepInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $appRoot;

    public function __construct(TranslatorInterface $translator, PathsHelper $pathsHelper, LoggerInterface $logger)
    {
        $this->translator = $translator;
        $this->appRoot    = $pathsHelper->getRootPath();
        $this->logger     = $logger;
    }

    public function getOrder(): int
    {
        return 10;
    }

    public function shouldExecuteInFinalStage(): bool
    {
        return false;
    }

    public function execute(ProgressBar $progressBar, InputInterface $input, OutputInterface $output): void
    {
        // Make sure we have a deleted_files list otherwise we can't process this step
        if (!file_exists($this->appRoot.'/deleted_files.txt')) {
            return;
        }

        $progressBar->setMessage($this->translator->trans('mautic.core.update.remove.deleted.files'));
        $progressBar->advance();

        $deletedFiles = json_decode(file_get_contents($this->appRoot.'/deleted_files.txt'), true);

        // Before looping over the deleted files, add in our upgrade specific files
        $deletedFiles += ['deleted_files.txt', 'upgrade.php'];

        foreach ($deletedFiles as $file) {
            $this->deleteFile($file);
        }

        @unlink($this->appRoot.'/deleted_files.txt');
    }

    private function deleteFile(string $file)
    {
        $path = $this->appRoot.'/'.$file;

        if (!file_exists($path)) {
            return;
        }

        // Try setting the permissions to 777 just to make sure we can get rid of the file
        @chmod($path, 0777);

        if (@unlink($path)) {
            return;
        }

        // Failed to delete, reset the permissions to 644 for safety
        @chmod($path, 0644);

        $this->logger->error(
            'UPDATE ERROR: '.$this->translator->trans('mautic.core.update.error.removing.file', ['%path%' => $file])
        );
    }
}
