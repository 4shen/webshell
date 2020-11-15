<?php
namespace Psalm\Internal\Analyzer\Statements;

use PhpParser;
use Psalm\Internal\Analyzer\ScopeAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Issue\ContinueOutsideLoop;
use Psalm\IssueBuffer;
use Psalm\Type;

class ContinueAnalyzer
{
    /**
     * @return false|null
     */
    public static function analyze(
        StatementsAnalyzer $statements_analyzer,
        PhpParser\Node\Stmt\Continue_ $stmt,
        Context $context
    ) {
        $loop_scope = $context->loop_scope;

        $leaving_switch = true;

        $codebase = $statements_analyzer->getCodebase();

        if ($loop_scope === null) {
            if (!$context->break_types) {
                if (IssueBuffer::accepts(
                    new ContinueOutsideLoop(
                        'Continue call outside loop context',
                        new CodeLocation($statements_analyzer, $stmt)
                    ),
                    $statements_analyzer->getSource()->getSuppressedIssues()
                )) {
                    return false;
                }
            }
        } else {
            if ($context->break_types
                && \end($context->break_types) === 'switch'
                && (!$stmt->num
                    || !$stmt->num instanceof PhpParser\Node\Scalar\LNumber
                    || $stmt->num->value < 2
                )
            ) {
                $loop_scope->final_actions[] = ScopeAnalyzer::ACTION_LEAVE_SWITCH;
            } else {
                $leaving_switch = false;
                $loop_scope->final_actions[] = ScopeAnalyzer::ACTION_CONTINUE;
            }

            $redefined_vars = $context->getRedefinedVars($loop_scope->loop_parent_context->vars_in_scope);

            if ($loop_scope->redefined_loop_vars === null) {
                $loop_scope->redefined_loop_vars = $redefined_vars;
            } else {
                foreach ($loop_scope->redefined_loop_vars as $redefined_var => $type) {
                    if (!isset($redefined_vars[$redefined_var])) {
                        unset($loop_scope->redefined_loop_vars[$redefined_var]);
                    } else {
                        $loop_scope->redefined_loop_vars[$redefined_var] = Type::combineUnionTypes(
                            $redefined_vars[$redefined_var],
                            $type
                        );
                    }
                }
            }

            foreach ($redefined_vars as $var => $type) {
                if ($type->hasMixed()) {
                    $loop_scope->possibly_redefined_loop_vars[$var] = $type;
                } elseif (isset($loop_scope->possibly_redefined_loop_vars[$var])) {
                    $loop_scope->possibly_redefined_loop_vars[$var] = Type::combineUnionTypes(
                        $type,
                        $loop_scope->possibly_redefined_loop_vars[$var]
                    );
                } else {
                    $loop_scope->possibly_redefined_loop_vars[$var] = $type;
                }
            }

            if ($codebase->find_unused_variables && (!$context->case_scope || $stmt->num)) {
                foreach ($context->unreferenced_vars as $var_id => $locations) {
                    if (isset($loop_scope->unreferenced_vars[$var_id])) {
                        $loop_scope->unreferenced_vars[$var_id] += $locations;
                    } else {
                        $loop_scope->unreferenced_vars[$var_id] = $locations;
                    }

                    if (isset($loop_scope->possibly_unreferenced_vars[$var_id])) {
                        $loop_scope->possibly_unreferenced_vars[$var_id] += $locations;
                    } else {
                        $loop_scope->possibly_unreferenced_vars[$var_id] = $locations;
                    }
                }

                $loop_scope->referenced_var_ids += $context->referenced_var_ids;
            }
        }

        $case_scope = $context->case_scope;
        if ($case_scope && $codebase->find_unused_variables && $leaving_switch) {
            foreach ($context->unreferenced_vars as $var_id => $locations) {
                if (isset($case_scope->unreferenced_vars[$var_id])) {
                    $case_scope->unreferenced_vars[$var_id] += $locations;
                } else {
                    $case_scope->unreferenced_vars[$var_id] = $locations;
                }
            }
        }

        $context->has_returned = true;
    }
}
