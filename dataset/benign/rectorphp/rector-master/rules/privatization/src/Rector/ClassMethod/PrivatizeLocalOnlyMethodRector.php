<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeAnalyzer\ClassMethodExternalCallNodeAnalyzer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodVisibilityVendorLockResolver;

/**
 * @see \Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\PrivatizeLocalOnlyMethodRectorTest
 */
final class PrivatizeLocalOnlyMethodRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var ClassMethodVisibilityVendorLockResolver
     */
    private $classMethodVisibilityVendorLockResolver;

    /**
     * @var ClassMethodExternalCallNodeAnalyzer
     */
    private $classMethodExternalCallNodeAnalyzer;

    public function __construct(
        ClassMethodVisibilityVendorLockResolver $classMethodVisibilityVendorLockResolver,
        ClassMethodExternalCallNodeAnalyzer $classMethodExternalCallNodeAnalyzer
    ) {
        $this->classMethodVisibilityVendorLockResolver = $classMethodVisibilityVendorLockResolver;
        $this->classMethodExternalCallNodeAnalyzer = $classMethodExternalCallNodeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Privatize local-only use methods', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    public function useMe()
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    private function useMe()
    {
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->classMethodExternalCallNodeAnalyzer->hasExternalCall($node)) {
            return null;
        }

        $this->makePrivate($node);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return true;
        }

        if ($this->isAnonymousClass($class)) {
            return true;
        }

        if ($this->isObjectType($class, TestCase::class)) {
            return true;
        }

        if ($this->isDoctrineEntityClass($class)) {
            return true;
        }

        if ($this->isControllerAction($class, $classMethod)) {
            return true;
        }

        if ($this->shouldSkipClassMethod($classMethod)) {
            return true;
        }

        // is interface required method? skip it
        if ($this->classMethodVisibilityVendorLockResolver->isParentLockedMethod($classMethod)) {
            return true;
        }

        if ($this->classMethodVisibilityVendorLockResolver->isChildLockedMethod($classMethod)) {
            return true;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        return $phpDocInfo->hasByNames(['api', 'required']);
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($classMethod->isPrivate()) {
            return true;
        }

        if ($classMethod->isAbstract()) {
            return true;
        }

        // skip for now
        if ($classMethod->isStatic()) {
            return true;
        }

        if ($this->isName($classMethod, '__*')) {
            return true;
        }

        // possibly container service factories
        return $this->isNames($classMethod, ['create', 'create*']);
    }

    private function isControllerAction(Class_ $class, ClassMethod $classMethod): bool
    {
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        if (! Strings::match($className, '#(Controller|Presenter)$#')) {
            return false;
        }

        $classMethodName = $this->getName($classMethod);

        if ((bool) Strings::match($classMethodName, '#^(render|action|handle|inject)#')) {
            return true;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        return $phpDocInfo->hasByName('inject');
    }
}
