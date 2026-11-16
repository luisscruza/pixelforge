<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('type');
            $table->string('pixelforge_generation_id')->nullable()->index();
            $table->jsonb('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }
};
