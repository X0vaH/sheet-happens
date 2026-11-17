import '../../styles/account-settings/SettingsErrorBanner.css';

function SettingsErrorBanner({ message }) {
    if (!message) return null;

    return (
        <div className="settings-error-banner" role="alert">
            {message}
        </div>
    );
}

export default SettingsErrorBanner;
