import { useCallback, useState } from 'react';

export function useSettingsForm({ validate, submit }) {
    const [error, setError] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = useCallback(
        async (event) => {
            event.preventDefault();

            const validationError = validate();
            if (validationError) {
                setError(validationError);
                return;
            }

            setError('');
            setSubmitting(true);

            try {
                await submit();
            } catch (err) {
                setError(err.message);
            } finally {
                setSubmitting(false);
            }
        },
        [validate, submit],
    );

    return { error, submitting, handleSubmit };
}
