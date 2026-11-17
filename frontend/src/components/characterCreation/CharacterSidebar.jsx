import CompactAbilities from './CompactAbilities.jsx';
import DerivedStats from './DerivedStats.jsx';
import IdentitySummary from './IdentitySummary.jsx';

// Side summary of the steps after the ability scores: identity card plus derived
// stats, and optionally the compact ability scores inside the identity card.
export default function CharacterSidebar({
    className,
    identityClassName,
    heading,
    withAbilities = false,
}) {
    const identity = <IdentitySummary heading={heading} />;
    return (
        <aside className={className} aria-label="Résumé du personnage">
            <div className={'race-summary race-panel ' + identityClassName}>
                {withAbilities ? (
                    <>
                        <div className="creation-identity-panel">
                            {identity}
                        </div>
                        <div className="creation-abilities-panel">
                            <h3>Caractéristiques</h3>
                            <CompactAbilities />
                        </div>
                    </>
                ) : (
                    identity
                )}
            </div>
            <DerivedStats />
        </aside>
    );
}
