import '../../styles/account-settings/SettingsButton.css';

function SettingsButton({
    type = 'button',
    variant = 'primary',
    disabled = false,
    onClick,
    children,
}) {
    return (
        <button
            type={type}
            className={`settings-button settings-button-${variant}`}
            disabled={disabled}
            onClick={onClick}
        >
            {children}
        </button>
    );
}

export default SettingsButton;
