<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\Class_;

use Nette\Application\IPresenter;
use Nette\Application\UI\Form;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://doc.nette.org/en/2.4/forms
 * ↓
 * @see https://symfony.com/doc/current/forms.html
 *
 * @see https://github.com/nette/forms/blob/master/src/Forms/Container.php
 * ↓
 * @see https://github.com/symfony/symfony/tree/master/src/Symfony/Component/Form/Extension/Core/Type
 * @see \Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector\NetteFormToSymfonyFormRectorTest
 */
final class NetteFormToSymfonyFormRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const ADD_METHOD_TO_FORM_TYPE = [
        'addText' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
        'addPassword' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
        'addTextArea' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
        'addEmail' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
        'addInteger' => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
        'addHidden' => 'Symfony\Component\Form\Extension\Core\Type\HiddenType',
        // https://symfony.com/doc/current/reference/forms/types/checkbox.html
        'addCheckbox' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',

        'addUpload' => 'Symfony\Component\Form\Extension\Core\Type\FileType',
        'addImage' => 'Symfony\Component\Form\Extension\Core\Type\FileType',
        'addMultiUpload' => 'Symfony\Component\Form\Extension\Core\Type\FileType',

        // https://symfony.com/doc/current/reference/forms/types/choice.html#select-tag-checkboxes-or-radio-buttons
        'addSelect' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        'addRadioList' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        'addCheckboxList' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        'addMultiSelect' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',

        'addSubmit' => 'Symfony\Component\Form\Extension\Core\Type\SubmitType',
        'addButton' => 'Symfony\Component\Form\Extension\Core\Type\ButtonType',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate Nette\Forms in Presenter to Symfony', [
            new CodeSample(
                <<<'PHP'
use Nette\Application\UI;

class SomePresenter extends UI\Presenter
{
    public function someAction()
    {
        $form = new UI\Form;
        $form->addText('name', 'Name:');
        $form->addPassword('password', 'Password:');
        $form->addSubmit('login', 'Sign up');
    }
}
PHP
                ,
                <<<'PHP'
use Nette\Application\UI;

class SomePresenter extends UI\Presenter
{
    public function someAction()
    {
        $form = $this->createFormBuilder();
        $form->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
            'label' => 'Name:'
        ]);
        $form->add('password', \Symfony\Component\Form\Extension\Core\Type\PasswordType::class, [
            'label' => 'Password:'
        ]);
        $form->add('login', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
            'label' => 'Sign up'
        ]);
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
        return [New_::class, MethodCall::class];
    }

    /**
     * @param New_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return null;
        }

        if (! $this->isObjectType($classNode, IPresenter::class)) {
            return null;
        }

        if ($node instanceof New_) {
            return $this->processNew($node);
        }

        /** @var MethodCall $node */
        if (! $this->isObjectType($node->var, Form::class)) {
            return null;
        }

        foreach (self::ADD_METHOD_TO_FORM_TYPE as $method => $classType) {
            if (! $this->isName($node->name, $method)) {
                continue;
            }

            $this->processAddMethod($node, $method, $classType);
        }

        return $node;
    }

    private function processNew(New_ $new): ?MethodCall
    {
        if (! $this->isName($new->class, Form::class)) {
            return null;
        }

        return $this->createMethodCall('this', 'createFormBuilder');
    }

    private function processAddMethod(MethodCall $methodCall, string $method, string $classType): void
    {
        $methodCall->name = new Identifier('add');

        // remove unused params
        if ($method === 'addText') {
            unset($methodCall->args[3], $methodCall->args[4]);
        }

        // has label
        $optionsArray = new Array_();
        if (isset($methodCall->args[1])) {
            $optionsArray->items[] = new ArrayItem($methodCall->args[1]->value, new String_('label'));
        }

        $this->addChoiceTypeOptions($method, $optionsArray);
        $this->addMultiFileTypeOptions($method, $optionsArray);

        $methodCall->args[1] = new Arg($this->createClassConstantReference($classType));

        if (count($optionsArray->items) > 0) {
            $methodCall->args[2] = new Arg($optionsArray);
        }
    }

    private function addChoiceTypeOptions(string $method, Array_ $optionsArray): void
    {
        if ($method === 'addSelect') {
            $expanded = false;
            $multiple = false;
        } elseif ($method === 'addRadioList') {
            $expanded = true;
            $multiple = false;
        } elseif ($method === 'addCheckboxList') {
            $expanded = true;
            $multiple = true;
        } elseif ($method === 'addMultiSelect') {
            $expanded = false;
            $multiple = true;
        } else {
            return;
        }

        $optionsArray->items[] = new ArrayItem(
            $expanded ? $this->createTrue() : $this->createFalse(),
            new String_('expanded')
        );

        $optionsArray->items[] = new ArrayItem(
            $multiple ? $this->createTrue() : $this->createFalse(),
            new String_('multiple')
        );
    }

    private function addMultiFileTypeOptions(string $method, Array_ $optionsArray): void
    {
        if ($method !== 'addMultiUpload') {
            return;
        }

        $optionsArray->items[] = new ArrayItem($this->createTrue(), new String_('multiple'));
    }
}
