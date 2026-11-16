<?php

declare(strict_types=1);

namespace App\Enums;

enum GenerationStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    public function isFinal(): bool
    {
        return in_array($this, [self::Succeeded, self::Failed], true);
    }

    public function canTransitionTo(self $next): bool
    {
        return ! $this->isFinal() && $next->rank() > $this->rank();
    }

    private function rank(): int
    {
        return match ($this) {
            self::Pending => 0,
            self::Queued => 1,
            self::Processing => 2,
            self::Succeeded, self::Failed => 3,
        };
    }
}
