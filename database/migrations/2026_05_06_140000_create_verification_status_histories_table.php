<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('subject');
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('actor_role', 50)->nullable();
            $table->string('action', 50);
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_owner_visible')->default(true);
            $table->timestamp('happened_at')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'happened_at'], 'verification_status_histories_subject_happened_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_status_histories');
    }
};