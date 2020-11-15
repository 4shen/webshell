<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Contract;

use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

interface FileSystemRectorInterface extends RectorInterface
{
    public function refactor(SmartFileInfo $smartFileInfo): void;
}
