<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\ValueObject;

use Nette\Utils\Strings;
use Rector\Core\Util\StaticRectorStrings;

final class Configuration
{
    /**
     * @var string
     */
    private $package;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $codeBefore;

    /**
     * @var string
     */
    private $codeAfter;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string|null
     */
    private $setConfig;

    /**
     * @var bool
     */
    private $isPhpSnippet = false;

    /**
     * @var string|null
     */
    private $extraFileContent;

    /**
     * @var string|null
     */
    private $extraFileName;

    /**
     * @var string[]
     */
    private $nodeTypes = [];

    /**
     * @var string[]
     */
    private $source = [];

    /**
     * @param string[] $nodeTypes
     * @param string[] $source
     */
    public function __construct(
        string $package,
        string $name,
        string $category,
        array $nodeTypes,
        string $description,
        string $codeBefore,
        string $codeAfter,
        ?string $extraFileContent = null,
        ?string $extraFileName = null,
        array $source,
        ?string $setConfig,
        bool $isPhpSnippet
    ) {
        $this->package = $package;
        $this->setName($name);
        $this->category = $category;
        $this->nodeTypes = $nodeTypes;
        $this->codeBefore = $codeBefore;
        $this->codeAfter = $codeAfter;
        $this->description = $description;
        $this->source = $source;
        $this->setConfig = $setConfig;
        $this->isPhpSnippet = $isPhpSnippet;
        $this->extraFileContent = $extraFileContent;
        $this->extraFileName = $extraFileName;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getPackageDirectory(): string
    {
        // special cases
        if ($this->package === 'PHPUnit') {
            return 'phpunit';
        }

        return StaticRectorStrings::camelCaseToDashes($this->package);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return $this->nodeTypes;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCodeBefore(): string
    {
        return $this->codeBefore;
    }

    public function getCodeAfter(): string
    {
        return $this->codeAfter;
    }

    /**
     * @return string[]
     */
    public function getSource(): array
    {
        return $this->source;
    }

    public function getSetConfig(): ?string
    {
        return $this->setConfig;
    }

    public function isPhpSnippet(): bool
    {
        return $this->isPhpSnippet;
    }

    public function getExtraFileContent(): ?string
    {
        return $this->extraFileContent;
    }

    public function getExtraFileName(): ?string
    {
        return $this->extraFileName;
    }

    private function setName(string $name): void
    {
        if (! Strings::endsWith($name, 'Rector')) {
            $name .= 'Rector';
        }

        $this->name = $name;
    }
}
