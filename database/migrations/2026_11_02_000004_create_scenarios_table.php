<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('analysis_id')->constrained()->restrictOnDelete();
            $table->string('name', 120)->nullable();
            $table->jsonb('input_snapshot');
            $table->jsonb('result_snapshot');
            $table->string('calculation_version', 20);
            $table->string('classification', 20);
            $table->timestamps();
            $table->index(['analysis_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenarios');
    }
};
