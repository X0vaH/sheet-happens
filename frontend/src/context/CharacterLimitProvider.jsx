import { useCallback, useEffect, useMemo, useState } from 'react';
import characterClient from '../api/characterClient.js';
import { useAuth } from '../hooks/useAuth.js';
import { CharacterLimitContext } from './CharacterLimitContext.js';
import { MAX_CHARACTERS } from '../configs/characterLimit.js';

// Loads the signed-in user's characters once and shares them, so the navbar, the creation
// guard and the creation slot picker do not each query the API. The home page keeps the list
// in sync with the one it already loads (see `setCharacters`). This is only a hint: if the list
// cannot be loaded, creation stays allowed and the API's 403/422 on save remain the source of
// truth.
export function CharacterLimitProvider({ children }) {
    const { user } = useAuth();
    const userId = user?.id ?? null;
    const [state, setState] = useState({ userId: null, characters: null });

    useEffect(() => {
        if (userId === null) return;
        let cancelled = false;
        characterClient
            .all()
            .then((characters) => {
                if (!cancelled) setState({ userId, characters });
            })
            .catch(() => {
                if (!cancelled) setState({ userId, characters: null });
            });
        return () => {
            cancelled = true;
        };
    }, [userId]);

    const setCharacters = useCallback(
        (nextCharacters) =>
            setState((previous) =>
                previous.userId === userId &&
                previous.characters === nextCharacters
                    ? previous
                    : { userId, characters: nextCharacters },
            ),
        [userId],
    );

    const value = useMemo(() => {
        const loaded = userId === null || state.userId === userId;
        const characters = loaded && userId !== null ? state.characters : null;
        return {
            loading: !loaded,
            characters,
            limitReached:
                characters !== null && characters.length >= MAX_CHARACTERS,
            setCharacters,
        };
    }, [userId, state, setCharacters]);

    return (
        <CharacterLimitContext.Provider value={value}>
            {children}
        </CharacterLimitContext.Provider>
    );
}
