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
        Schema::create('document_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('from_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->onDelete('set null');

            // Tracking information
            $table->enum('action', [
                'created',
                'forwarded',
                'acknowledged',
                'review_started',
                'review_completed',
                'sent_for_approval',
                'approved',
                'rejected',
                'completed',
                'returned',
                'archived',
            ])->default('created');
            $table->text('remarks')->nullable();
            $table->text('instructions')->nullable(); // Instructions for the recipient

            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->boolean('is_read')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_tracking');
    }
};
