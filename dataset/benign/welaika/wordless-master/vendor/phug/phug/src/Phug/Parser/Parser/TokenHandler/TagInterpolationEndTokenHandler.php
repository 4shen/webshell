<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\TagInterpolationEndToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class TagInterpolationEndTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof TagInterpolationEndToken)) {
            throw new \RuntimeException(
                'You can only pass tag interpolation end tokens to this token handler'
            );
        }

        $state->popInterpolationNode();
        $node = $state->getCurrentNode();
        $nodes = $state->getInterpolationStack()->offsetGet($token);
        $state->setCurrentNode($nodes->currentNode);
        $state->setParentNode($nodes->parentNode);
        if ($node) {
            $state->getInterpolationStack()->attach($node, $token);
            $state->append($node);
        }
        $state->store();
        $state->setCurrentNode($nodes->currentNode);
    }
}
