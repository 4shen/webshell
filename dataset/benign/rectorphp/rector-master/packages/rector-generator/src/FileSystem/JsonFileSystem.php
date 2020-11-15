<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;

final class JsonFileSystem
{
    /**
     * @var JsonStringFormatter
     */
    private $jsonStringFormatter;

    public function __construct(JsonStringFormatter $jsonStringFormatter)
    {
        $this->jsonStringFormatter = $jsonStringFormatter;
    }

    /**
     * @return mixed[]
     */
    public function loadFileToJson(string $filePath): array
    {
        $fileContent = FileSystem::read($filePath);
        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }

    /**
     * @param mixed[] $json
     */
    public function saveJsonToFile(string $filePath, array $json): void
    {
        $content = Json::encode($json, Json::PRETTY);
        $content = $this->jsonStringFormatter->inlineSections($content, ['keywords', 'bin']);
        $content = $this->jsonStringFormatter->inlineAuthors($content);

        // make sure there is newline in the end
        $content = trim($content) . PHP_EOL;

        FileSystem::write($filePath, $content);
    }
}
