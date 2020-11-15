<?php

namespace Wallabag\CoreBundle\Operator\Doctrine;

/**
 * Provides a "matches" operator used for tagging rules.
 *
 * It asserts that a given pattern is contained in a subject, in a
 * case-insensitive way.
 *
 * This operator will be used to compile tagging rules in DQL, usable
 * by Doctrine ORM.
 * It's registered in RulerZ using a service (wallabag.operator.doctrine.matches);
 */
class Matches
{
    public function __invoke($subject, $pattern)
    {
        if ("'" === $pattern[0]) {
            $pattern = sprintf("'%%%s%%'", substr($pattern, 1, -1));
        }

        return sprintf('UPPER(%s) LIKE UPPER(%s)', $subject, $pattern);
    }
}
