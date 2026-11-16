export function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('pl-PL', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export function formatTime(value) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('pl-PL', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

export function formatDay(value) {
    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat('pl-PL', {
        day: 'numeric',
        month: 'long',
    }).format(new Date(value));
}

export function formatDuration(seconds) {
    if (seconds === null || seconds === undefined) {
        return 'brak danych';
    }

    const total = Math.max(0, Math.round(seconds));
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);

    if (hours > 0) {
        return `${hours} godz. ${minutes} min`;
    }

    if (minutes > 0) {
        return `${minutes} min`;
    }

    return `${total} s`;
}

export function formatSize(bytes) {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${Math.round(bytes / 1024)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
