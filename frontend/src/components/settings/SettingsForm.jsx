import '../../styles/account-settings/SettingsForm.css';

function SettingsForm({ onSubmit, children }) {
    return (
        <form className="settings-form" onSubmit={onSubmit} noValidate>
            {children}
        </form>
    );
}

export default SettingsForm;
