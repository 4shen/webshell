<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Application;

use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorError
{
    /**
     * @var int|null
     */
    private $line;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string|null
     */
    private $rectorClass;

    /**
     * @var SmartFileInfo
     */
    private $fileInfo;

    public function __construct(
        SmartFileInfo $smartFileInfo,
        string $message,
        ?int $line = null,
        ?string $rectorClass = null
    ) {
        $this->fileInfo = $smartFileInfo;
        $this->message = $message;
        $this->line = $line;
        $this->rectorClass = $rectorClass;
    }

    public function getFileInfo(): SmartFileInfo
    {
        return $this->fileInfo;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getRectorClass(): ?string
    {
        return $this->rectorClass;
    }
}
