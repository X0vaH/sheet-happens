import '../../styles/account-settings/SettingsField.css';

function SettingsField({ label, value, action }) {
    return (
        <div className="settings-field">
            <span className="settings-field-text">
                {label} : {value}
            </span>
            {action}
        </div>
    );
}

export default SettingsField;
