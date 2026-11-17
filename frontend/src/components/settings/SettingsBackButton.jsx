import '../../styles/account-settings/SettingsBackButton.css';

function SettingsBackButton({ onClick }) {
    return (
        <button
            type="button"
            className="settings-back-button"
            onClick={onClick}
            aria-label="Retour"
        >
            ←
        </button>
    );
}

export default SettingsBackButton;
