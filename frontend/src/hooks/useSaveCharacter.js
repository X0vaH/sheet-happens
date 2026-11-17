import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { createCharacter } from '../api/characterCreation.js';
import { validationMessages } from '../utils/characterCreation/characterPayload.js';
import { clearDraft } from '../utils/characterCreation/creationDraft.js';

// Sends the finished character and goes home on success. On failure the draft is kept:
// `sessionExpired` is set when the session ended, `errors` lists what the API (or the network)
// refused.
export function useSaveCharacter(payload) {
    const navigate = useNavigate();
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState([]);
    const [sessionExpired, setSessionExpired] = useState(false);

    async function save() {
        setSaving(true);
        setErrors([]);
        setSessionExpired(false);
        try {
            await createCharacter(payload);
            clearDraft();
            navigate('/');
        } catch (error) {
            const status = error?.response?.status;
            if (status === 401 || status === 419) setSessionExpired(true);
            else if (error?.response) setErrors(validationMessages(error));
            else
                setErrors([
                    'Impossible de contacter le serveur. Vos choix sont conservés, réessayez.',
                ]);
            setSaving(false);
        }
    }

    return { save, saving, errors, sessionExpired };
}
