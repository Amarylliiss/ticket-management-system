export class ApiError extends Error {
    constructor(message, status, errors = {}) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

export function fieldError(error, name) {
    return error?.errors?.[name]?.[0] ?? '';
}

export async function api(path, { method = 'GET', body } = {}) {
    const headers = { Accept: 'application/json' };
    const token = localStorage.getItem('helpdesk_token');

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    const options = { method, headers };

    if (body instanceof FormData) {
        options.body = body;
    } else if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }

    const response = await fetch(`/api${path}`, options);

    if (response.status === 204) {
        return null;
    }

    const isJson = (response.headers.get('content-type') || '').includes('application/json');
    const payload = isJson ? await response.json() : null;

    if (!response.ok) {
        throw new ApiError(payload?.message || 'Nie udało się wykonać żądania.', response.status, payload?.errors || {});
    }

    return payload;
}

export async function downloadFile(path, filename) {
    const headers = {};
    const token = localStorage.getItem('helpdesk_token');

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    const response = await fetch(`/api${path}`, { headers });

    if (!response.ok) {
        throw new ApiError('Nie udało się pobrać pliku.', response.status);
    }

    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    URL.revokeObjectURL(url);
}
