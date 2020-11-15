<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @see https://symfony.com/doc/current/components/translation/usage.html#message-placeholders
 * @see https://github.com/Kdyby/Translation/blob/master/docs/en/index.md#placeholders
 * https://github.com/Kdyby/Translation/blob/6b0721c767a7be7f15b2fb13c529bea8536230aa/src/Translator.php#L172
 * @see \Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\WrapTransParameterNameRectorTest
 */
final class WrapTransParameterNameRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds %% to placeholder name of trans() method if missing', [
            new CodeSample(
                <<<'PHP'
use Symfony\Component\Translation\Translator;

final class SomeController
{
    public function run()
    {
        $translator = new Translator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['name' => $name]
        );
    }
}
PHP
                ,
                <<<'PHP'
use Symfony\Component\Translation\Translator;

final class SomeController
{
    public function run()
    {
        $translator = new Translator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['%name%' => $name]
        );
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->var, TranslatorInterface::class)) {
            return null;
        }

        if (! $this->isName($node->name, 'trans')) {
            return null;
        }

        if (count($node->args) < 2) {
            return null;
        }

        if (! $node->args[1]->value instanceof Array_) {
            return null;
        }

        /** @var Array_ $parametersArrayNode */
        $parametersArrayNode = $node->args[1]->value;

        foreach ($parametersArrayNode->items as $arrayItem) {
            if (! $arrayItem->key instanceof String_) {
                continue;
            }

            if (Strings::match($arrayItem->key->value, '#%(.*?)%#')) {
                continue;
            }

            $arrayItem->key = new String_('%' . $arrayItem->key->value . '%');
        }

        return $node;
    }
}
