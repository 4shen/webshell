<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\Statements;

use function Zephir\add_slashes;
use Zephir\CompilationContext;
use Zephir\Detectors\ForValueUseDetector;
use Zephir\Exception\CompilerException;
use Zephir\Expression;
use Zephir\Expression\Builder\BuilderFactory;
use Zephir\FunctionCall;
use Zephir\Optimizers\EvalExpression;
use Zephir\StatementsBlock;
use Zephir\Variable;

/**
 * ForStatement.
 *
 * For statement
 */
class ForStatement extends StatementAbstract
{
    /**
     * Compiles a for statement that use a 'range' as expression.
     *
     * @param array              $exprRaw
     * @param CompilationContext $compilationContext
     *
     * @return bool
     */
    public function compileRange($exprRaw, CompilationContext $compilationContext)
    {
        if (!\count($exprRaw['parameters'])) {
            return false;
        }

        if (\count($exprRaw['parameters']) > 3) {
            return false;
        }

        $functionCall = new FunctionCall();
        $parameters = $functionCall->getResolvedParamsAsExpr($exprRaw['parameters'], $compilationContext, $exprRaw);

        if (2 != \count($parameters) && 3 != \count($parameters)) {
            throw new CompilerException('Wrong number of parameters', $this->statement['expr']);
        }

        if ('variable' != $parameters[0]->getType()) {
            if (!$parameters[0]->isIntCompatibleType()) {
                return false;
            }
        }

        if ('variable' != $parameters[1]->getType()) {
            if (!$parameters[1]->isIntCompatibleType()) {
                return false;
            }
        }

        $codePrinter = $compilationContext->codePrinter;
        $exprBuilder = BuilderFactory::getInstance();

        /*
         * Initialize 'key' variable
         */
        if (isset($this->statement['key'])) {
            /**
             * This variable is used to check if the loop is in its first iteration.
             */
            $keyVariable = $compilationContext->symbolTable->getTempVariableForWrite('long', $compilationContext, $this->statement);
            $keyVariable->increaseUses();
        }

        /**
         * This variable is used to check if the loop is in its first iteration.
         */
        $flagVariable = $compilationContext->symbolTable->getTempVariableForWrite('bool', $compilationContext, $this->statement);

        if ('variable' != $parameters[0]->getType()) {
            $tempVariable = $compilationContext->symbolTable->addTemp($parameters[0]->getType(), $compilationContext);
        } else {
            $rangeVariable = $compilationContext->symbolTable->getVariableForRead($parameters[0]->getCode(), $compilationContext, $this->statement['expr']);
            $tempVariable = $compilationContext->symbolTable->addTemp($rangeVariable->getType(), $compilationContext);
        }

        /*
         * Create a copy of the current value in the end of the range to avoid modify the range
         * inside the cycle
         */
        if ('variable' != $parameters[1]->getType()) {
            $upperBoundVariable = $compilationContext->symbolTable->getTempVariable($parameters[1]->getType(), $compilationContext);
        } else {
            $rangeVariable = $compilationContext->symbolTable->getVariableForRead($parameters[1]->getCode(), $compilationContext, $this->statement['expr']);
            $upperBoundVariable = $compilationContext->symbolTable->getTempVariable($rangeVariable->getType(), $compilationContext);
        }

        /**
         * Create an implicit 'let' operation to set the current value in the upper bound of the range.
         */
        $builderLet = $exprBuilder->statements()->let();
        $builderLet->setAssignments([$exprBuilder->operators()
            ->assignVariable($upperBoundVariable->getName(), $exprBuilder->literal($parameters[1]->getType(), $parameters[1]->getCode())
                ->setFile($this->statement['file'])
                ->setLine($this->statement['line'])
                ->setChar($this->statement['char']))
            ->setFile($this->statement['file'])
            ->setLine($this->statement['line'])
            ->setChar($this->statement['char']),
        ]);

        $statement = new LetStatement($builderLet->build());
        $statement->compile($compilationContext);

        if ($this->statement['reverse']) {
            /*
             * Create an implicit 'let' operation for the initialize expression
             */
            $builderLet->setAssignments([$exprBuilder->operators()
                ->assignVariable($tempVariable->getName(), $exprBuilder->variable($upperBoundVariable->getName())
                    ->setFile($this->statement['file'])
                    ->setLine($this->statement['line'])
                    ->setChar($this->statement['char']))
                ->setFile($this->statement['file'])
                ->setLine($this->statement['line'])
                ->setChar($this->statement['char']),
            ]);
            $statement = new LetStatement($builderLet->build());
        } else {
            /*
             * Create an implicit 'let' operation for the initialize expression
             */
            $builderLet->setAssignments([$exprBuilder->operators()
                ->assignVariable($tempVariable->getName(), $exprBuilder->literal($parameters[0]->getType(), $parameters[0]->getCode())
                    ->setFile($this->statement['file'])
                    ->setLine($this->statement['line'])
                    ->setChar($this->statement['char']))
                ->setFile($this->statement['file'])
                ->setLine($this->statement['line'])
                ->setChar($this->statement['char']),
            ]);
            $statement = new LetStatement($builderLet->build());
        }

        $statement->compile($compilationContext);

        /*
         * Initialize 'key' variable
         */
        if (isset($this->statement['key'])) {
            $codePrinter->output($keyVariable->getName().' = 0;');
        }
        $codePrinter->output($flagVariable->getName().' = 0;');

        if ($this->statement['reverse']) {
            $conditionExpr = [
                'type' => 'greater-equal',
                'left' => ['type' => 'variable', 'value' => $tempVariable->getName()],
                'right' => ['type' => $parameters[0]->getType(), 'value' => $parameters[0]->getCode()],
            ];
        } else {
            $conditionExpr = [
                'type' => 'less-equal',
                'left' => ['type' => 'variable', 'value' => $tempVariable->getName()],
                'right' => ['type' => 'variable', 'value' => $upperBoundVariable->getName()],
            ];
        }

        $expr = new EvalExpression();
        $condition = $expr->optimize($conditionExpr, $compilationContext);

        $codePrinter->output('if ('.$condition.') {');
        $codePrinter->increaseLevel();

        /*
         * Inside a cycle
         */
        ++$compilationContext->insideCycle;

        $codePrinter->output('while (1) {');
        $codePrinter->increaseLevel();

        $codePrinter->output('if ('.$flagVariable->getName().') {');

        $codePrinter->increaseLevel();

        if (isset($this->statement['key'])) {
            $codePrinter->output($keyVariable->getName().'++;');
        }

        if ($this->statement['reverse']) {
            if (!isset($parameters[2])) {
                $statement = new LetStatement([
                    'type' => 'let',
                    'assignments' => [
                        [
                            'assign-type' => 'decr',
                            'variable' => $tempVariable->getName(),
                            'file' => $this->statement['file'],
                            'line' => $this->statement['line'],
                            'char' => $this->statement['char'],
                        ],
                    ],
                ]);
            } else {
                $statement = new LetStatement([
                    'type' => 'let',
                    'assignments' => [
                        [
                            'assign-type' => 'variable',
                            'operator' => 'sub-assign',
                            'variable' => $tempVariable->getName(),
                            'expr' => [
                                'type' => $parameters[2]->getType(),
                                'value' => $parameters[2]->getCode(),
                                'file' => $this->statement['file'],
                                'line' => $this->statement['line'],
                                'char' => $this->statement['char'],
                            ],
                            'file' => $this->statement['file'],
                            'line' => $this->statement['line'],
                            'char' => $this->statement['char'],
                        ],
                    ],
                ]);
            }
        } else {
            if (!isset($parameters[2])) {
                $statement = new LetStatement([
                    'type' => 'let',
                    'assignments' => [
                        [
                            'assign-type' => 'incr',
                            'variable' => $tempVariable->getName(),
                            'file' => $this->statement['file'],
                            'line' => $this->statement['line'],
                            'char' => $this->statement['char'],
                        ],
                    ],
                ]);
            } else {
                $statement = new LetStatement([
                    'type' => 'let',
                    'assignments' => [
                        [
                            'assign-type' => 'variable',
                            'operator' => 'add-assign',
                            'variable' => $tempVariable->getName(),
                            'expr' => [
                                'type' => $parameters[2]->getType(),
                                'value' => $parameters[2]->getCode(),
                                'file' => $this->statement['file'],
                                'line' => $this->statement['line'],
                                'char' => $this->statement['char'],
                            ],
                            'file' => $this->statement['file'],
                            'line' => $this->statement['line'],
                            'char' => $this->statement['char'],
                        ],
                    ],
                ]);
            }
        }
        $statement->compile($compilationContext);

        /**
         * Multi-line conditions would need to be regenerated here.
         */
        $condition = $expr->optimize($conditionExpr, $compilationContext);
        $codePrinter->output('if (!('.$condition.')) {');
        $codePrinter->output("\t".'break;');
        $codePrinter->output('}');

        $codePrinter->decreaseLevel();

        $codePrinter->output('} else {');
        $codePrinter->output("\t".$flagVariable->getName().' = 1;');
        $codePrinter->output('}');

        /*
         * Initialize 'key' variable
         */
        if (isset($this->statement['key'])) {
            /*
             * Check for anonymous variables
             */
            if ('_' != $this->statement['key']) {
                $keyVariableName = $this->statement['key'];
            } else {
                $keyVariableName = $keyVariable->getName();
            }

            /**
             * Create an implicit 'let' operation, @TODO use a builder.
             */
            $statement = new LetStatement([
                'type' => 'let',
                'assignments' => [
                    [
                        'assign-type' => 'variable',
                        'variable' => $keyVariableName,
                        'operator' => 'assign',
                        'expr' => [
                            'type' => 'variable',
                            'value' => $keyVariable->getName(),
                            'file' => $this->statement['file'],
                            'line' => $this->statement['line'],
                            'char' => $this->statement['char'],
                        ],
                        'file' => $this->statement['file'],
                        'line' => $this->statement['line'],
                        'char' => $this->statement['char'],
                    ],
                ],
            ]);

            $statement->compile($compilationContext);
        }

        /*
         * Initialize 'value' variable
         */
        if (isset($this->statement['value'])) {
            /*
             * Check for anonymous variables
             */
            if ('_' != $this->statement['value']) {
                $valueVariable = $this->statement['value'];
            } else {
                $valueVariable = $tempVariable->getName();
            }

            /**
             * Create an implicit 'let' operation, @TODO use a builder.
             */
            $statement = new LetStatement([
                'type' => 'let',
                'assignments' => [
                    [
                        'assign-type' => 'variable',
                        'variable' => $valueVariable,
                        'operator' => 'assign',
                        'expr' => [
                            'type' => 'variable',
                            'value' => $tempVariable->getName(),
                            'file' => $this->statement['file'],
                            'line' => $this->statement['line'],
                            'char' => $this->statement['char'],
                        ],
                        'file' => $this->statement['file'],
                        'line' => $this->statement['line'],
                        'char' => $this->statement['char'],
                    ],
                ],
            ]);

            $statement->compile($compilationContext);
        }

        $codePrinter->decreaseLevel();

        /*
         * Compile statements in the 'for' block
         */
        if (isset($this->statement['statements'])) {
            $st = new StatementsBlock($this->statement['statements']);
            $st->isLoop(true);
            if (isset($this->statement['key'])) {
                $st->getMutateGatherer()->increaseMutations($this->statement['key']);
            }
            $st->getMutateGatherer()->increaseMutations($this->statement['value']);
            $st->compile($compilationContext);
        }

        /*
         * Restore the cycle counter
         */
        --$compilationContext->insideCycle;

        $codePrinter->output('}');

        $codePrinter->decreaseLevel();

        $codePrinter->output('}');
    }

