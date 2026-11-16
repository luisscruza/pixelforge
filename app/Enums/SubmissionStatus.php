<?php

declare(strict_types=1);

namespace App\Enums;

enum SubmissionStatus: string
{
    case Submitting = 'submitting';
    case Unknown = 'unknown';
    case NeedsReconciliation = 'needs_reconciliation';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}
