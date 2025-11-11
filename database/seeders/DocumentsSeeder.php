<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\DocumentTracking;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $documentTypes = DocumentType::where('is_active', true)->get();
        $faculty = User::where('usertype', 'faculty')->get();
        $staff = User::where('usertype', 'staff')->get();
        $deans = User::where('usertype', 'dean')->get();
        $deptHeads = User::where('usertype', 'department_head')->get();
        $registrars = User::where('usertype', 'registrar')->get();
        $students = User::where('usertype', 'student')->limit(50)->get();

        if ($users->isEmpty() || $documentTypes->isEmpty()) {
            $this->command->warn('No users or document types found. Please run ManyUsersSeeder first.');

            return;
        }

        $documentsCreated = 0;

        // Create draft documents (10-15)
        for ($i = 0; $i < rand(10, 15); $i++) {
            $creator = $users->random();
            $docType = $documentTypes->random();

            // Check if user can create this document type
            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $creator->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_DRAFT,
                'urgency_level' => fake()->randomElement(['low', 'normal', 'high']),
                'deadline' => fake()->optional(0.6)->dateTimeBetween('now', '+30 days'),
                'is_overdue' => false,
                'approval_status' => 'not_required',
                'created_at' => now()->subDays(rand(0, 30)),
            ]);
            $documentsCreated++;
        }

        // Create routing documents (15-20)
        for ($i = 0; $i < rand(15, 20); $i++) {
            $creator = $users->random();
            $recipient = $users->except($creator->id)->random();
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $recipient->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_ROUTING,
                'urgency_level' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
                'deadline' => fake()->optional(0.7)->dateTimeBetween('now', '+20 days'),
                'is_overdue' => false,
                'approval_status' => 'pending',
                'created_at' => now()->subDays(rand(1, 25)),
            ]);

            DocumentTracking::create([
                'document_id' => $doc->id,
                'from_user_id' => $creator->id,
                'to_user_id' => $recipient->id,
                'from_department_id' => $creator->department_id,
                'to_department_id' => $recipient->department_id,
                'action' => 'forwarded',
                'remarks' => fake()->optional(0.7)->sentence(),
                'instructions' => fake()->optional(0.5)->sentence(),
                'sent_at' => $doc->created_at,
            ]);

            $documentsCreated++;
        }

        // Create received documents (10-15)
        for ($i = 0; $i < rand(10, 15); $i++) {
            $creator = $users->random();
            $recipient = $users->except($creator->id)->random();
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $sentAt = now()->subDays(rand(1, 15));
            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $recipient->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_RECEIVED,
                'urgency_level' => fake()->randomElement(['normal', 'high', 'urgent']),
                'deadline' => fake()->optional(0.7)->dateTimeBetween('now', '+15 days'),
                'is_overdue' => false,
                'approval_status' => 'pending',
                'created_at' => $sentAt,
            ]);

            DocumentTracking::create([
                'document_id' => $doc->id,
                'from_user_id' => $creator->id,
                'to_user_id' => $recipient->id,
                'from_department_id' => $creator->department_id,
                'to_department_id' => $recipient->department_id,
                'action' => 'forwarded',
                'remarks' => fake()->optional(0.7)->sentence(),
                'sent_at' => $sentAt,
                'received_at' => $sentAt->copy()->addHours(rand(1, 48)),
                'is_read' => true,
            ]);

            $documentsCreated++;
        }

        // Create in_review documents (10-15)
        for ($i = 0; $i < rand(10, 15); $i++) {
            $creator = $users->random();
            $recipient = $users->except($creator->id)->random();
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $recipient->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_IN_REVIEW,
                'urgency_level' => fake()->randomElement(['normal', 'high', 'urgent']),
                'deadline' => fake()->optional(0.7)->dateTimeBetween('now', '+10 days'),
                'is_overdue' => false,
                'approval_status' => 'pending',
                'created_at' => now()->subDays(rand(1, 20)),
            ]);

            // Add tracking
            $tracking = DocumentTracking::create([
                'document_id' => $doc->id,
                'from_user_id' => $creator->id,
                'to_user_id' => $recipient->id,
                'from_department_id' => $creator->department_id,
                'to_department_id' => $recipient->department_id,
                'action' => 'forwarded',
                'sent_at' => $doc->created_at,
                'received_at' => $doc->created_at->copy()->addHours(rand(1, 24)),
                'is_read' => true,
            ]);

            // Add comments
            if (fake()->boolean(60)) {
                DocumentComment::create([
                    'document_id' => $doc->id,
                    'user_id' => $recipient->id,
                    'comment' => fake()->paragraph(),
                    'is_internal' => fake()->boolean(30),
                ]);
            }

            $documentsCreated++;
        }

        // Create for_approval documents (8-12)
        for ($i = 0; $i < rand(8, 12); $i++) {
            $creator = $users->random();
            $approver = $deans->isNotEmpty() ? $deans->random() : ($deptHeads->isNotEmpty() ? $deptHeads->random() : $users->except($creator->id)->random());
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $approver->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_FOR_APPROVAL,
                'urgency_level' => fake()->randomElement(['normal', 'high', 'urgent']),
                'deadline' => fake()->optional(0.8)->dateTimeBetween('now', '+7 days'),
                'is_overdue' => false,
                'approval_status' => 'pending',
                'created_at' => now()->subDays(rand(1, 15)),
            ]);

            DocumentTracking::create([
                'document_id' => $doc->id,
                'from_user_id' => $creator->id,
                'to_user_id' => $approver->id,
                'from_department_id' => $creator->department_id,
                'to_department_id' => $approver->department_id,
                'action' => 'forwarded',
                'remarks' => 'For approval',
                'sent_at' => $doc->created_at,
                'received_at' => $doc->created_at->copy()->addHours(rand(1, 24)),
                'is_read' => true,
            ]);

            $documentsCreated++;
        }

        // Create approved documents (10-15)
        for ($i = 0; $i < rand(10, 15); $i++) {
            $creator = $users->random();
            $approver = $deans->isNotEmpty() ? $deans->random() : ($deptHeads->isNotEmpty() ? $deptHeads->random() : $users->except($creator->id)->random());
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $createdAt = now()->subDays(rand(5, 30));
            $approvedAt = $createdAt->copy()->addDays(rand(1, 10));

            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $creator->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_APPROVED,
                'urgency_level' => fake()->randomElement(['low', 'normal', 'high']),
                'deadline' => fake()->optional(0.6)->dateTimeBetween('now', '+20 days'),
                'is_overdue' => false,
                'approval_status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => $approvedAt,
                'approval_remarks' => fake()->optional(0.7)->sentence(),
                'created_at' => $createdAt,
            ]);

            DocumentTracking::create([
                'document_id' => $doc->id,
                'from_user_id' => $creator->id,
                'to_user_id' => $approver->id,
                'from_department_id' => $creator->department_id,
                'to_department_id' => $approver->department_id,
                'action' => 'forwarded',
                'sent_at' => $createdAt,
                'received_at' => $createdAt->copy()->addHours(rand(1, 24)),
                'is_read' => true,
            ]);

            $documentsCreated++;
        }

        // Create completed documents (15-20)
        for ($i = 0; $i < rand(15, 20); $i++) {
            $creator = $users->random();
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $createdAt = now()->subDays(rand(10, 45));
            $completedAt = $createdAt->copy()->addDays(rand(3, 15));

            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $creator->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_COMPLETED,
                'urgency_level' => fake()->randomElement(['low', 'normal', 'high']),
                'deadline' => fake()->optional(0.6)->dateTimeBetween($createdAt, $completedAt->copy()->addDays(5)),
                'is_overdue' => false,
                'approval_status' => fake()->randomElement(['approved', 'not_required']),
                'completed_at' => $completedAt,
                'created_at' => $createdAt,
            ]);

            $documentsCreated++;
        }

        // Create rejected documents (5-8)
        for ($i = 0; $i < rand(5, 8); $i++) {
            $creator = $users->random();
            $rejecter = $deans->isNotEmpty() ? $deans->random() : ($deptHeads->isNotEmpty() ? $deptHeads->random() : $users->except($creator->id)->random());
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $createdAt = now()->subDays(rand(5, 20));
            $rejectedAt = $createdAt->copy()->addDays(rand(1, 7));

            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $creator->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_REJECTED,
                'urgency_level' => fake()->randomElement(['normal', 'high']),
                'deadline' => null,
                'is_overdue' => false,
                'approval_status' => 'rejected',
                'rejected_by' => $rejecter->id,
                'rejected_at' => $rejectedAt,
                'rejection_reason' => fake()->sentence(),
                'created_at' => $createdAt,
            ]);

            DocumentTracking::create([
                'document_id' => $doc->id,
                'from_user_id' => $creator->id,
                'to_user_id' => $rejecter->id,
                'from_department_id' => $creator->department_id,
                'to_department_id' => $rejecter->department_id,
                'action' => 'forwarded',
                'sent_at' => $createdAt,
                'received_at' => $createdAt->copy()->addHours(rand(1, 24)),
                'is_read' => true,
            ]);

            $documentsCreated++;
        }

        // Create archived documents (8-12)
        for ($i = 0; $i < rand(8, 12); $i++) {
            $creator = $users->random();
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $createdAt = now()->subDays(rand(60, 180));
            $completedAt = $createdAt->copy()->addDays(rand(5, 20));
            $archivedAt = $completedAt->copy()->addDays(rand(30, 90));

            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => fake()->sentence(rand(4, 8)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $creator->id,
                'origin_department_id' => $creator->department_id,
                'status' => Document::STATUS_ARCHIVED,
                'urgency_level' => fake()->randomElement(['low', 'normal']),
                'deadline' => null,
                'is_overdue' => false,
                'approval_status' => 'approved',
                'completed_at' => $completedAt,
                'archived_at' => $archivedAt,
                'created_at' => $createdAt,
            ]);

            $documentsCreated++;
        }

        // Create some urgent documents with deadlines
        for ($i = 0; $i < rand(5, 8); $i++) {
            $creator = $users->random();
            $recipient = $users->except($creator->id)->random();
            $docType = $documentTypes->random();

            if (! $creator->isAdmin() && $docType->allowed_roles) {
                $userRole = $creator->getUserRole();
                if (! in_array($userRole, $docType->allowed_roles, true)) {
                    continue;
                }
            }

            $deadline = now()->addDays(rand(1, 5));
            $isOverdue = $deadline < now();

            $doc = Document::create([
                'tracking_number' => Document::generateTrackingNumber(),
                'document_type_id' => $docType->id,
                'title' => 'URGENT: '.fake()->sentence(rand(3, 6)),
                'description' => fake()->paragraph(rand(2, 4)),
                'created_by' => $creator->id,
                'current_holder_id' => $recipient->id,
                'origin_department_id' => $creator->department_id,
                'status' => fake()->randomElement([Document::STATUS_ROUTING, Document::STATUS_RECEIVED, Document::STATUS_IN_REVIEW, Document::STATUS_FOR_APPROVAL]),
                'urgency_level' => 'urgent',
                'deadline' => $deadline,
                'is_overdue' => $isOverdue,
                'approval_status' => 'pending',
                'created_at' => now()->subDays(rand(0, 5)),
            ]);

            DocumentTracking::create([
                'document_id' => $doc->id,
                'from_user_id' => $creator->id,
                'to_user_id' => $recipient->id,
                'from_department_id' => $creator->department_id,
                'to_department_id' => $recipient->department_id,
                'action' => 'forwarded',
                'remarks' => 'URGENT - Please review immediately',
                'sent_at' => $doc->created_at,
                'received_at' => $doc->status !== Document::STATUS_ROUTING ? $doc->created_at->copy()->addHours(rand(1, 12)) : null,
                'is_read' => $doc->status !== Document::STATUS_ROUTING,
            ]);

            $documentsCreated++;
        }

        $this->command->info("✓ Successfully created {$documentsCreated} documents with various statuses!");
        $this->command->info('  - Draft, Routing, Received, In Review, For Approval');
        $this->command->info('  - Approved, Completed, Rejected, Archived');
        $this->command->info('  - Urgent documents with deadlines');
    }
}
