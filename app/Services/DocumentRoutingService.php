<?php

namespace App\Services;

use App\Models\Document;
use App\Models\RoutingRule;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DocumentRoutingService
{
    /**
     * Get suggested recipients based on routing rules.
     */
    public function getSuggestedRecipients(Document $document): array
    {
        $rules = RoutingRule::getActiveRulesForDocumentType($document->document_type_id);
        $suggestedUsers = [];

        foreach ($rules as $rule) {
            if ($rule->matchesDocument($document)) {
                if ($rule->user_id) {
                    $user = User::find($rule->user_id);
                    if ($user) {
                        $suggestedUsers[] = [
                            'user' => $user,
                            'rule' => $rule,
                            'priority' => $rule->priority,
                        ];
                    }
                } elseif ($rule->department_id) {
                    // Get all users in the department
                    $users = User::where('department_id', $rule->department_id)
                        ->where('is_active', true)
                        ->get();

                    foreach ($users as $user) {
                        $suggestedUsers[] = [
                            'user' => $user,
                            'rule' => $rule,
                            'priority' => $rule->priority,
                        ];
                    }
                }
            }
        }

        // Sort by priority (higher first)
        usort($suggestedUsers, function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        // Remove duplicates
        $uniqueUsers = [];
        $userIds = [];

        foreach ($suggestedUsers as $item) {
            if (! in_array($item['user']->id, $userIds)) {
                $uniqueUsers[] = $item;
                $userIds[] = $item['user']->id;
            }
        }

        return $uniqueUsers;
    }

    /**
     * Auto-route document based on rules.
     */
    public function autoRoute(Document $document): ?array
    {
        $suggested = $this->getSuggestedRecipients($document);

        if (empty($suggested)) {
            Log::info("No routing rules matched for document {$document->tracking_number}");

            return null;
        }

        // Return the highest priority recipient
        $topRecipient = $suggested[0];

        Log::info("Auto-routing document {$document->tracking_number} to user {$topRecipient['user']->id} based on rule {$topRecipient['rule']->id}");

        return [
            'user_id' => $topRecipient['user']->id,
            'department_id' => $topRecipient['user']->department_id,
            'rule_id' => $topRecipient['rule']->id,
        ];
    }

    /**
     * Get all possible recipients for a document type.
     */
    public function getPossibleRecipients(int $documentTypeId): array
    {
        $rules = RoutingRule::getActiveRulesForDocumentType($documentTypeId);
        $users = [];

        foreach ($rules as $rule) {
            if ($rule->user_id) {
                $user = User::find($rule->user_id);
                if ($user && ! in_array($user->id, array_column($users, 'id'))) {
                    $users[] = $user;
                }
            } elseif ($rule->department_id) {
                $deptUsers = User::where('department_id', $rule->department_id)
                    ->where('is_active', true)
                    ->get();

                foreach ($deptUsers as $user) {
                    if (! in_array($user->id, array_column($users, 'id'))) {
                        $users[] = $user;
                    }
                }
            }
        }

        return $users;
    }
}
