<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Rector\Core\Exception\FileSystem\FileNotFoundException;

final class FileGuard
{
    public function ensureFileExists(string $file, string $location): void
    {
        if (is_file($file) && file_exists($file)) {
            return;
        }

        throw new FileNotFoundException(sprintf('File "%s" not found in "%s".', $file, $location));
    }
}
