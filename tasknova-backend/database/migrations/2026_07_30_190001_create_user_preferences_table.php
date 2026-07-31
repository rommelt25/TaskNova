<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('theme', 20)->default('system');
            $table->string('language', 10)->default('es');
            $table->boolean('notifications_enabled')->default(true);
            $table->string('timezone', 60)->default('America/Lima');
            $table->string('week_start', 10)->default('monday');
            $table->string('default_view', 20)->default('list');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
