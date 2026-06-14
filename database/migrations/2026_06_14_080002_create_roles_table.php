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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->jsonb('read')->nullable();
            $table->jsonb('create')->nullable();
            $table->jsonb('update')->nullable();
            $table->jsonb('delete')->nullable();
            $table->jsonb('extra')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX roles_read_gin_idx ON roles USING GIN ("read")');
            DB::statement('CREATE INDEX roles_create_gin_idx ON roles USING GIN ("create")');
            DB::statement('CREATE INDEX roles_update_gin_idx ON roles USING GIN ("update")');
            DB::statement('CREATE INDEX roles_delete_gin_idx ON roles USING GIN ("delete")');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
