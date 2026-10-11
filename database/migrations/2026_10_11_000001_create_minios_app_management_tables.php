<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minios_app_definitions', function (Blueprint $table) {
            $table->string('app_id', 120)->primary();
            $table->json('overrides');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // Prepared for the Phase 2 menu builder; no records are required in Phase 1.
        Schema::create('minios_app_menus', function (Blueprint $table) {
            $table->id();
            $table->string('app_id', 120)->index();
            $table->foreignId('parent_id')->nullable()->constrained('minios_app_menus')->cascadeOnDelete();
            $table->string('label');
            $table->string('icon')->nullable();
            $table->string('shortcut')->nullable();
            $table->string('action_type')->nullable();
            $table->json('action_payload')->nullable();
            $table->json('permissions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // Prepared for the Phase 3 sidebar and CRUD generator.
        Schema::create('minios_app_sidebar_items', function (Blueprint $table) {
            $table->id();
            $table->string('app_id', 120)->index();
            $table->string('slug', 120);
            $table->string('type', 20)->default('default');
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_color')->nullable();
            $table->string('badge')->nullable();
            $table->json('permissions')->nullable();
            $table->string('route_prefix')->nullable();
            $table->json('options')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['app_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minios_app_sidebar_items');
        Schema::dropIfExists('minios_app_menus');
        Schema::dropIfExists('minios_app_definitions');
    }
};
