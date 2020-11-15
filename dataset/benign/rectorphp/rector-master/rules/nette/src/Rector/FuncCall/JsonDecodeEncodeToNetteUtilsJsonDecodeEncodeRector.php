<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Nette\Tests\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRectorTest
 */
final class JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes json_encode()/json_decode() to safer and more verbose Nette\Utils\Json::encode()/decode() calls',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    public function decodeJson(string $jsonString)
    {
        $stdClass = json_decode($jsonString);

        $array = json_decode($jsonString, true);
        $array = json_decode($jsonString, false);
    }

    public function encodeJson(array $data)
    {
        $jsonString = json_encode($data);

        $prettyJsonString = json_encode($data, JSON_PRETTY_PRINT);
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeClass
{
    public function decodeJson(string $jsonString)
    {
        $stdClass = \Nette\Utils\Json::decode($jsonString);

        $array = \Nette\Utils\Json::decode($jsonString, \Nette\Utils\Json::FORCE_ARRAY);
        $array = \Nette\Utils\Json::decode($jsonString);
    }

    public function encodeJson(array $data)
    {
        $jsonString = \Nette\Utils\Json::encode($data);

        $prettyJsonString = \Nette\Utils\Json::encode($data, \Nette\Utils\Json::PRETTY);
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isName($node, 'json_encode')) {
            return $this->refactorJsonEncode($node);
        }

        if ($this->isName($node, 'json_decode')) {
            return $this->refactorJsonDecode($node);
        }

        return null;
    }

    private function refactorJsonEncode(FuncCall $funcCall): StaticCall
    {
        $args = $funcCall->args;
        if (isset($args[1])) {
            $secondArgumentValue = $args[1]->value;

            if ($this->isName($secondArgumentValue, 'JSON_PRETTY_PRINT')) {
                $prettyClassConstant = $this->createClassConstFetch('Nette\Utils\Json', 'PRETTY');
                $args[1] = new Arg($prettyClassConstant);
            }
        }

        return $this->createStaticCall('Nette\Utils\Json', 'encode', $args);
    }

    private function refactorJsonDecode(FuncCall $funcCall): StaticCall
    {
        $args = $funcCall->args;

        if (isset($args[1])) {
            $secondArgumentValue = $args[1]->value;

            if ($this->isFalse($secondArgumentValue)) {
                unset($args[1]);
            } elseif ($this->isTrue($secondArgumentValue)) {
                $forceArrayClassConstant = $this->createClassConstFetch('Nette\Utils\Json', 'FORCE_ARRAY');
                $args[1] = new Arg($forceArrayClassConstant);
            }
        }

        return $this->createStaticCall('Nette\Utils\Json', 'decode', $args);
    }
}
