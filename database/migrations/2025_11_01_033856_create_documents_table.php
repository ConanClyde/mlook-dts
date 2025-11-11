<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique(); // e.g., DOC-2025-0001
            $table->foreignId('document_type_id')->constrained('document_types')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // Path to uploaded document
            $table->string('file_name')->nullable(); // Original file name

            // Document origin and routing
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('current_holder_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('origin_department_id')->nullable()->constrained('departments')->onDelete('set null');

            // Document status
            $table->enum('status', [
                'draft',
                'routing',
                'received',
                'in_review',
                'for_approval',
                'approved',
                'rejected',
                'completed',
                'returned',
                'archived',
            ])->default('draft');
            $table->enum('urgency_level', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('deadline')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->timestamp('expired_at')->nullable();
            $table->boolean('auto_archive_on_expiration')->default(false);
            $table->boolean('is_overdue')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('escalated_to')->nullable()->constrained('users')->onDelete('set null');

            // Approval fields
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'not_required'])->default('not_required');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Additional metadata
            $table->text('remarks')->nullable();
            $table->json('tags')->nullable();
            $table->string('category', 100)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
