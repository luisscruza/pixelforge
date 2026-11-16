<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;

final readonly class GenerationInput
{
    /**
     * @param  list<string>  $referenceImages
     */
    public function __construct(
        public string $prompt,
        public GenerationSize $size,
        public GenerationQuality $quality,
        public array $referenceImages = [],
    ) {
        //
    }
}
