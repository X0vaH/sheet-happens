import SettingsPage from '../components/settings/SettingsPage.jsx';
import SettingsCard from '../components/settings/SettingsCard.jsx';
import SettingsBackButton from '../components/settings/SettingsBackButton.jsx';
import SettingsButton from '../components/settings/SettingsButton.jsx';
import SettingsErrorBanner from '../components/settings/SettingsErrorBanner.jsx';
import SettingsForm from '../components/settings/SettingsForm.jsx';
import SettingsInput from '../components/settings/SettingsInput.jsx';
import { useChangePasswordForm } from '../hooks/useChangePasswordForm.js';
import { useAccountSettingsNavigation } from '../hooks/useAccountSettingsNavigation.js';

function ChangePasswordPage() {
    const {
        currentPassword,
        password,
        passwordConfirmation,
        onCurrentPasswordChange,
        onPasswordChange,
        onPasswordConfirmationChange,
        error,
        submitting,
        handleSubmit,
    } = useChangePasswordForm();
    const { goBack } = useAccountSettingsNavigation();

    return (
        <SettingsPage>
            <SettingsErrorBanner message={error} />
            <SettingsCard
                title="Modification du mot de passe"
                leading={<SettingsBackButton onClick={goBack} />}
            >
                <SettingsForm onSubmit={handleSubmit}>
                    <SettingsInput
                        type="password"
                        placeholder="Ancien mot de passe"
                        value={currentPassword}
                        onChange={onCurrentPasswordChange}
                        autoComplete="current-password"
                    />
                    <SettingsInput
                        type="password"
                        placeholder="Nouveau mot de passe"
                        value={password}
                        onChange={onPasswordChange}
                        autoComplete="new-password"
                    />
                    <SettingsInput
                        type="password"
                        placeholder="Confirmer le mot de passe"
                        value={passwordConfirmation}
                        onChange={onPasswordConfirmationChange}
                        autoComplete="new-password"
                    />
                    <SettingsButton type="submit" disabled={submitting}>
                        {submitting ? 'Confirmation...' : 'Confirmer'}
                    </SettingsButton>
                </SettingsForm>
            </SettingsCard>
        </SettingsPage>
    );
}

export default ChangePasswordPage;
