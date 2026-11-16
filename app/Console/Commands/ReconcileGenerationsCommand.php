<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ReconcileGenerations;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pixelforge:reconcile')]
#[Description('Re-submit stalled generations and poll Pixelforge for ones whose webhooks never arrived')]
final class ReconcileGenerationsCommand extends Command
{
    public function handle(ReconcileGenerations $reconcileGenerations): int
    {
        $result = $reconcileGenerations->handle();

        $this->components->info("Re-submitted {$result->resubmitted}, reconciled {$result->reconciled}, polled {$result->polled}.");

        return self::SUCCESS;
    }
}
