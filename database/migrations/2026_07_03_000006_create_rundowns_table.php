<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rundowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('time');
            $table->string('activity');
            $table->text('description')->nullable();
            $table->string('assigned_to');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rundowns');
    }
};
