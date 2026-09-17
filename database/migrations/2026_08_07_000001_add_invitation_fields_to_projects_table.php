<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_published')->default(false);
            $table->json('prewed_photos')->nullable();
            $table->string('music_url')->nullable();
            $table->string('akad_location')->nullable();
            $table->dateTime('akad_datetime')->nullable();
            $table->string('akad_maps_url')->nullable();
            $table->string('resepsi_location')->nullable();
            $table->dateTime('resepsi_datetime')->nullable();
            $table->string('resepsi_maps_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'is_published',
                'prewed_photos',
                'music_url',
                'akad_location',
                'akad_datetime',
                'akad_maps_url',
                'resepsi_location',
                'resepsi_datetime',
                'resepsi_maps_url',
            ]);
        });
    }
};
