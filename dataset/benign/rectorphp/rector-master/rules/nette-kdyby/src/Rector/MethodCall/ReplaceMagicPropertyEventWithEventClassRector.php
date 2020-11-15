<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\DataProvider\EventAndListenerTreeProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\ReplaceMagicPropertyEventWithEventClassRectorTest
 */
final class ReplaceMagicPropertyEventWithEventClassRector extends AbstractRector
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var EventAndListenerTreeProvider
     */
    private $eventAndListenerTreeProvider;

    public function __construct(
        ClassNaming $classNaming,
        EventAndListenerTreeProvider $eventAndListenerTreeProvider
    ) {
        $this->classNaming = $classNaming;
        $this->eventAndListenerTreeProvider = $eventAndListenerTreeProvider;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change $onProperty magic call with event disptacher and class dispatch', [
            new CodeSample(
                <<<'PHP'
final class FileManager
{
    public $onUpload;

    public function run(User $user)
    {
        $this->onUpload($user);
    }
}
PHP
,
                <<<'PHP'
final class FileManager
{
    use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run(User $user)
    {
        $onFileManagerUploadEvent = new FileManagerUploadEvent($user);
        $this->eventDispatcher->dispatch($onFileManagerUploadEvent);
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
        // 1. is onProperty? call
        $eventAndListenerTree = $this->eventAndListenerTreeProvider->matchMethodCall($node);
        if ($eventAndListenerTree === null) {
            return null;
        }

        // 2. guess event name
        $eventClassName = $eventAndListenerTree->getEventClassName();

        // 3. create new event class with args
        $eventClassInNamespace = $eventAndListenerTree->getEventClassInNamespace();
        $this->printNodesToFilePath($eventClassInNamespace, $eventAndListenerTree->getEventFileLocation());

        // 4. ad dispatch method call
        $dispatchMethodCall = $eventAndListenerTree->getEventDispatcherDispatchMethodCall();
        $this->addNodeAfterNode($dispatchMethodCall, $node);

        // 5. return event adding
        // add event dispatcher dependency if needed
        $assign = $this->createEventInstanceAssign($eventClassName, $node);

        /** @var Class_ $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        $this->addPropertyToClass(
            $class,
            new FullyQualifiedObjectType(EventDispatcherInterface::class),
            'eventDispatcher'
        );

        // 6. remove property
        if ($eventAndListenerTree->getOnMagicProperty() !== null) {
            $this->removeNode($eventAndListenerTree->getOnMagicProperty());
        }

        return $assign;
    }

    private function createEventInstanceAssign(string $eventClassName, MethodCall $methodCall): Assign
    {
        $shortEventClassName = $this->classNaming->getVariableName($eventClassName);

        $new = new New_(new FullyQualified($eventClassName));

        if ($methodCall->args) {
            $new->args = $methodCall->args;
        }

        return new Assign(new Variable($shortEventClassName), $new);
    }
}
