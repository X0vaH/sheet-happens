import IdentitySummary from './IdentitySummary.jsx';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    abilityNames,
    skillEntries,
} from '../../utils/characterCreation/catalog.js';
import { characterSkills } from '../../utils/characterCreation/characterSkills.js';
import { useCreationDraft } from '../../hooks/useCreationDraft.js';
import DerivedStats from './DerivedStats.jsx';
import CompactAbilities from './CompactAbilities.jsx';
import { formatModifier } from '../../utils/characterCreation/creationOptions.js';

export default function CharacterOverview({
    heading = 'h2',
    withBackground = false,
}) {
    const options = useCreationOptions();
    const { scores, background, skills: chosen } = useCreationDraft();
    const selected = characterSkills(options, background, chosen);
    const abilities = abilityNames(options);
    const skills = skillEntries(options);
    return (
        <aside
            className="race-summary race-panel background-summary"
            aria-label="Résumé du personnage"
        >
            <div className="creation-identity-panel">
                <IdentitySummary
                    heading={heading}
                    withBackground={withBackground}
                />
            </div>
            <div className="creation-overview-stats">
                <DerivedStats />
            </div>
            <div className="creation-abilities-panel">
                <h3 className="background-abilities-title">Caractéristiques</h3>
                <CompactAbilities />
            </div>
            <section className="creation-selected-skills">
                <h3 className="background-skills-title">
                    Compétences maitrisées
                </h3>
                <div className="background-selected-skills">
                    {selected.map((label) => {
                        const ability = skills.find(
                            ([value]) => value === label,
                        )[1];
                        return (
                            <div key={label}>
                                <strong>{label}</strong>
                                <span>
                                    {abilities[ability]}{' '}
                                    {formatModifier(scores[ability])}
                                </span>
                            </div>
                        );
                    })}
                </div>
            </section>
        </aside>
    );
}
