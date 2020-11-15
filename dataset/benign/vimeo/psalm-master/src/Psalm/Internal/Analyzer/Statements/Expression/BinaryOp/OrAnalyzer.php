<?php
namespace Psalm\Internal\Analyzer\Statements\Expression\BinaryOp;

use PhpParser;
use Psalm\Internal\Analyzer\Statements\ExpressionAnalyzer;
use Psalm\Internal\Analyzer\Statements\Expression\ExpressionIdentifier;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Internal\Analyzer\Statements\Block\IfAnalyzer;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Type;
use Psalm\Type\Algebra;
use Psalm\Type\Reconciler;
use Psalm\Internal\Type\AssertionReconciler;
use function array_merge;
use function array_diff_key;
use function array_filter;
use function array_values;
use function array_map;

/**
 * @internal
 */
class OrAnalyzer
{
    public static function analyze(
        StatementsAnalyzer $statements_analyzer,
        PhpParser\Node\Expr\BinaryOp $stmt,
        Context $context,
        bool $from_stmt = false
    ) : bool {
        if ($from_stmt) {
            $fake_if_stmt = new PhpParser\Node\Stmt\If_(
                new PhpParser\Node\Expr\BooleanNot($stmt->left, $stmt->left->getAttributes()),
                [
                    'stmts' => [
                        new PhpParser\Node\Stmt\Expression(
                            $stmt->right
                        )
                    ]
                ],
                $stmt->getAttributes()
            );

            return IfAnalyzer::analyze($statements_analyzer, $fake_if_stmt, $context) !== false;
        }

        $codebase = $statements_analyzer->getCodebase();

        if (!$stmt->left instanceof PhpParser\Node\Expr\BinaryOp\BooleanOr
            && !($stmt->left instanceof PhpParser\Node\Expr\BooleanNot
                && $stmt->left->expr instanceof PhpParser\Node\Expr\BinaryOp\BooleanAnd)
        ) {
            $if_scope = new \Psalm\Internal\Scope\IfScope();

            try {
                $if_conditional_scope = IfAnalyzer::analyzeIfConditional(
                    $statements_analyzer,
                    $stmt->left,
                    $context,
                    $codebase,
                    $if_scope,
                    $context->branch_point ?: (int) $stmt->getAttribute('startFilePos')
                );

                $left_context = $if_conditional_scope->if_context;

                $left_referenced_var_ids = $if_conditional_scope->cond_referenced_var_ids;
                $left_assigned_var_ids = $if_conditional_scope->cond_assigned_var_ids;
            } catch (\Psalm\Exception\ScopeAnalysisException $e) {
                return false;
            }
        } else {
            $pre_referenced_var_ids = $context->referenced_var_ids;
            $context->referenced_var_ids = [];

            $pre_assigned_var_ids = $context->assigned_var_ids;

            $left_context = clone $context;
            $left_context->parent_context = $context;
            $left_context->if_context = null;
            $left_context->assigned_var_ids = [];

            if (ExpressionAnalyzer::analyze($statements_analyzer, $stmt->left, $left_context) === false) {
                return false;
            }

            foreach ($left_context->vars_in_scope as $var_id => $type) {
                if (!isset($context->vars_in_scope[$var_id])) {
                    if (isset($left_context->assigned_var_ids[$var_id])) {
                        $context->vars_in_scope[$var_id] = clone $type;
                    }
                } else {
                    $context->vars_in_scope[$var_id] = Type::combineUnionTypes(
                        $context->vars_in_scope[$var_id],
                        $type,
                        $codebase
                    );
                }
            }

            if ($codebase->find_unused_variables) {
                $context->unreferenced_vars = $left_context->unreferenced_vars;
            }

            $left_referenced_var_ids = $left_context->referenced_var_ids;
            $left_context->referenced_var_ids = array_merge($pre_referenced_var_ids, $left_referenced_var_ids);

            $left_assigned_var_ids = array_diff_key($left_context->assigned_var_ids, $pre_assigned_var_ids);

            $left_referenced_var_ids = array_diff_key($left_referenced_var_ids, $left_assigned_var_ids);
        }

        $left_clauses = Algebra::getFormula(
            \spl_object_id($stmt->left),
            $stmt->left,
            $context->self,
            $statements_analyzer,
            $codebase
        );

        try {
            $negated_left_clauses = Algebra::negateFormula($left_clauses);
        } catch (\Psalm\Exception\ComplicatedExpressionException $e) {
            return false;
        }

        if ($left_context->reconciled_expression_clauses) {
            $reconciled_expression_clauses = $left_context->reconciled_expression_clauses;

            $negated_left_clauses = array_values(
                array_filter(
                    $negated_left_clauses,
                    function ($c) use ($reconciled_expression_clauses) {
                        return !\in_array($c->getHash(), $reconciled_expression_clauses);
                    }
                )
            );

            if (\count($negated_left_clauses) === 1
                && $negated_left_clauses[0]->wedge
                && !$negated_left_clauses[0]->possibilities
            ) {
                $negated_left_clauses = [];
            }
        }

        $clauses_for_right_analysis = Algebra::simplifyCNF(
            array_merge(
                $context->clauses,
                $negated_left_clauses
            )
        );

        $active_negated_type_assertions = [];

        $negated_type_assertions = Algebra::getTruthsFromFormula(
            $clauses_for_right_analysis,
            \spl_object_id($stmt->left),
            $left_referenced_var_ids,
            $active_negated_type_assertions
        );

        $changed_var_ids = [];

        $right_context = clone $context;

        if ($negated_type_assertions) {
            // while in an or, we allow scope to boil over to support
            // statements of the form if ($x === null || $x->foo())
            $right_vars_in_scope = Reconciler::reconcileKeyedTypes(
                $negated_type_assertions,
                $active_negated_type_assertions,
                $right_context->vars_in_scope,
                $changed_var_ids,
                $left_referenced_var_ids,
                $statements_analyzer,
                [],
                $left_context->inside_loop,
                new CodeLocation($statements_analyzer->getSource(), $stmt)
            );
            $right_context->vars_in_scope = $right_vars_in_scope;
        }

        $right_context->clauses = $clauses_for_right_analysis;

        if ($changed_var_ids) {
            $partitioned_clauses = Context::removeReconciledClauses($right_context->clauses, $changed_var_ids);
            $right_context->clauses = $partitioned_clauses[0];
            $right_context->reconciled_expression_clauses = array_merge(
                $context->reconciled_expression_clauses,
                array_map(
                    function ($c) {
                        return $c->getHash();
                    },
                    $partitioned_clauses[1]
                )
            );

            $partitioned_clauses = Context::removeReconciledClauses($context->clauses, $changed_var_ids);
            $context->clauses = $partitioned_clauses[0];
            $context->reconciled_expression_clauses = array_merge(
                $context->reconciled_expression_clauses,
                array_map(
                    function ($c) {
                        return $c->getHash();
                    },
                    $partitioned_clauses[1]
                )
            );
        }

        $right_context->if_context = null;

        if (ExpressionAnalyzer::analyze($statements_analyzer, $stmt->right, $right_context) === false) {
            return false;
        }

        if (!($stmt->right instanceof PhpParser\Node\Expr\Exit_)) {
            foreach ($right_context->vars_in_scope as $var_id => $type) {
                if (isset($context->vars_in_scope[$var_id])) {
                    $context->vars_in_scope[$var_id] = Type::combineUnionTypes(
                        $context->vars_in_scope[$var_id],
                        $type,
                        $codebase
                    );
                }
            }
        } elseif ($stmt->left instanceof PhpParser\Node\Expr\Assign) {
            $var_id = ExpressionIdentifier::getVarId($stmt->left->var, $context->self);

            if ($var_id && isset($left_context->vars_in_scope[$var_id])) {
                $left_inferred_reconciled = AssertionReconciler::reconcile(
                    '!falsy',
                    clone $left_context->vars_in_scope[$var_id],
                    '',
                    $statements_analyzer,
                    $context->inside_loop,
                    [],
                    new CodeLocation($statements_analyzer->getSource(), $stmt->left),
                    $statements_analyzer->getSuppressedIssues()
                );

                $context->vars_in_scope[$var_id] = $left_inferred_reconciled;
            }
        }

        if ($context->inside_conditional) {
            $context->updateChecks($right_context);
        }

        $context->referenced_var_ids = array_merge(
            $right_context->referenced_var_ids,
            $context->referenced_var_ids
        );

        $context->assigned_var_ids = array_merge(
            $context->assigned_var_ids,
            $right_context->assigned_var_ids
        );

        if ($codebase->find_unused_variables) {
            foreach ($right_context->unreferenced_vars as $var_id => $locations) {
                if (!isset($context->unreferenced_vars[$var_id])) {
                    $context->unreferenced_vars[$var_id] = $locations;
                } else {
                    $new_locations = array_diff_key(
                        $locations,
                        $context->unreferenced_vars[$var_id]
                    );

                    if ($new_locations) {
                        $context->unreferenced_vars[$var_id] += $locations;
                    }
                }
            }
        }

        if ($context->if_context) {
            $if_context = $context->if_context;

            foreach ($right_context->vars_in_scope as $var_id => $type) {
                if (isset($if_context->vars_in_scope[$var_id])) {
                    $if_context->vars_in_scope[$var_id] = Type::combineUnionTypes(
                        $type,
                        $if_context->vars_in_scope[$var_id],
                        $codebase
                    );
                } elseif (isset($left_context->vars_in_scope[$var_id])) {
                    $if_context->vars_in_scope[$var_id] = $left_context->vars_in_scope[$var_id];
                }
            }

            $if_context->referenced_var_ids = array_merge(
                $context->referenced_var_ids,
                $if_context->referenced_var_ids
            );

            $if_context->assigned_var_ids = array_merge(
                $context->assigned_var_ids,
                $if_context->assigned_var_ids
            );

            if ($codebase->find_unused_variables) {
                $if_context->unreferenced_vars = $context->unreferenced_vars;
            }

            $if_context->updateChecks($context);
        }

        $context->vars_possibly_in_scope = array_merge(
            $right_context->vars_possibly_in_scope,
            $context->vars_possibly_in_scope
        );

        return true;
    }
}
