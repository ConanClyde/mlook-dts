<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Documents table indexes
        Schema::table('documents', function (Blueprint $table) {
            // Composite indexes for common queries
            $table->index(['status', 'created_at'], 'idx_documents_status_created');
            $table->index(['current_holder_id', 'status'], 'idx_documents_holder_status');
            $table->index(['created_by', 'status'], 'idx_documents_creator_status');
            $table->index(['created_by', 'status', 'created_at'], 'idx_creator_status_date');
            $table->index(['deadline', 'status'], 'idx_documents_deadline_status');
            $table->index(['status', 'urgency_level'], 'idx_status_urgency');
            $table->index(['document_type_id', 'status'], 'idx_documents_type_status');
            $table->index(['origin_department_id', 'status'], 'idx_documents_dept_status');

            // Search optimization
            // Note: tracking_number already has a unique index, so we don't need to add another
            $table->index('is_overdue', 'idx_documents_overdue');
        });

        // Document tracking table indexes
        Schema::table('document_tracking', function (Blueprint $table) {
            $table->index(['to_user_id', 'is_read'], 'idx_tracking_user_read');
            $table->index(['from_user_id', 'created_at'], 'idx_tracking_from_created');
            $table->index(['document_id', 'created_at'], 'idx_tracking_doc_created');
            $table->index(['from_user_id', 'to_user_id'], 'idx_from_to_users');
            $table->index('action', 'idx_tracking_action');
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read', 'created_at'], 'idx_notifications_user_read_created');
            $table->index('type', 'idx_notifications_type');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('usertype');
            $table->index(['usertype', 'department_id'], 'idx_users_type_dept');
            $table->index('email', 'idx_users_email');
        });

        // Audit logs table indexes are already created in the create_audit_logs_table migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Helper function to safely drop index
        $dropIndexIfExists = function ($tableName, $indexName) {
            try {
                $indexes = DB::select("SHOW INDEXES FROM `{$tableName}` WHERE Key_name = ?", [$indexName]);
                if (! empty($indexes)) {
                    DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`");
                }
            } catch (\Exception $e) {
                // Index might be tied to a foreign key, skip it
            }
        };

        // Drop documents table indexes
        if (Schema::hasTable('documents')) {
            $dropIndexIfExists('documents', 'idx_documents_status_created');
            $dropIndexIfExists('documents', 'idx_documents_holder_status');
            $dropIndexIfExists('documents', 'idx_documents_creator_status');
            $dropIndexIfExists('documents', 'idx_creator_status_date');
            $dropIndexIfExists('documents', 'idx_documents_deadline_status');
            $dropIndexIfExists('documents', 'idx_status_urgency');
            $dropIndexIfExists('documents', 'idx_documents_type_status');
            $dropIndexIfExists('documents', 'idx_documents_dept_status');
            // tracking_number index is created by unique constraint, don't try to drop it
            $dropIndexIfExists('documents', 'idx_documents_overdue');
        }

        // Drop document_tracking table indexes
        if (Schema::hasTable('document_tracking')) {
            $dropIndexIfExists('document_tracking', 'idx_tracking_user_read');
            $dropIndexIfExists('document_tracking', 'idx_tracking_from_created');
            $dropIndexIfExists('document_tracking', 'idx_tracking_doc_created');
            $dropIndexIfExists('document_tracking', 'idx_from_to_users');
            $dropIndexIfExists('document_tracking', 'idx_tracking_action');
        }

        // Drop notifications table indexes
        if (Schema::hasTable('notifications')) {
            $dropIndexIfExists('notifications', 'idx_notifications_user_read_created');
            $dropIndexIfExists('notifications', 'idx_notifications_type');
        }

        // Drop users table indexes
        if (Schema::hasTable('users')) {
            $dropIndexIfExists('users', 'users_usertype_index');
            $dropIndexIfExists('users', 'idx_users_type_dept');
            // Email index might be unique, handle separately
            try {
                $indexes = DB::select("SHOW INDEXES FROM `users` WHERE Key_name = 'idx_users_email'");
                if (! empty($indexes)) {
                    DB::statement('ALTER TABLE `users` DROP INDEX `idx_users_email`');
                }
            } catch (\Exception $e) {
                // Skip if can't drop
            }
        }

        // Audit logs indexes are managed in the create_audit_logs_table migration
    }
};
