<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_create_document(): void
    {
        $user = User::factory()->create();
        $documentType = DocumentType::factory()->create([
            'allowed_roles' => [$user->getUserRole()],
        ]);

        // Create a fake file for upload
        $file = \Illuminate\Http\UploadedFile::fake()->create('test-document.pdf', 100);

        $response = $this->actingAs($user)->post(route('documents.store'), [
            'title' => 'Test Document',
            'document_type_id' => $documentType->id,
            'description' => 'Test description',
            'urgency_level' => 'normal',
            'files' => [$file],
        ]);

        // Check if document was created (could be redirect or JSON response)
        if ($response->isRedirect()) {
            $response->assertRedirect();
        } else {
            $response->assertStatus(200);
        }
        
        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'created_by' => $user->id,
        ]);
    }

    public function test_user_can_view_own_documents(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create(['created_by' => $user->id]);

        $response = $this->actingAs($user)->get(route('documents.show', $document));

        $response->assertStatus(200);
        $response->assertSee($document->title);
    }

    public function test_user_cannot_view_others_documents_without_permission(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $document = Document::factory()->create(['created_by' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('documents.show', $document));

        $response->assertStatus(403);
    }

    public function test_user_can_forward_document(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create();
        $document = Document::factory()->create([
            'created_by' => $user->id,
            'current_holder_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('documents.forward', $document), [
            'to_user_id' => $recipient->id,
            'remarks' => 'Please review',
            'intent' => 'route',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('document_tracking', [
            'document_id' => $document->id,
            'from_user_id' => $user->id,
            'to_user_id' => $recipient->id,
        ]);
    }

    public function test_user_can_approve_document(): void
    {
        $admin = User::factory()->create(['usertype' => 'admin']);
        $document = Document::factory()->create([
            'status' => Document::STATUS_FOR_APPROVAL,
            'current_holder_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('documents.approve', $document), [
            'remarks' => 'Approved',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => Document::STATUS_APPROVED,
            'approved_by' => $admin->id,
        ]);
    }

    public function test_document_tracking_number_is_unique(): void
    {
        $document1 = Document::factory()->create();
        $document2 = Document::factory()->create();

        $this->assertNotEquals($document1->tracking_number, $document2->tracking_number);
    }

    public function test_overdue_documents_are_flagged(): void
    {
        $document = Document::factory()->create([
            'deadline' => now()->subDays(1),
            'status' => Document::STATUS_ROUTING,
        ]);

        $this->assertTrue($document->is_overdue);
    }
}
