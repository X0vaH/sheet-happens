import { useState } from 'react';
import { Link } from 'react-router-dom';
import AuthInput from '../components/auth/AuthInput.jsx';
import AuthLayout from '../components/auth/AuthLayout.jsx';
import { useAuth } from '../hooks/useAuth.js';
import { useAuthSubmit } from '../hooks/useAuthSubmit.js';

function LoginPage() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    const { login } = useAuth();
    const { error, setError, submitting, submit } = useAuthSubmit();

    const handleSubmit = (event) => {
        event.preventDefault();

        if (!email.trim() || !password) {
            setError('Les informations sont invalides');
            return;
        }

        submit(() => login({ email, password }));
    };

    return (
        <AuthLayout
            title="Connexion"
            error={error}
            onSubmit={handleSubmit}
            submitting={submitting}
            submitLabel="Connexion"
            submittingLabel="Connexion..."
            hint={
                <>
                    Pas de compte ? <Link to="/signup">Créer un compte</Link>
                </>
            }
        >
            <AuthInput
                label="Courriel"
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                autoComplete="email"
            />
            <AuthInput
                label="Mot de passe"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                autoComplete="current-password"
            />
        </AuthLayout>
    );
}

export default LoginPage;
