import { scoreBoundsFor } from '../../utils/characterCreation/catalogExtras.js';
import { formatModifier } from '../../utils/characterCreation/creationOptions.js';
import { validScore } from '../../utils/characterCreation/creationGuard.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import scoreBackground from '../../assets/ability-score-background.svg';

// One ability card: a number typed by hand (`manual`) or cycled through the generated
// values with the previous / next buttons, and the resulting modifier.
export default function AbilityScoreInput({
    label,
    index,
    score,
    manual,
    canCycle,
    onCycle,
    onChange,
}) {
    const options = useCreationOptions();
    const scoreBounds = scoreBoundsFor(options);
    const id = 'ability-' + index;
    const BLOCKED_KEYS = ['-', '+', 'e', 'E', '.', ','];

    const handleKeyDown = (event) => {
        if (!manual) return;
        if (BLOCKED_KEYS.includes(event.key)) event.preventDefault();
    };

    const handleInputChange = (event) => {
        if (!manual) return;
        // Retire tout ce qui n'est pas un chiffre (couvre le copier-coller)
        const digits = event.target.value.replace(/\D/g, '');
        onChange(digits === '' ? '' : Number(digits));
    };

    return (
        <div className="ability-card">
            <label htmlFor={id}>{label}</label>
            <div className="ability-score-picker">
                {!manual && (
                    <button
                        type="button"
                        aria-label={'Valeur précédente pour ' + label}
                        disabled={!canCycle}
                        onClick={() => onCycle(-1)}
                    >
                        ‹
                    </button>
                )}
                <input
                    id={id}
                    type="number"
                    min={scoreBounds.min}
                    max={scoreBounds.max}
                    step="1"
                    value={score}
                    readOnly={!manual}
                    onKeyDown={handleKeyDown}
                    onChange={(event) => handleInputChange(event)}
                    placeholder="—"
                />
                {!manual && (
                    <button
                        type="button"
                        aria-label={'Valeur suivante pour ' + label}
                        disabled={!canCycle}
                        onClick={() => onCycle(1)}
                    >
                        ›
                    </button>
                )}
            </div>
            <div className="ability-modifier">
                <img
                    src={scoreBackground}
                    width="90.91"
                    height="44.3097"
                    alt=""
                />
                <output aria-label={'Modificateur de ' + label}>
                    {validScore(score) ? formatModifier(score) : '—'}
                </output>
            </div>
        </div>
    );
}
