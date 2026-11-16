<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetPricing;
use App\Actions\StartGeneration;
use App\Actions\SummarizeGenerations;
use App\Http\Requests\IndexGenerationRequest;
use App\Http\Requests\StoreGenerationRequest;
use App\Http\Resources\GenerationResource;
use App\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class GenerationController
{
    private const int PER_PAGE = 12;

    public function index(
        IndexGenerationRequest $request,
        #[CurrentUser] User $user,
        GetPricing $getPricing,
        SummarizeGenerations $summarizeGenerations,
        #[Config('services.pixelforge.markup')] string $markup,
    ): Response {
        return Inertia::render('generations/Index', [
            'generations' => Inertia::scroll(fn () => GenerationResource::collection(
                $user->generations()->latest()->latest('id')->paginate(self::PER_PAGE),
            )),
            'watched' => Inertia::optional(fn () => GenerationResource::collection(
                $user->generations()->whereIn('uuid', $request->watchedIds())->get(),
            )),
            'stats' => Inertia::defer(fn (): array => $summarizeGenerations->handle($user)),
            'pricing' => Inertia::once(fn (): array => $getPricing->handle()->toArray()),
            'markup' => $markup,
            'maxReferenceImages' => StoreGenerationRequest::MAX_REFERENCE_IMAGES,
        ]);
    }

    public function store(StoreGenerationRequest $request, #[CurrentUser] User $user, StartGeneration $startGeneration): RedirectResponse
    {
        $startGeneration->handle($user, $request->toInput());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Queued. Watch it come to life on the right.']);

        return to_route('generations.index');
    }
}
