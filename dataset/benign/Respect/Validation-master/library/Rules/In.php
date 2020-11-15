<?php

/*
 * This file is part of Respect/Validation.
 *
 * (c) Alexandre Gomes Gaigalas <alexandre@gaigalas.net>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

declare(strict_types=1);

namespace Respect\Validation\Rules;

use function in_array;
use function is_array;
use function mb_detect_encoding;
use function mb_stripos;
use function mb_strpos;

/**
 * Validates if the input can be found in a defined array or string.
 *
 * @author Alexandre Gomes Gaigalas <alexandre@gaigalas.net>
 * @author Danilo Benevides <danilobenevides01@gmail.com>
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
final class In extends AbstractRule
{
    /**
     * @var mixed[]|string
     */
    private $haystack;

    /**
     * @var bool
     */
    private $compareIdentical;

    /**
     * Initializes the rule with the haystack and optionally compareIdentical flag.
     *
     * @param mixed[]|string $haystack
     */
    public function __construct($haystack, bool $compareIdentical = false)
    {
        $this->haystack = $haystack;
        $this->compareIdentical = $compareIdentical;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($input): bool
    {
        if ($this->compareIdentical) {
            return $this->validateIdentical($input);
        }

        return $this->validateEquals($input);
    }

    /**
     * @param mixed $input
     */
    private function validateEquals($input): bool
    {
        if (is_array($this->haystack)) {
            return in_array($input, $this->haystack);
        }

        if ($input === null || $input === '') {
            return $input == $this->haystack;
        }

        $inputString = (string) $input;

        return mb_stripos($this->haystack, $inputString, 0, mb_detect_encoding($inputString)) !== false;
    }

    /**
     * @param mixed $input
     */
    private function validateIdentical($input): bool
    {
        if (is_array($this->haystack)) {
            return in_array($input, $this->haystack, true);
        }

        if ($input === null || $input === '') {
            return $input === $this->haystack;
        }

        $inputString = (string) $input;

        return mb_strpos($this->haystack, $inputString, 0, mb_detect_encoding($inputString)) !== false;
    }
}
