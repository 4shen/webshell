<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\Operators\Other;

use Zephir\CompilationContext;
use Zephir\CompiledExpression;
use Zephir\Exception\CompilerException;
use Zephir\Expression;
use Zephir\Operators\BaseOperator;

/**
 * Require.
 *
 * Includes a plain PHP file
 */
class RequireOperator extends BaseOperator
{
    /**
     * @param array              $expression
     * @param CompilationContext $compilationContext
     *
     * @throws CompilerException
     *
     * @return CompiledExpression
     */
    public function compile(array $expression, CompilationContext $compilationContext)
    {
        $expr = new Expression($expression['left']);
        $expr->setReadOnly(true);
        $expr->setExpectReturn(true);

        $exprPath = $expr->compile($compilationContext);
        if ('variable' == $exprPath->getType()) {
            $exprVariable = $compilationContext->symbolTable->getVariableForRead($exprPath->getCode(), $compilationContext, $expression);
            $exprVar = $compilationContext->backend->getVariableCode($exprVariable);
            if ('variable' == $exprVariable->getType()) {
                if ($exprVariable->hasDifferentDynamicType(['undefined', 'string'])) {
                    $compilationContext->logger->warning(
                        'Possible attempt to use invalid type as path in "require" operator',
                        ['non-valid-require', $expression]
                    );
                }
            }
        } else {
            $exprVar = $compilationContext->symbolTable->getTempVariableForWrite('variable', $compilationContext, $expression);
            $compilationContext->backend->assignString($exprVar, $exprPath->getCode(), $compilationContext);
            $exprVar = $compilationContext->backend->getVariableCode($exprVar);
        }

        $symbolVariable = false;
        if ($this->isExpecting()) {
            $symbolVariable = $compilationContext->symbolTable->getTempVariableForObserveOrNullify('variable', $compilationContext, $expression);
        }

        $compilationContext->headersManager->add('kernel/memory');
        $compilationContext->headersManager->add('kernel/require');

        $codePrinter = $compilationContext->codePrinter;

        if ($symbolVariable) {
            $codePrinter->output('ZEPHIR_OBSERVE_OR_NULLIFY_PPZV(&'.$symbolVariable->getName().');');
            $symbol = $compilationContext->backend->getVariableCodePointer($symbolVariable);
            $codePrinter->output('if (zephir_require_zval_ret('.$symbol.', '.$exprVar.') == FAILURE) {');
        } else {
            $codePrinter->output('if (zephir_require_zval('.$exprVar.') == FAILURE) {');
        }
        $codePrinter->output("\t".'RETURN_MM_NULL();');
        $codePrinter->output('}');

        if ($symbolVariable) {
            return new CompiledExpression('variable', $symbolVariable->getName(), $expression);
        }

        return new CompiledExpression('null', null, $expression);
    }
}
