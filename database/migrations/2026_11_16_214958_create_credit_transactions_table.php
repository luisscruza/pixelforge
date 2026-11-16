<?php

declare(strict_types=1);

use App\Models\Generation;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Generation::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 20, 12);
            $table->timestamps();

            $table->unique(['generation_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }
};
