<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')->constrained()->restrictOnDelete();
            $table->jsonb('device_snapshot');
            $table->jsonb('input_snapshot');
            $table->jsonb('result_snapshot');
            $table->string('calculation_version', 20);
            $table->string('classification', 20);
            $table->timestamps();
            $table->index(['device_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
