import { useId } from 'react';

function AuthInput({ label, ...inputProps }) {
    const id = useId();

    return (
        <>
            <label className="auth-label" htmlFor={id}>
                {label}
            </label>
            <input
                id={id}
                className="auth-input"
                placeholder={label}
                {...inputProps}
            />
        </>
    );
}

export default AuthInput;
