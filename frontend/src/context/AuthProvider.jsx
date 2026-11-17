import { useEffect, useState, useCallback, useRef } from 'react';
import api, { getCsrfCookie } from '../api/axios.js';
import { AuthContext } from './AuthContext.js';

// Set to false to require a real session during development.
const BYPASS_AUTH = false;

function extractErrorMessage(err) {
    const data = err?.response?.data;
    if (!data)
        return 'Impossible de contacter le serveur. Veuillez réessayer plus tard.';
    if (data.errors) {
        return Object.values(data.errors).flat().join(' ');
    }
    return data.message || 'Une erreur est survenue, veuillez réessayer.';
}

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    // Retourne true si la réponse est définitive (authentifié ou non),
    // false en cas d'échec transitoire (réseau, backend pas encore prêt).
    const fetchUser = useCallback(async () => {
        if (import.meta.env.DEV && BYPASS_AUTH) {
            setUser({ id: 0, name: 'Développement', email: 'dev@example.com' });
            setLoading(false);
            return true;
        }
        try {
            const { data } = await api.get('/api/user');
            setUser(data);
            return true;
        } catch (err) {
            const status = err?.response?.status;
            if (status === 401 || status === 419) {
                setUser(null);
                return true;
            }
            return false;
        }
    }, []);

    // Vérifie la session existante (cookie Sanctum) au chargement de l'app.
    // Le guard évite le double appel causé par le double-montage de
    // React StrictMode en développement. Un échec transitoire déclenche
    // un seul réessai avant d'abandonner, pour ne pas laisser l'app bloquée
    // sur "non authentifié" à cause d'un simple aléa réseau au démarrage.
    const initialFetchDone = useRef(false);
    useEffect(() => {
        if (initialFetchDone.current) return;
        initialFetchDone.current = true;

        (async () => {
            const ok = await fetchUser();
            if (!ok) await fetchUser();
            setLoading(false);
        })();
    }, [fetchUser]);

    const register = useCallback(
        async ({ name, email, password, password_confirmation }) => {
            try {
                await getCsrfCookie();
                const { data } = await api.post('/api/register', {
                    name,
                    email,
                    password,
                    password_confirmation,
                });
                setUser(data.user);
                return data.user;
            } catch (err) {
                throw new Error(extractErrorMessage(err), { cause: err });
            }
        },
        [],
    );

    const login = useCallback(
        async ({ email, password }) => {
            try {
                await getCsrfCookie();
                await api.post('/api/login', { email, password });
                await fetchUser();
            } catch (err) {
                throw new Error(extractErrorMessage(err), { cause: err });
            }
        },
        [fetchUser],
    );

    const logout = useCallback(async () => {
        try {
            await getCsrfCookie();
            await api.post('/api/logout');
        } finally {
            setUser(null);
        }
    }, []);

    const changePassword = useCallback(
        async ({ currentPassword, password, passwordConfirmation }) => {
            try {
                await getCsrfCookie();
                const { data } = await api.post('/api/updateProfile', {
                    current_password: currentPassword,
                    password,
                    password_confirmation: passwordConfirmation,
                });
                setUser(data.user);
                // Le backend régénère la session lors d'un changement de mot de passe :
                // on revalide immédiatement plutôt que de laisser l'utilisateur croire
                // qu'il est toujours connecté jusqu'au prochain rechargement de page.
                await fetchUser();
                return data.user;
            } catch (err) {
                throw new Error(extractErrorMessage(err), { cause: err });
            }
        },
        [fetchUser],
    );

    const updateUsername = useCallback(async ({ name }) => {
        try {
            await getCsrfCookie();
            const { data } = await api.post('/api/updateProfile', { name });
            setUser(data.user);
            return data.user;
        } catch (err) {
            throw new Error(extractErrorMessage(err), { cause: err });
        }
    }, []);

    const value = {
        user,
        loading,
        login,
        register,
        logout,
        changePassword,
        updateUsername,
        isAuthenticated: !!user,
    };

    return (
        <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
    );
}
