import SettingsPage from '../components/settings/SettingsPage.jsx';
import SettingsCard from '../components/settings/SettingsCard.jsx';
import SettingsBackButton from '../components/settings/SettingsBackButton.jsx';
import SettingsButton from '../components/settings/SettingsButton.jsx';
import SettingsErrorBanner from '../components/settings/SettingsErrorBanner.jsx';
import SettingsForm from '../components/settings/SettingsForm.jsx';
import SettingsInput from '../components/settings/SettingsInput.jsx';
import { useChangeUsernameForm } from '../hooks/useChangeUsernameForm.js';
import { useAccountSettingsNavigation } from '../hooks/useAccountSettingsNavigation.js';

function ChangeUsernamePage() {
    const { username, onUsernameChange, error, submitting, handleSubmit } =
        useChangeUsernameForm();
    const { goBack } = useAccountSettingsNavigation();

    return (
        <SettingsPage>
            <SettingsErrorBanner message={error} />
            <SettingsCard
                title="Modification du nom d'utilisateur"
                leading={<SettingsBackButton onClick={goBack} />}
            >
                <SettingsForm onSubmit={handleSubmit}>
                    <SettingsInput
                        type="text"
                        placeholder="Nom d'utilisateur"
                        value={username}
                        onChange={onUsernameChange}
                        autoComplete="username"
                    />
                    <SettingsButton type="submit" disabled={submitting}>
                        {submitting ? 'Confirmation...' : 'Confirmer'}
                    </SettingsButton>
                </SettingsForm>
            </SettingsCard>
        </SettingsPage>
    );
}

export default ChangeUsernamePage;
