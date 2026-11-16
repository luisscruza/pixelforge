<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordWebhookEvent;
use App\Http\Requests\StorePixelforgeWebhookRequest;
use Illuminate\Http\Response;

final class PixelforgeWebhookController
{
    public function store(StorePixelforgeWebhookRequest $request, RecordWebhookEvent $recordWebhookEvent): Response
    {
        $recordWebhookEvent->handle($request->all());

        return response()->noContent();
    }
}
