import { useCallback } from 'react';
import { useNavigate } from 'react-router-dom';

export function useAccountSettingsNavigation() {
    const navigate = useNavigate();

    const goToUsername = useCallback(() => {
        navigate('/settings/username');
    }, [navigate]);

    const goToPassword = useCallback(() => {
        navigate('/settings/password');
    }, [navigate]);

    const goHome = useCallback(() => {
        navigate('/');
    }, [navigate]);

    const goBack = useCallback(() => {
        navigate(-1);
    }, [navigate]);

    return { goToUsername, goToPassword, goHome, goBack };
}
