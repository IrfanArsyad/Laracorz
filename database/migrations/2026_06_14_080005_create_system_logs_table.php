<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 10)->index();
            $table->string('channel', 50)->index();
            $table->string('event')->nullable();
            $table->text('message');
            $table->jsonb('context')->nullable();
            $table->text('exception')->nullable();
            $table->timestampTz('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
