import { useEffect, useState } from 'react';
import { Outlet } from 'react-router-dom';
import { fetchCreationOptions } from '../api/characterCreation.js';
import CreationCatalogShell from '../components/characterCreation/CreationCatalogShell.jsx';
import StatusBox from '../components/global/StatusBox.jsx';
import { CreationOptionsContext } from './CreationOptionsContext.js';

export function CreationOptionsProvider() {
    const [options, setOptions] = useState(null);
    const [error, setError] = useState(false);
    const [attempt, setAttempt] = useState(0);

    useEffect(() => {
        let active = true;
        fetchCreationOptions().then(
            (data) => {
                if (active) setOptions(data);
            },
            () => {
                if (active) setError(true);
            },
        );
        return () => {
            active = false;
        };
    }, [attempt]);

    function retry() {
        setError(false);
        setAttempt((count) => count + 1);
    }

    if (error) {
        return (
            <CreationCatalogShell>
                <StatusBox isError actions>
                    <p>
                        Impossible de charger les options de création de
                        personnage.
                    </p>
                    <button type="button" onClick={retry}>
                        Réessayer
                    </button>
                </StatusBox>
            </CreationCatalogShell>
        );
    }
    if (!options) {
        return (
            <CreationCatalogShell>
                <StatusBox>Chargement des éléments de création...</StatusBox>
            </CreationCatalogShell>
        );
    }

    return (
        <CreationOptionsContext.Provider value={options}>
            <Outlet />
        </CreationOptionsContext.Provider>
    );
}
