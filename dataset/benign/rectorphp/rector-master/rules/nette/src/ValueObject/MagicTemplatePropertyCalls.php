<?php

declare(strict_types=1);

namespace Rector\Nette\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;

final class MagicTemplatePropertyCalls
{
    /**
     * @var Node[]
     */
    private $nodesToRemove = [];

    /**
     * @var Node[]
     */
    private $templateVariables = [];

    /**
     * @var Expr|null
     */
    private $templateFileExpr;

    /**
     * @param Node[] $templateVariables
     * @param Node[] $nodesToRemove
     */
    public function __construct(?Expr $templateFileExpr, array $templateVariables, array $nodesToRemove)
    {
        $this->templateFileExpr = $templateFileExpr;
        $this->templateVariables = $templateVariables;
        $this->nodesToRemove = $nodesToRemove;
    }

    public function getTemplateFileExpr(): ?Expr
    {
        return $this->templateFileExpr;
    }

    /**
     * @return Node[]
     */
    public function getTemplateVariables(): array
    {
        return $this->templateVariables;
    }

    /**
     * @return Node[]
     */
    public function getNodesToRemove(): array
    {
        return $this->nodesToRemove;
    }
}
