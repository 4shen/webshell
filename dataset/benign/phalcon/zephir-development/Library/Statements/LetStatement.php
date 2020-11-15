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

use Zephir\CompilationContext;
use Zephir\Detectors\ReadDetector;
use Zephir\Exception\CompilerException;
use Zephir\Expression;
use Zephir\Statements\Let\ArrayIndex as LetArrayIndex;
use Zephir\Statements\Let\ArrayIndexAppend as LetArrayIndexAppend;
use Zephir\Statements\Let\Decr as LetDecr;
use Zephir\Statements\Let\ExportSymbol as LetExportSymbol;
use Zephir\Statements\Let\ExportSymbolString as LetExportSymbolString;
use Zephir\Statements\Let\Incr as LetIncr;
use Zephir\Statements\Let\ObjectDynamicProperty as LetObjectDynamicProperty;
use Zephir\Statements\Let\ObjectDynamicStringProperty as LetObjectDynamicStringProperty;
use Zephir\Statements\Let\ObjectProperty as LetObjectProperty;
use Zephir\Statements\Let\ObjectPropertyAppend as LetObjectPropertyAppend;
use Zephir\Statements\Let\ObjectPropertyArrayIndex as LetObjectPropertyArrayIndex;
use Zephir\Statements\Let\ObjectPropertyArrayIndexAppend as LetObjectPropertyArrayIndexAppend;
use Zephir\Statements\Let\ObjectPropertyDecr as LetObjectPropertyDecr;
use Zephir\Statements\Let\ObjectPropertyIncr as LetObjectPropertyIncr;
use Zephir\Statements\Let\StaticProperty as LetStaticProperty;
use Zephir\Statements\Let\StaticPropertyAdd as LetStaticPropertyAdd;
use Zephir\Statements\Let\StaticPropertyAppend as LetStaticPropertyAppend;
use Zephir\Statements\Let\StaticPropertyArrayIndex as LetStaticPropertyArrayIndex;
use Zephir\Statements\Let\StaticPropertyArrayIndexAppend as LetStaticPropertyArrayIndexAppend;
use Zephir\Statements\Let\StaticPropertySub as LetStaticPropertySub;
use Zephir\Statements\Let\Variable as LetVariable;
use Zephir\Statements\Let\VariableAppend as LetVariableAppend;

/**
 * LetStatement.
 *
 * Let statement is used to assign variables
 */
class LetStatement extends StatementAbstract
{
    /**
     * @param CompilationContext $compilationContext
     *
     * @throws CompilerException
     */
    public function compile(CompilationContext $compilationContext)
    {
        $readDetector = new ReadDetector();

        $statement = $this->statement;
        foreach ($statement['assignments'] as $assignment) {
            $variable = $assignment['variable'];

            /*
             * Get the symbol from the symbol table if necessary
             */
            switch ($assignment['assign-type']) {
                case 'static-property':
                case 'static-property-append':
                case 'static-property-array-index':
                case 'static-property-array-index-append':
                case 'dynamic-variable-string':
                    $symbolVariable = null;
                    break;

                case 'array-index':
                case 'variable-append':
                case 'object-property':
                case 'array-index-append':
                case 'string-dynamic-object-property':
                case 'variable-dynamic-object-property':
                    $symbolVariable = $compilationContext->symbolTable->getVariableForUpdate($variable, $compilationContext, $assignment);
                    break;

                default:
                    $symbolVariable = $compilationContext->symbolTable->getVariableForWrite($variable, $compilationContext, $assignment);
                    break;
            }

            /*
             * Incr/Decr assignments don't require an expression
             */
            if (isset($assignment['expr'])) {
                $expr = new Expression($assignment['expr']);

                switch ($assignment['assign-type']) {
                    case 'variable':
                        if (!$readDetector->detect($variable, $assignment['expr'])) {
                            if (isset($assignment['operator'])) {
                                if ('assign' == $assignment['operator']) {
                                    $expr->setExpectReturn(true, $symbolVariable);
                                }
                            } else {
                                $expr->setExpectReturn(true, $symbolVariable);
                            }
                        } else {
                            if (isset($assignment['operator'])) {
                                if ('assign' == $assignment['operator']) {
                                    $expr->setExpectReturn(true);
                                }
                            } else {
                                $expr->setExpectReturn(true);
                            }
                        }
                        break;
                }

                switch ($assignment['expr']['type']) {
                    case 'property-access':
                    case 'array-access':
                    case 'type-hint':
                        $expr->setReadOnly(true);
                        break;
                }

                $resolvedExpr = $expr->compile($compilationContext);

                /*
                 * Bad implemented operators could return values different than objects
                 */
                if (!\is_object($resolvedExpr)) {
                    throw new CompilerException('Resolved expression is not valid', $assignment['expr']);
                }
            }

            if ($symbolVariable) {
                $variable = $symbolVariable->getName();
            }

            /*
             * There are four types of assignments
             */
            switch ($assignment['assign-type']) {
                case 'variable':
                    $let = new LetVariable();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $readDetector, $compilationContext, $assignment);
                    break;

                case 'variable-append':
                    $let = new LetVariableAppend();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'object-property':
                    $let = new LetObjectProperty();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'variable-dynamic-object-property':
                    $let = new LetObjectDynamicProperty();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'string-dynamic-object-property':
                    $let = new LetObjectDynamicStringProperty();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'static-property':
                    $let = new LetStaticProperty();
                    if (isset($assignment['operator'])) {
                        switch ($assignment['operator']) {
                            case 'add-assign':
                                $let = new LetStaticPropertyAdd();
                                break;
                            case 'sub-assign':
                                $let = new LetStaticPropertySub();
                                break;
                            default:
                                $let = new LetStaticProperty();
                        }
                    }

                    $let->assignStatic($variable, $assignment['property'], $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'static-property-append':
                    $let = new LetStaticPropertyAppend();
                    $let->assignStatic($variable, $assignment['property'], $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'static-property-array-index':
                    $let = new LetStaticPropertyArrayIndex();
                    $let->assignStatic($variable, $assignment['property'], $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'static-property-array-index-append':
                    $let = new LetStaticPropertyArrayIndexAppend();
                    $let->assignStatic($variable, $assignment['property'], $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'array-index':
                    $let = new LetArrayIndex();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'array-index-append':
                    $let = new LetArrayIndexAppend();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'object-property-append':
                    $let = new LetObjectPropertyAppend();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'object-property-array-index':
                    $let = new LetObjectPropertyArrayIndex();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'object-property-array-index-append':
                    $let = new LetObjectPropertyArrayIndexAppend();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'incr':
                    $let = new LetIncr();
                    $let->assign($variable, $symbolVariable, $compilationContext, $assignment);
                    break;

                case 'decr':
                    $let = new LetDecr();
                    $let->assign($variable, $symbolVariable, $compilationContext, $assignment);
                    break;

                case 'object-property-incr':
                    $let = new LetObjectPropertyIncr();
                    $let->assign($variable, $assignment['property'], $symbolVariable, $compilationContext, $assignment);
                    break;

                case 'object-property-decr':
                    $let = new LetObjectPropertyDecr();
                    $let->assign($variable, $assignment['property'], $symbolVariable, $compilationContext, $assignment);
                    break;

                case 'dynamic-variable':
                    $let = new LetExportSymbol();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                case 'dynamic-variable-string':
                    $let = new LetExportSymbolString();
                    $let->assign($variable, $symbolVariable, $resolvedExpr, $compilationContext, $assignment);
                    break;

                default:
                    throw new CompilerException('Unknown assignment: '.$assignment['assign-type'], $assignment);
            }
        }
    }
}
