<?php

namespace App\Services;

use App\Models\Document;
use App\Models\RoutingRule;
use App\Models\User;

class WorkflowAutomationService
{
    /**
     * Auto-route document based on rules.
     */
    public function autoRouteDocument(Document $document): ?User
    {
        // Find applicable routing rules
        $rules = RoutingRule::where('is_active', true)
            ->where(function ($query) use ($document) {
                $query->whereNull('document_type_id')
                    ->orWhere('document_type_id', $document->document_type_id);
            })
            ->where(function ($query) use ($document) {
                $query->whereNull('origin_department_id')
                    ->orWhere('origin_department_id', $document->origin_department_id);
            })
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($rules as $rule) {
            // Check conditions
            if ($this->evaluateConditions($document, $rule->conditions)) {
                // Get target user based on rule
                $targetUser = $this->getTargetUser($rule, $document);

                if ($targetUser) {
                    return $targetUser;
                }
            }
        }

        return null;
    }

    /**
     * Evaluate workflow conditions.
     */
    private function evaluateConditions(Document $document, ?array $conditions): bool
    {
        if (! $conditions) {
            return true;
        }

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? null;

            if (! $field) {
                continue;
            }

            $documentValue = data_get($document, $field);

            $result = match ($operator) {
                '=' => $documentValue == $value,
                '!=' => $documentValue != $value,
                '>' => $documentValue > $value,
                '<' => $documentValue < $value,
                '>=' => $documentValue >= $value,
                '<=' => $documentValue <= $value,
                'contains' => str_contains($documentValue, $value),
                'in' => in_array($documentValue, (array) $value),
                default => false,
            };

            if (! $result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get target user from routing rule.
     */
    private function getTargetUser(RoutingRule $rule, Document $document): ?User
    {
        if ($rule->target_user_id) {
            return User::find($rule->target_user_id);
        }

        if ($rule->target_department_id) {
            // Route to department head
            return User::where('department_id', $rule->target_department_id)
                ->where('usertype', 'department_head')
                ->first();
        }

        if ($rule->target_role) {
            // Route to first user with role
            return User::where('usertype', $rule->target_role)
                ->first();
        }

        return null;
    }

    /**
     * Apply workflow template to document.
     */
    public function applyWorkflowTemplate(Document $document, array $template): array
    {
        $steps = [];

        foreach ($template['steps'] as $step) {
            $steps[] = [
                'step_number' => $step['order'],
                'action' => $step['action'],
                'target_user_id' => $this->resolveTargetUser($step['target'], $document),
                'conditions' => $step['conditions'] ?? null,
                'deadline_hours' => $step['deadline_hours'] ?? null,
                'status' => 'pending',
            ];
        }

        return $steps;
    }

    /**
     * Resolve target user from template step.
     */
    private function resolveTargetUser(string $target, Document $document): ?int
    {
        return match ($target) {
            'creator' => $document->created_by,
            'department_head' => User::where('department_id', $document->origin_department_id)
                ->where('usertype', 'department_head')
                ->first()?->id,
            'dean' => User::where('department_id', $document->origin_department_id)
                ->where('usertype', 'dean')
                ->first()?->id,
            'registrar' => User::where('usertype', 'registrar')->first()?->id,
            'admin' => User::where('usertype', 'admin')->first()?->id,
            default => null,
        };
    }

    /**
     * Check if document should trigger workflow.
     */
    public function shouldTriggerWorkflow(Document $document, string $trigger): bool
    {
        return match ($trigger) {
            'on_create' => $document->wasRecentlyCreated,
            'on_status_change' => $document->wasChanged('status'),
            'on_approval' => $document->status === Document::STATUS_FOR_APPROVAL,
            'on_deadline_approaching' => $document->deadline &&
                $document->deadline->diffInHours(now()) <= 24,
            default => false,
        };
    }

    /**
     * Get available workflow templates.
     */
    public function getWorkflowTemplates(): array
    {
        return [
            [
                'id' => 'standard_approval',
                'name' => 'Standard Approval Workflow',
                'description' => 'Department Head → Dean → Registrar',
                'steps' => [
                    [
                        'order' => 1,
                        'action' => 'review',
                        'target' => 'department_head',
                        'deadline_hours' => 48,
                    ],
                    [
                        'order' => 2,
                        'action' => 'approve',
                        'target' => 'dean',
                        'deadline_hours' => 72,
                        'conditions' => [
                            ['field' => 'status', 'operator' => '=', 'value' => 'approved'],
                        ],
                    ],
                    [
                        'order' => 3,
                        'action' => 'final_approval',
                        'target' => 'registrar',
                        'deadline_hours' => 24,
                    ],
                ],
            ],
            [
                'id' => 'urgent_fast_track',
                'name' => 'Urgent Fast Track',
                'description' => 'Direct to Registrar for urgent documents',
                'steps' => [
                    [
                        'order' => 1,
                        'action' => 'approve',
                        'target' => 'registrar',
                        'deadline_hours' => 24,
                    ],
                ],
            ],
            [
                'id' => 'department_internal',
                'name' => 'Department Internal',
                'description' => 'Department Head review only',
                'steps' => [
                    [
                        'order' => 1,
                        'action' => 'review',
                        'target' => 'department_head',
                        'deadline_hours' => 48,
                    ],
                ],
            ],
        ];
    }
}
