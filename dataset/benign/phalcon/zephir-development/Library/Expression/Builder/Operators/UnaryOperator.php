<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\Expression\Builder\Operators;

use Zephir\Expression\Builder\AbstractBuilder;

/**
 * UnaryOperator.
 *
 * Allows to manually build a unary operator AST node
 */
class UnaryOperator extends AbstractOperator
{
    // y = &a
    const OPERATOR_REFERENCE = 'reference';

    // y = !a
    const OPERATOR_NOT = 'not';

    // y = ~a
    const OPERATOR_BITWISE_NOT = 'bitwise_not';

    // y = -a
    const OPERATOR_MINUS = 'minus';

    // y = +a
    const OPERATOR_PLUS = 'plus';

    // y = isset a
    const OPERATOR_ISSET = 'isset';

    // y = require a
    const OPERATOR_REQUIRE = 'require';

    // y = clone a
    const OPERATOR_CLONE = 'clone';

    // y = empty a
    const OPERATOR_EMPTY = 'empty';

    // y = likely a
    const OPERATOR_LIKELY = 'likely';

    // y = unlikely a
    const OPERATOR_UNLIKELY = 'unlikely';

    // y = list a
    const OPERATOR_LIST = 'list';

    // y = typeof a
    const OPERATOR_TYPEOF = 'typeof';

    private $operator;
    private $expression;

    /**
     * @param null                 $operator
     * @param AbstractBuilder|null $expression
     */
    public function __construct($operator = null, AbstractBuilder $expression = null)
    {
        if (null !== $operator) {
            $this->setOperator($operator);
        }

        if (null !== $expression) {
            $this->setExpression($expression);
        }
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param $operator
     *
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param AbstractBuilder $expression
     *
     * @return $this
     */
    public function setExpression(AbstractBuilder $expression)
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function preBuild()
    {
        return [
            'type' => $this->getOperator(),
            'left' => $this->getExpression(),
        ];
    }
}
