<?php

declare(strict_types=1);

namespace Rector\RectorGenerator;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\RectorGenerator\ValueObject\Configuration;

final class TemplateVariablesFactory
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return mixed[]
     */
    public function createFromConfiguration(Configuration $configuration): array
    {
        $data = [
            '_Package_' => $configuration->getPackage(),
            '_package_' => $configuration->getPackageDirectory(),
            '_Category_' => $configuration->getCategory(),
            '_Description_' => $configuration->getDescription(),
            '_Name_' => $configuration->getName(),
            '_CodeBefore_' => trim($configuration->getCodeBefore()) . PHP_EOL,
            '_CodeBeforeExample_' => $this->createCodeForDefinition($configuration->getCodeBefore()),
            '_CodeAfter_' => trim($configuration->getCodeAfter()) . PHP_EOL,
            '_CodeAfterExample_' => $this->createCodeForDefinition($configuration->getCodeAfter()),
            '_Source_' => $this->createSourceDocBlock($configuration->getSource()),
        ];

        if ($configuration->getExtraFileContent() !== null && $configuration->getExtraFileName() !== null) {
            $data['_ExtraFileName_'] = $configuration->getExtraFileName();
            $data['_ExtraFileContent_'] = trim($configuration->getExtraFileContent()) . PHP_EOL;
            $data['_ExtraFileContentExample_'] = $this->createCodeForDefinition($configuration->getExtraFileContent());
        }

        $data['_NodeTypes_Php_'] = $this->createNodeTypePhp($configuration);
        $data['_NodeTypes_Doc_'] = '\\' . implode('|\\', $configuration->getNodeTypes());

        return $data;
    }

    private function createCodeForDefinition(string $code): string
    {
        if (Strings::contains($code, PHP_EOL)) {
            // multi lines
            return sprintf("<<<'PHP'%s%s%sPHP%s", PHP_EOL, $code, PHP_EOL, PHP_EOL);
        }

        // single line
        return "'" . str_replace("'", '"', $code) . "'";
    }

    /**
     * @param string[] $source
     */
    private function createSourceDocBlock(array $source): string
    {
        if ($source === []) {
            return '';
        }

        $sourceAsString = '';
        foreach ($source as $singleSource) {
            $sourceAsString .= ' * @see ' . $singleSource . PHP_EOL;
        }

        $sourceAsString .= ' *';

        return rtrim($sourceAsString);
    }

    private function createNodeTypePhp(Configuration $configuration): string
    {
        $arrayNodes = [];
        foreach ($configuration->getNodeTypes() as $nodeType) {
            $classConstFetchNode = new ClassConstFetch(new FullyQualified($nodeType), 'class');
            $arrayNodes[] = new ArrayItem($classConstFetchNode);
        }

        return $this->betterStandardPrinter->print(new Array_($arrayNodes));
    }
}
