const formatter = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });

const UNITS: [Intl.RelativeTimeFormatUnit, number][] = [
    ['day', 86_400],
    ['hour', 3_600],
    ['minute', 60],
    ['second', 1],
];

export function relativeTime(iso: string | null, now: number): string {
    if (iso === null) {
        return '';
    }

    const seconds = Math.round((new Date(iso).getTime() - now) / 1000);

    if (Math.abs(seconds) < 5) {
        return 'just now';
    }

    for (const [unit, size] of UNITS) {
        if (Math.abs(seconds) >= size) {
            return formatter.format(Math.trunc(seconds / size), unit);
        }
    }

    return 'just now';
}

export function elapsedSeconds(iso: string | null, now: number): number {
    return iso === null
        ? 0
        : Math.max(0, Math.floor((now - new Date(iso).getTime()) / 1000));
}

export function formatDuration(seconds: number): string {
    const minutes = Math.floor(seconds / 60);

    return minutes > 0
        ? `${minutes}m ${String(seconds % 60).padStart(2, '0')}s`
        : `${seconds}s`;
}

export function formatDay(date: string): string {
    return new Date(`${date}T00:00:00Z`).toLocaleDateString('en', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        timeZone: 'UTC',
    });
}
