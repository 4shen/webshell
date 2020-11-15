<?php

declare(strict_types=1);

namespace Rector\NodeNestingScope\ValueObject;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;

final class ControlStructure
{
    /**
     * @var class-string[]
     */
    public const BREAKING_SCOPE_NODE_TYPES = [
        For_::class,
        Foreach_::class,
        If_::class,
        While_::class,
        Do_::class,
        Else_::class,
        ElseIf_::class,
        Catch_::class,
        Case_::class,
        FunctionLike::class,
    ];
}
