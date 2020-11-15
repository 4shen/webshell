<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        https://www.mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Tests\Unit\Update\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractStepTest extends TestCase
{
    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * @var MockObject|InputInterface
     */
    protected $input;

    /**
     * @var MockObject|OutputInterface
     */
    protected $output;

    protected function setUp()
    {
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
        $this->output->method('getFormatter')
            ->willReturn($this->createMock(OutputFormatterInterface::class));

        $this->progressBar = new ProgressBar($this->output);
    }
}
