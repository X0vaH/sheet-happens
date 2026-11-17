import '../../styles/account-settings/SettingsInput.css';

function SettingsInput({ type, placeholder, value, onChange, autoComplete }) {
    return (
        <input
            className="settings-input"
            type={type}
            placeholder={placeholder}
            value={value}
            onChange={onChange}
            autoComplete={autoComplete}
        />
    );
}

export default SettingsInput;
