<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('cost_centers')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            
            // Link to departments/projects (optional)
            $table->unsignedBigInteger('department_id')->nullable(); // FK to be added when Department model exists
            $table->unsignedBigInteger('project_id')->nullable(); // FK to be added when Project model exists
            
            // Hierarchy
            $table->integer('level')->default(0);
            $table->integer('sort_order')->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_group')->default(false); // For grouping cost centers
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index('parent_id');
            
            // TODO: Add foreign key constraints for department_id and project_id when those tables exist
            // $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            // $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