    /**
     * Compiles a 'for' statement that use an 'iterator' as expression.
     *
     * @param array              $exprRaw
     * @param CompilationContext $compilationContext
     *
     * @return bool
     */
    public function compileIterator(array $exprRaw, CompilationContext $compilationContext)
    {
        $iteratorVariable = $compilationContext->symbolTable->getTempVariableForWrite('zend_object_iterator', $compilationContext);

        $compilationContext->headersManager->add('kernel/iterator');

        $codePrinter = $compilationContext->codePrinter;

        /**
         * Resolve first parameter of iterator(p).
         */
        $expr = new Expression($exprRaw['parameters'][0]['parameter']);
        $expr->setReadOnly(false);
        $expression = $expr->compile($compilationContext);

        if ('variable' != $expression->getType()) {
            throw new CompilerException('Unknown type: '.$expression->getType(), $exprRaw);
        }

        $exprVariable = $compilationContext->symbolTable->getVariableForRead($expression->getCode(), $compilationContext, $this->statement['expr']);
        switch ($exprVariable->getType()) {
            case 'variable':
                break;
        }

        /*
         * Initialize 'key' variable
         */
        if (isset($this->statement['key'])) {
            if ('_' != $this->statement['key']) {
                $keyVariable = $compilationContext->symbolTable->getVariableForWrite($this->statement['key'], $compilationContext, $this->statement['expr']);
                if ('variable' != $keyVariable->getType()) {
                    throw new CompilerException('Cannot use variable: '.$this->statement['key'].' type: '.$keyVariable->getType().' as key in hash traversal', $this->statement['expr']);
                }
            } else {
                /**
                 * Anonymous key variable.
                 */
                $keyVariable = $compilationContext->symbolTable->getTempVariableForWrite('variable', $compilationContext);
                $keyVariable->increaseUses();
            }

            $keyVariable->setMustInitNull(true);
            $keyVariable->setIsInitialized(true, $compilationContext);
            $keyVariable->setDynamicTypes('undefined');
        }

        /*
         * Initialize 'value' variable
         */
        if (isset($this->statement['value'])) {
            if ('_' != $this->statement['value']) {
                $variable = $compilationContext->symbolTable->getVariableForWrite($this->statement['value'], $compilationContext, $this->statement['expr']);
                if ('variable' != $variable->getType()) {
                    throw new CompilerException('Cannot use variable: '.$this->statement['value'].' type: '.$variable->getType().' as value in hash traversal', $this->statement['expr']);
                }
            } else {
                /**
                 * Anonymous value variable.
                 */
                $variable = $compilationContext->symbolTable->getTempVariableForWrite('variable', $compilationContext);
                $variable->increaseUses();
            }

            $variable->setMustInitNull(true);
            $variable->setIsInitialized(true, $compilationContext);
            $variable->setDynamicTypes('undefined');
        }

        /*
         * Variables are initialized in a different way inside cycle
         */
        ++$compilationContext->insideCycle;

        $codePrinter->output($iteratorVariable->getName().' = zephir_get_iterator('.$compilationContext->backend->getVariableCode($exprVariable).');');

        $codePrinter->output($iteratorVariable->getName().'->funcs->rewind('.$iteratorVariable->getName().');');
        $codePrinter->output('for (;'.$iteratorVariable->getName().'->funcs->valid('.$iteratorVariable->getName().') == SUCCESS && !EG(exception); '.$iteratorVariable->getName().'->funcs->move_forward('.$iteratorVariable->getName().')) {');

        if (isset($this->statement['key'])) {
            $compilationContext->symbolTable->mustGrownStack(true);
            $codePrinter->output("\t".'ZEPHIR_GET_IMKEY('.$this->statement['key'].', '.$iteratorVariable->getName().');');
        }

        if (isset($this->statement['value'])) {
            $compilationContext->symbolTable->mustGrownStack(true);
            $codePrinter->increaseLevel();
            $codePrinter->output('{');
            $codePrinter->increaseLevel();
            $compilationContext->backend->forStatementIterator($iteratorVariable, $variable, $compilationContext);
            $codePrinter->decreaseLevel();
            $codePrinter->output('}');
            $codePrinter->decreaseLevel();
        }

        /*
         * Compile statements in the 'for' block
         */
        if (isset($this->statement['statements'])) {
            $st = new StatementsBlock($this->statement['statements']);
            $st->isLoop(true);
            if (isset($this->statement['key'])) {
                $st->getMutateGatherer()->increaseMutations($this->statement['key']);
            }
            $st->getMutateGatherer()->increaseMutations($this->statement['value']);
            $st->compile($compilationContext);
        }

        /*
         * Restore the cycle counter
         */
        --$compilationContext->insideCycle;

        $codePrinter->output('}');

        $compilationContext->backend->destroyIterator($iteratorVariable, $compilationContext);
    }

