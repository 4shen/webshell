<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\ValueObject\StartEndValueObject;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;

final class PhpDocInfoFactory
{
    /**
     * @var PhpDocParser
     */
    private $phpDocParser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var TypeComparator
     */
    private $typeComparator;

    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    /**
     * @var ParamPhpDocNodeFactory
     */
    private $paramPhpDocNodeFactory;

    public function __construct(
        PhpDocParser $phpDocParser,
        Lexer $lexer,
        CurrentNodeProvider $currentNodeProvider,
        StaticTypeMapper $staticTypeMapper,
        TypeComparator $typeComparator,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        ParamPhpDocNodeFactory $paramPhpDocNodeFactory
    ) {
        $this->phpDocParser = $phpDocParser;
        $this->lexer = $lexer;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->paramPhpDocNodeFactory = $paramPhpDocNodeFactory;
    }

    public function createFromNode(Node $node): ?PhpDocInfo
    {
        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        if ($node->getDocComment() === null) {
            if ($node->getComments() !== []) {
                return null;
            }

            // create empty node
            $content = '';
            $tokens = [];
            $phpDocNode = new AttributeAwarePhpDocNode([]);
        } else {
            $content = $node->getDocComment()->getText();
            $tokens = $this->lexer->tokenize($content);
            $phpDocNode = $this->parseTokensToPhpDocNode($tokens);
            $this->setPositionOfLastToken($phpDocNode);
        }

        return $this->createFromPhpDocNode($phpDocNode, $content, $tokens, $node);
    }

    public function createEmpty(Node $node): PhpDocInfo
    {
        /** needed for @see PhpDocNodeFactoryInterface */
        $this->currentNodeProvider->setNode($node);

        $phpDocNode = new AttributeAwarePhpDocNode([]);

        return $this->createFromPhpDocNode($phpDocNode, '', [], $node);
    }

    private function parseTokensToPhpDocNode(array $tokens): AttributeAwarePhpDocNode
    {
        $tokenIterator = new TokenIterator($tokens);

        return $this->phpDocParser->parse($tokenIterator);
    }

    /**
     * Needed for printing
     */
    private function setPositionOfLastToken(AttributeAwarePhpDocNode $attributeAwarePhpDocNode): void
    {
        if ($attributeAwarePhpDocNode->children === []) {
            return;
        }

        $phpDocChildNodes = $attributeAwarePhpDocNode->children;
        /** @var AttributeAwareNodeInterface $lastChildNode */
        $lastChildNode = array_pop($phpDocChildNodes);

        /** @var StartEndValueObject $startEndValueObject */
        $startEndValueObject = $lastChildNode->getAttribute(Attribute::START_END);

        if ($startEndValueObject !== null) {
            $attributeAwarePhpDocNode->setAttribute(Attribute::LAST_TOKEN_POSITION, $startEndValueObject->getEnd());
        }
    }

    private function createFromPhpDocNode(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        string $content,
        array $tokens,
        Node $node
    ): PhpDocInfo {
        /** @var AttributeAwarePhpDocNode $attributeAwarePhpDocNode */
        $attributeAwarePhpDocNode = $this->attributeAwareNodeFactory->createFromNode(
            $attributeAwarePhpDocNode,
            $content
        );

        $phpDocInfo = new PhpDocInfo(
            $attributeAwarePhpDocNode,
            $tokens,
            $content,
            $this->staticTypeMapper,
            $node,
            $this->typeComparator,
            $this->paramPhpDocNodeFactory
        );
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $phpDocInfo;
    }
}
