function AuthLayout({
    title,
    error,
    onSubmit,
    submitting,
    submitLabel,
    submittingLabel,
    hint,
    children,
}) {
    return (
        <div className="auth-page">
            {error && (
                <div className="auth-error-banner" role="alert">
                    {error}
                </div>
            )}

            <div className="auth-content">
                <h1 className="auth-brand">Sheet Happens</h1>

                <div className="auth-card">
                    <h2 className="auth-title">{title}</h2>

                    <form className="auth-form" onSubmit={onSubmit} noValidate>
                        {children}

                        <p className="auth-hint">{hint}</p>

                        <button
                            className="auth-submit"
                            type="submit"
                            disabled={submitting}
                        >
                            {submitting ? submittingLabel : submitLabel}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}

export default AuthLayout;
