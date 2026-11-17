export function apiError(error) {
    const data = error.response?.data;
    return (
        Object.values(data?.errors ?? {})
            .flat()
            .filter(Boolean)
            .join(' ') ||
        data?.message ||
        'Impossible de contacter le serveur.'
    );
}
