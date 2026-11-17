import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

export function useAuthSubmit() {
    const [error, setError] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const navigate = useNavigate();

    const submit = async (action) => {
        setError('');
        setSubmitting(true);

        try {
            await action();
            navigate('/');
        } catch (err) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };

    return { error, setError, submitting, submit };
}
