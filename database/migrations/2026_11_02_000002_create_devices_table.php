<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('category', 30);
            $table->string('brand', 80)->nullable();
            $table->string('model', 120)->nullable();
            $table->unsignedSmallInteger('purchase_year')->nullable();
            $table->text('condition_notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'archived_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