    /**
     * Compiles traversing of string values
     * - Evaluated expression must be a string
     * - Every key must be an integer or compatible
     * - Every value must be a char/integer or compatible.
     *
     * @param array              $expression
     * @param CompilationContext $compilationContext
     * @param Variable           $exprVariable
     */
    public function compileStringTraverse($expression, CompilationContext $compilationContext, $exprVariable)
    {
        $codePrinter = $compilationContext->codePrinter;

        /*
         * Initialize 'key' variable
         */
        if (isset($this->statement['key'])) {
            if ('_' != $this->statement['key']) {
                $keyVariable = $compilationContext->symbolTable->getVariableForWrite($this->statement['key'], $compilationContext, $this->statement['expr']);
                switch ($keyVariable->getType()) {
                    case 'int':
                    case 'uint':
                    case 'long':
                    case 'ulong':
                    case 'char':
                    case 'uchar':
                        break;
                    default:
                        throw new CompilerException('Cannot use variable: '.$this->statement['key'].' type: '.$keyVariable->getType().' as key in string traversal', $this->statement['expr']);
                }
            } else {
                $keyVariable = $compilationContext->symbolTable->getTempVariableForWrite('int', $compilationContext);
                $keyVariable->increaseUses();
            }

            $keyVariable->setMustInitNull(true);
            $keyVariable->setIsInitialized(true, $compilationContext);
        }

        /*
         * Initialize 'value' variable
         */
        if (isset($this->statement['value'])) {
            if ('_' != $this->statement['value']) {
                $variable = $compilationContext->symbolTable->getVariableForWrite($this->statement['value'], $compilationContext, $this->statement['expr']);
                switch ($variable->getType()) {
                    case 'int':
                    case 'uint':
                    case 'long':
                    case 'ulong':
                    case 'char':
                    case 'uchar':
                        break;
                    default:
                        throw new CompilerException('Cannot use variable: '.$this->statement['value'].' type: '.$variable->getType().' as value in string traversal', $this->statement['expr']);
                }
            } else {
                $variable = $compilationContext->symbolTable->getTempVariableForWrite('char', $compilationContext);
                $variable->increaseUses();
            }

            $variable->setMustInitNull(true);
            $variable->setIsInitialized(true, $compilationContext);
        }

        $tempVariable = $compilationContext->symbolTable->addTemp('long', $compilationContext);

        /*
         * Create a temporary value to store the constant string
         */
        if ('string' == $expression->getType()) {
            $constantVariable = $compilationContext->symbolTable->getTempLocalVariableForWrite('variable', $compilationContext);

            $compilationContext->backend->assignString(
                $constantVariable,
                add_slashes($expression->getCode()),
                $compilationContext,
                true
            );

            $stringVariable = $constantVariable;
        } else {
            $stringVariable = $exprVariable;
        }

        $stringVariableCode = $compilationContext->backend->getVariableCode($stringVariable);
        if ($this->statement['reverse']) {
            $codePrinter->output('for ('.$tempVariable->getName().' = Z_STRLEN_P('.$stringVariableCode.'); '.$tempVariable->getName().' >= 0; '.$tempVariable->getName().'--) {');
        } else {
            $codePrinter->output('for ('.$tempVariable->getName().' = 0; '.$tempVariable->getName().' < Z_STRLEN_P('.$stringVariableCode.'); '.$tempVariable->getName().'++) {');
        }

        if (isset($this->statement['key'])) {
            $codePrinter->output("\t".$keyVariable->getName().' = '.$tempVariable->getName().'; ');
        }

        $compilationContext->headersManager->add('kernel/operators');
        $codePrinter->output("\t".$variable->getName().' = ZEPHIR_STRING_OFFSET('.$stringVariableCode.', '.$tempVariable->getName().');');

        /*
         * Variables are initialized in a different way inside cycle
         */
        ++$compilationContext->insideCycle;

        /*
         * Compile statements in the 'for' block
         */
        if (isset($this->statement['statements'])) {
            $st = new StatementsBlock($this->statement['statements']);
            $st->isLoop(true);
            if (isset($this->statement['key'])) {
                $st->getMutateGatherer()->increaseMutations($this->statement['key']);
            }
            $st->getMutateGatherer()->increaseMutations($this->statement['value']);
            $st->compile($compilationContext);
        }

        --$compilationContext->insideCycle;

        $codePrinter->output('}');
    }

