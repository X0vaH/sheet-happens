import { useCallback, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from './useAuth.js';
import { useSettingsForm } from './useSettingsForm.js';

export function useChangePasswordForm() {
    const [currentPassword, setCurrentPassword] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const { changePassword } = useAuth();
    const navigate = useNavigate();

    const validate = useCallback(() => {
        if (!currentPassword || !password || !passwordConfirmation) {
            return 'Les informations sont invalides';
        }
        if (password !== passwordConfirmation) {
            return 'Les mots de passe ne correspondent pas';
        }
        return '';
    }, [currentPassword, password, passwordConfirmation]);

    const submit = useCallback(async () => {
        await changePassword({
            currentPassword,
            password,
            passwordConfirmation,
        });
        navigate('/settings');
    }, [
        changePassword,
        currentPassword,
        password,
        passwordConfirmation,
        navigate,
    ]);

    const { error, submitting, handleSubmit } = useSettingsForm({
        validate,
        submit,
    });

    const onCurrentPasswordChange = useCallback((event) => {
        setCurrentPassword(event.target.value);
    }, []);

    const onPasswordChange = useCallback((event) => {
        setPassword(event.target.value);
    }, []);

    const onPasswordConfirmationChange = useCallback((event) => {
        setPasswordConfirmation(event.target.value);
    }, []);

    return {
        currentPassword,
        password,
        passwordConfirmation,
        onCurrentPasswordChange,
        onPasswordChange,
        onPasswordConfirmationChange,
        error,
        submitting,
        handleSubmit,
    };
}
