import { useState } from 'react';
import { Link } from 'react-router-dom';
import AuthInput from '../components/auth/AuthInput.jsx';
import AuthLayout from '../components/auth/AuthLayout.jsx';
import { useAuth } from '../hooks/useAuth.js';
import { useAuthSubmit } from '../hooks/useAuthSubmit.js';

function SignupPage() {
    const [username, setUsername] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');

    const { register } = useAuth();
    const { error, setError, submitting, submit } = useAuthSubmit();

    const handleSubmit = (event) => {
        event.preventDefault();

        if (!username.trim() || !email.trim() || !password) {
            setError('Les informations sont invalides');
            return;
        }

        if (password !== passwordConfirmation) {
            setError('Les mots de passe ne correspondent pas');
            return;
        }

        submit(() =>
            register({
                name: username,
                email,
                password,
                password_confirmation: passwordConfirmation,
            }),
        );
    };

    return (
        <AuthLayout
            title="Créer un compte"
            error={error}
            onSubmit={handleSubmit}
            submitting={submitting}
            submitLabel="Créer"
            submittingLabel="Création..."
            hint={
                <>
                    Déjà un compte ? <Link to="/login">Se connecter</Link>
                </>
            }
        >
            <AuthInput
                label="Nom d'utilisateur"
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                autoComplete="username"
            />
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
                autoComplete="new-password"
            />
            <AuthInput
                label="Confirmer le mot de passe"
                type="password"
                value={passwordConfirmation}
                onChange={(e) => setPasswordConfirmation(e.target.value)}
                autoComplete="new-password"
            />
        </AuthLayout>
    );
}

export default SignupPage;
