import StepActions from '../../components/characterCreation/StepActions.jsx';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import CharacterSidebar from '../../components/characterCreation/CharacterSidebar.jsx';
import AbilityScoreInput from '../../components/characterCreation/AbilityScoreInput.jsx';
import { useAbilityAssignment } from '../../hooks/useAbilityAssignment.js';

export default function ChooseAbilities() {
    const {
        labels,
        methods,
        method,
        manual,
        pool,
        scores,
        available,
        selectMethod,
        setScore,
        cycleScore,
    } = useAbilityAssignment();

    return (
        <CreationStep step="abilities">
            <main
                className="abilities-layout"
                aria-labelledby="abilities-title"
            >
                <CharacterSidebar
                    className="abilities-summary"
                    identityClassName="abilities-identity"
                />
                <section className="abilities-content">
                    <div className="race-panel abilities-method">
                        <h2 id="abilities-title">
                            Méthode de génération
                            <br />
                            des caractéristiques
                        </h2>
                        <div
                            className="race-options"
                            role="group"
                            aria-label="Méthode de génération"
                        >
                            {methods.map((value) => (
                                <button
                                    type="button"
                                    key={value}
                                    aria-pressed={method === value}
                                    onClick={() => selectMethod(value)}
                                >
                                    {value}
                                </button>
                            ))}
                        </div>
                    </div>
                    {!manual && pool.length > 0 && (
                        <p
                            className="abilities-message race-panel"
                            aria-live="polite"
                        >
                            Disponibles : {available.join(', ') || 'Aucun'}.
                        </p>
                    )}
                    <div className="abilities-grid">
                        {labels.map((label, index) => (
                            <AbilityScoreInput
                                key={label}
                                label={label}
                                index={index}
                                score={scores[index]}
                                manual={manual}
                                canCycle={pool.length > 0}
                                onCycle={(direction) =>
                                    cycleScore(index, direction)
                                }
                                onChange={(value) => setScore(index, value)}
                            />
                        ))}
                    </div>
                </section>
            </main>
            <StepActions className="abilities-actions" />
        </CreationStep>
    );
}
