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
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('route_name')->nullable();
            $table->string('method', 10);
            $table->string('uri', 500);
            $table->string('controller_action')->nullable();
            $table->integer('status_code');
            $table->float('response_time_ms', 10, 2);
            $table->integer('memory_usage_mb')->nullable();
            $table->integer('query_count')->default(0);
            $table->float('query_time_ms', 10, 2)->default(0);
            $table->integer('cache_hits')->default(0);
            $table->integer('cache_misses')->default(0);
            $table->string('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('slow_queries')->nullable();
            $table->boolean('is_slow_request')->default(false);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['created_at', 'is_slow_request']);
            $table->index(['route_name', 'method']);
            $table->index('user_id');
            $table->index('status_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
