<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->foreignId('meal_distribution_id')->constrained()->restrictOnDelete();

            $table->string('title', 150);
            $table->text('incident_description');
            $table->text('symptoms');
            $table->unsignedInteger('affected_count')->nullable();
            $table->date('incident_date');

            $table->enum('status', ['submitted', 'under_review', 'forwarded', 'closed'])
                ->default('submitted');
            $table->index('status');

            $table->string('evidence_path')->nullable();
            $table->string('evidence_original_name')->nullable();

            $table->foreignId('monitor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('monitor_notes')->nullable();
            $table->boolean('flagged_for_forwarding')->default(false);

            $table->string('forwarded_to')->nullable();
            $table->text('coordinator_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
