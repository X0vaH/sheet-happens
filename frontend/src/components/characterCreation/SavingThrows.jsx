import { proficiencyBonus } from '../../utils/characterCreation/catalogExtras.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    formatSigned,
    modifier,
} from '../../utils/characterCreation/creationOptions.js';

// Saving throw bonus of every ability: its modifier, plus the proficiency bonus for the
// abilities the class is proficient in (`proficient`, by ability name).
export default function SavingThrows({ abilities, scores, proficient }) {
    const options = useCreationOptions();
    return (
        <section
            className="race-panel summary-saves"
            aria-labelledby="summary-saves-title"
        >
            <h2 id="summary-saves-title">Jets de sauvegardes</h2>
            <div className="summary-save-grid">
                {abilities.map((label, index) => {
                    const isProficient = proficient.includes(label);
                    return (
                        <div
                            className="summary-save"
                            key={label}
                            aria-label={
                                label + (isProficient ? ', maîtrisé' : '')
                            }
                        >
                            <span
                                className={
                                    'summary-save-dot ' +
                                    (isProficient ? 'is-proficient' : '')
                                }
                                aria-hidden="true"
                            />
                            <span>{label}</span>
                            <strong>
                                {formatSigned(
                                    modifier(scores[index]) +
                                        (isProficient
                                            ? proficiencyBonus(options)
                                            : 0),
                                    { compact: true },
                                )}
                            </strong>
                        </div>
                    );
                })}
            </div>
        </section>
    );
}
