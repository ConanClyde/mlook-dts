<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentTracking;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_forward_document_for_processing(): void
    {
        $creator = User::factory()->create(['usertype' => 'staff']);
        $recipient = User::factory()->create(['usertype' => 'staff']);
        $documentType = DocumentType::create([
            'name' => 'Memorandum',
            'code' => 'MEMO',
            'description' => 'Memorandum',
            'is_active' => true,
        ]);

        $document = $this->createDocumentFor($creator, $documentType);

        $this->actingAs($creator);

        $response = $this->postJson(route('documents.forward', $document), [
            'to_user_id' => $recipient->id,
            'intent' => 'route',
            'remarks' => 'Please review',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $document->refresh();

        $this->assertSame(Document::STATUS_ROUTING, $document->status);
        $this->assertSame($recipient->id, $document->current_holder_id);

        $this->assertDatabaseHas('document_tracking', [
            'document_id' => $document->id,
            'action' => DocumentTracking::ACTION_FORWARDED,
            'to_user_id' => $recipient->id,
        ]);
    }

    public function test_document_can_be_forwarded_for_approval(): void
    {
        $creator = User::factory()->create(['usertype' => 'staff']);
        $approver = User::factory()->create(['usertype' => 'dean']);
        $documentType = DocumentType::create([
            'name' => 'Official Letter',
            'code' => 'LETTER',
            'description' => 'Official Letter',
            'is_active' => true,
        ]);

        $document = $this->createDocumentFor($creator, $documentType);

        $this->actingAs($creator);

        $response = $this->postJson(route('documents.forward', $document), [
            'to_user_id' => $approver->id,
            'intent' => 'approval',
            'remarks' => 'Needs signature',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $document->refresh();

        $this->assertSame(Document::STATUS_FOR_APPROVAL, $document->status);
        $this->assertSame('pending', $document->approval_status);
        $this->assertSame($approver->id, $document->current_holder_id);

        $this->assertDatabaseHas('document_tracking', [
            'document_id' => $document->id,
            'action' => DocumentTracking::ACTION_SENT_FOR_APPROVAL,
            'to_user_id' => $approver->id,
        ]);
    }

    public function test_recipient_can_acknowledge_and_document_moves_to_in_review(): void
    {
        $creator = User::factory()->create(['usertype' => 'staff']);
        $recipient = User::factory()->create(['usertype' => 'staff']);
        $documentType = DocumentType::create([
            'name' => 'Guideline',
            'code' => 'GUIDE',
            'description' => 'Guideline',
            'is_active' => true,
        ]);

        $document = $this->createDocumentFor($creator, $documentType);

        $this->actingAs($creator)->postJson(route('documents.forward', $document), [
            'to_user_id' => $recipient->id,
            'intent' => 'route',
        ])->assertOk();

        $forwardTracking = DocumentTracking::where('document_id', $document->id)
            ->where('action', DocumentTracking::ACTION_FORWARDED)
            ->latest()
            ->firstOrFail();

        $this->actingAs($recipient)
            ->postJson(route('tracking.receive', $forwardTracking))
            ->assertOk()
            ->assertJson(['success' => true]);

        $document->refresh();

        $this->assertSame(Document::STATUS_IN_REVIEW, $document->status);
        $this->assertDatabaseHas('document_tracking', [
            'document_id' => $document->id,
            'action' => DocumentTracking::ACTION_ACKNOWLEDGED,
            'to_user_id' => $recipient->id,
        ]);
    }

    public function test_approver_can_approve_document(): void
    {
        $creator = User::factory()->create(['usertype' => 'staff']);
        $approver = User::factory()->create(['usertype' => 'dean']);
        $documentType = DocumentType::create([
            'name' => 'Policy',
            'code' => 'POLICY',
            'description' => 'Policy Document',
            'is_active' => true,
        ]);

        $document = $this->createDocumentFor($creator, $documentType);

        $this->actingAs($creator)->postJson(route('documents.forward', $document), [
            'to_user_id' => $approver->id,
            'intent' => 'approval',
        ])->assertOk();

        $this->actingAs($approver)
            ->postJson(route('documents.approve', $document), [
                'remarks' => 'Looks good',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $document->refresh();

        $this->assertSame(Document::STATUS_APPROVED, $document->status);
        $this->assertSame('approved', $document->approval_status);
        $this->assertNotNull($document->approved_at);
        $this->assertNotNull($document->completed_at);

        $this->assertDatabaseHas('document_tracking', [
            'document_id' => $document->id,
            'action' => DocumentTracking::ACTION_APPROVED,
            'from_user_id' => $approver->id,
        ]);
    }

    private function createDocumentFor(User $creator, DocumentType $type, array $overrides = []): Document
    {
        $document = Document::create(array_merge([
            'tracking_number' => Document::generateTrackingNumber(),
            'document_type_id' => $type->id,
            'title' => 'Test Document',
            'description' => 'Test description',
            'created_by' => $creator->id,
            'current_holder_id' => $creator->id,
            'origin_department_id' => null,
            'status' => Document::STATUS_DRAFT,
            'urgency_level' => 'normal',
            'approval_status' => 'not_required',
        ], $overrides));

        DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $creator->id,
            'to_user_id' => $creator->id,
            'from_department_id' => $creator->department_id,
            'to_department_id' => $creator->department_id,
            'action' => DocumentTracking::ACTION_CREATED,
            'remarks' => 'Document created',
            'sent_at' => now(),
            'received_at' => now(),
            'is_read' => true,
        ]);

        return $document;
    }
}
