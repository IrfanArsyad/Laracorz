<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->foreignId('module_group_id')->nullable()->constrained('module_groups')->nullOnDelete();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->string('url')->nullable();
            $table->string('route_name')->nullable()->unique();
            $table->string('badge_source')->nullable();
            $table->jsonb('extra_actions')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('external')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'order']);
            $table->index(['module_group_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
