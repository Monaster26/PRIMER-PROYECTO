export function formatDate(dateStr) {
    if (!dateStr) return '—';
    let parts;
    if (
        typeof dateStr === 'string' &&
        (parts = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})$/))
    ) {
        const [, y, m, d] = parts.map(Number);
        return new Date(y, m - 1, d).toLocaleDateString('es-CL', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    }
    const dt = new Date(dateStr);
    if (isNaN(dt.getTime())) return dateStr;
    return dt.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

export function formatTime(dateStr) {
    if (!dateStr) return '—';
    const dt = new Date(dateStr);
    if (isNaN(dt.getTime())) return '—';
    return dt.toLocaleTimeString('es-CL', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
}

export function formatDateTime(dateStr) {
    if (!dateStr) return '—';
    return `${formatDate(dateStr)} ${formatTime(dateStr)}`;
}