    /**
     * Compiles traversing of hash values.
     *
     * - Evaluated expression must be a zval
     * - A key must be a zval
     * - A value must be a zval
     *
     * @param array              $expression
     * @param CompilationContext $compilationContext
     * @param Variable           $exprVariable
     */
    public function compileHashTraverse($expression, CompilationContext $compilationContext, Variable $exprVariable)
    {
        /*
         * Initialize 'key' variable
         */
        if (isset($this->statement['key'])) {
            if ('_' != $this->statement['key']) {
                $keyVariable = $compilationContext->symbolTable->getVariableForWrite($this->statement['key'], $compilationContext, $this->statement['expr']);
                if ('variable' != $keyVariable->getType()) {
                    throw new CompilerException('Cannot use variable: '.$this->statement['key'].' type: '.$keyVariable->getType().' as key in hash traversal', $this->statement['expr']);
                }
            } else {
                $keyVariable = $compilationContext->symbolTable->getTempVariableForWrite('variable', $compilationContext);
            }

            $keyVariable->setMustInitNull(true);
            $keyVariable->setIsInitialized(true, $compilationContext);
            $keyVariable->setDynamicTypes('undefined');
        }

        /*
         * Initialize 'value' variable
         */
        if (isset($this->statement['value'])) {
            if ('_' != $this->statement['value']) {
                $variable = $compilationContext->symbolTable->getVariableForWrite($this->statement['value'], $compilationContext, $this->statement['expr']);
                if ('variable' != $variable->getType()) {
                    throw new CompilerException('Cannot use variable: '.$this->statement['value'].' type: '.$variable->getType().' as value in hash traversal', $this->statement['expr']);
                }
            } else {
                $variable = $compilationContext->symbolTable->getTempVariableForWrite('variable', $compilationContext);
            }

            $variable->setMustInitNull(true);
            $variable->setIsInitialized(true, $compilationContext);
            $variable->increaseVariantIfNull();
            $variable->setDynamicTypes('undefined');
        }

        /*
         * Variables are initialized in a different way inside cycle
         */
        ++$compilationContext->insideCycle;

//         $compilationContext->headersManager->add('kernel/hash');

        $duplicateHash = '0';
        $duplicateKey = true;

        /**
         * We have to check if hashes are modified within the for's block.
         */
        $st = null;
        if (isset($this->statement['statements'])) {
            /**
             * Create the statements block here to obtain the last use line.
             */
            $st = new StatementsBlock($this->statement['statements']);

            $detector = new ForValueUseDetector();
            if ($detector->detect($exprVariable->getName(), $this->statement['statements'])) {
                $duplicateHash = '1';
            }

            /*
             * Detect if the key is modified or passed to an external scope
             */
            if (isset($this->statement['key'])) {
                if (!$keyVariable->isTemporal()) {
                    $detector->setDetectionFlags(ForValueUseDetector::DETECT_ALL);
                    if ($detector->detect($keyVariable->getName(), $this->statement['statements'])) {
                        $duplicateKey = true;
                    }
                }
            }
        }

        $compilationContext->backend->forStatement(
            $exprVariable,
            isset($this->statement['key']) ? $keyVariable : null,
            isset($this->statement['value']) ? $variable : null,
            $duplicateKey,
            $duplicateHash,
            $this->statement,
            $st,
            $compilationContext
        );

        /*
         * Restore the cycle counter
         */
        --$compilationContext->insideCycle;
    }

