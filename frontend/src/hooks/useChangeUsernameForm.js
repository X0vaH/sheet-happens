import { useCallback, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from './useAuth.js';
import { useSettingsForm } from './useSettingsForm.js';

export function useChangeUsernameForm() {
    const { user, updateUsername } = useAuth();
    const [username, setUsername] = useState(user?.name ?? '');
    const navigate = useNavigate();

    const validate = useCallback(() => {
        if (!username.trim()) {
            return 'Les informations sont invalides';
        }
        return '';
    }, [username]);

    const submit = useCallback(async () => {
        await updateUsername({ name: username.trim() });
        navigate('/settings');
    }, [updateUsername, username, navigate]);

    const { error, submitting, handleSubmit } = useSettingsForm({
        validate,
        submit,
    });

    const onUsernameChange = useCallback((event) => {
        setUsername(event.target.value);
    }, []);

    return { username, onUsernameChange, error, submitting, handleSubmit };
}
