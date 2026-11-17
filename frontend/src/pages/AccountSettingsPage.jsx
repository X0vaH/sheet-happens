import SettingsPage from '../components/settings/SettingsPage.jsx';
import SettingsCard from '../components/settings/SettingsCard.jsx';
import SettingsButton from '../components/settings/SettingsButton.jsx';
import SettingsField from '../components/settings/SettingsField.jsx';
import { useAuth } from '../hooks/useAuth.js';
import { useAccountSettingsNavigation } from '../hooks/useAccountSettingsNavigation.js';

function AccountSettingsPage() {
    const { user } = useAuth();
    const { goToUsername, goToPassword, goHome } =
        useAccountSettingsNavigation();

    return (
        <SettingsPage>
            <SettingsCard title="Informations du compte" variant="wide">
                <SettingsField label="Adresse courriel" value={user?.email} />
                <SettingsField
                    label="Nom d'utilisateur"
                    value={user?.name}
                    action={
                        <SettingsButton variant="edit" onClick={goToUsername}>
                            Modifier
                        </SettingsButton>
                    }
                />
                <SettingsField
                    label="Mot de passe"
                    value="**********"
                    action={
                        <SettingsButton variant="edit" onClick={goToPassword}>
                            Modifier
                        </SettingsButton>
                    }
                />
                <SettingsButton onClick={goHome}>Terminer</SettingsButton>
            </SettingsCard>
        </SettingsPage>
    );
}

export default AccountSettingsPage;
