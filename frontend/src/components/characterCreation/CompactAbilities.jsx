import { useCreationDraft } from '../../hooks/useCreationDraft.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import { abilityNames } from '../../utils/characterCreation/catalog.js';
import { formatModifier } from '../../utils/characterCreation/creationOptions.js';
import scoreBackground from '../../assets/skill-score-background.svg';

export default function CompactAbilities() {
    const { scores } = useCreationDraft();
    const abilities = abilityNames(useCreationOptions());
    return (
        <div className="skills-abilities">
            {abilities.map((label, index) => (
                <div className="skill-ability" key={label} aria-label={label}>
                    <span>{label}</span>
                    <strong>{scores[index]}</strong>
                    <div className="skill-ability-modifier">
                        <img
                            src={scoreBackground}
                            width="47"
                            height="22.8372"
                            alt=""
                        />
                        <span>
                            {formatModifier(scores[index], { compact: true })}
                        </span>
                    </div>
                </div>
            ))}
        </div>
    );
}