    /**
     * @param CompilationContext $compilationContext
     *
     * @throws CompilerException
     */
    public function compile(CompilationContext $compilationContext)
    {
        $exprRaw = $this->statement['expr'];

        /*
         * @TODO implement optimizers here
         */
        if ('fcall' == $exprRaw['type']) {
            if ('range' == $exprRaw['name']) {
                $status = $this->compileRange($exprRaw, $compilationContext);
                if (false !== $status) {
                    return;
                }
            }

            if ('iterator' == $exprRaw['name']) {
                $status = $this->compileIterator($exprRaw, $compilationContext);
                if (false !== $status) {
                    return;
                }
            }
        }

        $expr = new Expression($exprRaw);
        $expr->setReadOnly(true);
        $expression = $expr->compile($compilationContext);

        /*
         * Check for traversing a constant string
         */
        if ('string' == $expression->getType()) {
            $this->compileStringTraverse($expression, $compilationContext, null);

            return;
        }

        if ('variable' != $expression->getType() && 'array' != $expression->getType()) {
            throw new CompilerException('Unknown type: '.$expression->getType(), $exprRaw);
        }

        $exprVariable = $compilationContext->symbolTable->getVariableForRead($expression->getCode(), $compilationContext, $this->statement['expr']);
        switch ($exprVariable->getType()) {
            case 'variable':
            case 'array':
                $this->compileHashTraverse($expression, $compilationContext, $exprVariable);
                break;

            case 'string':
                $this->compileStringTraverse($expression, $compilationContext, $exprVariable);
                break;

            default:
                throw new CompilerException('Cannot traverse value type: '.$exprVariable->getType(), $exprRaw);
        }
    }
}
