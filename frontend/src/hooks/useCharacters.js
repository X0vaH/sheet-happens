import { useCallback, useEffect, useState } from 'react';
import useApiData from './useApiData.js';
import characterClient from '../api/characterClient.js';

function extractErrorMessage(err) {
    const data = err?.response?.data;
    if (!data)
        return 'Impossible de contacter le serveur. Veuillez réessayer plus tard.';
    return data.message || 'Une erreur est survenue, veuillez réessayer.';
}

export function useCharacters() {
    const [characters, setCharacters] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchCharacters = useCallback(() => {
        setLoading(true);
        setError(null);
        characterClient
            .all()
            .then((data) => setCharacters(data))
            .catch((err) => setError(extractErrorMessage(err)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        // eslint-disable-next-line react-hooks/set-state-in-effect
        fetchCharacters();
    }, [fetchCharacters]);

    return { characters, loading, error, refetch: fetchCharacters };
}

export function useCharacter(id) {
    const { data: character, ...result } = useApiData(characterClient.get, id);
    return { character, ...result };
}
