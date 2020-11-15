<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeCollector\NodeCollector\ParsedClassConstFetchNodeCollector;
use Rector\NodeCollector\NodeCollector\ParsedFunctionLikeNodeCollector;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeCollector\NodeCollector\ParsedPropertyFetchNodeCollector;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ParsedFunctionLikeNodeCollector
     */
    private $parsedFunctionLikeNodeCollector;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    /**
     * @var ParsedPropertyFetchNodeCollector
     */
    private $parsedPropertyFetchNodeCollector;

    /**
     * @var ParsedClassConstFetchNodeCollector
     */
    private $parsedClassConstFetchNodeCollector;

    public function __construct(
        ParsedNodeCollector $parsedNodeCollector,
        ParsedFunctionLikeNodeCollector $parsedFunctionLikeNodeCollector,
        ParsedPropertyFetchNodeCollector $parsedPropertyFetchNodeCollector,
        ParsedClassConstFetchNodeCollector $parsedClassConstFetchNodeCollector
    ) {
        $this->parsedFunctionLikeNodeCollector = $parsedFunctionLikeNodeCollector;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->parsedPropertyFetchNodeCollector = $parsedPropertyFetchNodeCollector;
        $this->parsedClassConstFetchNodeCollector = $parsedClassConstFetchNodeCollector;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($this->parsedNodeCollector->isCollectableNode($node)) {
            $this->parsedNodeCollector->collect($node);
        }

        $this->parsedFunctionLikeNodeCollector->collect($node);
        $this->parsedPropertyFetchNodeCollector->collect($node);
        $this->parsedClassConstFetchNodeCollector->collect($node);
    }
}
