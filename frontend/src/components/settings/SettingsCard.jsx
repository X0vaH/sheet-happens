import '../../styles/account-settings/SettingsCard.css';

function SettingsCard({ title, variant = 'narrow', leading, children }) {
    const withLeading = leading ? ' settings-card-with-leading' : '';

    return (
        <section
            className={`settings-card settings-card-${variant}${withLeading}`}
        >
            {leading}
            <h2 className="settings-card-title">{title}</h2>
            {children}
        </section>
    );
}

export default SettingsCard;
