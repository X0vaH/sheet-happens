import StepActions from '../../components/characterCreation/StepActions.jsx';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import CharacterOverview from '../../components/characterCreation/CharacterOverview.jsx';
import DerivedStats from '../../components/characterCreation/DerivedStats.jsx';
import SavingThrows from '../../components/characterCreation/SavingThrows.jsx';
import SummaryTabs from '../../components/characterCreation/SummaryTabs.jsx';
import SummaryNotices from '../../components/characterCreation/SummaryNotices.jsx';
import { useCharacterSummary } from '../../hooks/useCharacterSummary.js';
import { useSaveCharacter } from '../../hooks/useSaveCharacter.js';

export default function CharacterSummary() {
    return (
        <CreationStep step="summary">
            <SummaryContent />
        </CreationStep>
    );
}

// Rendered by <CreationStep> only once every earlier step is complete, so the draft is
// safe to read without checking it.
function SummaryContent() {
    const summary = useCharacterSummary();
    const { save, saving, errors, sessionExpired } = useSaveCharacter(
        summary.payload,
    );
    return (
        <>
            <main className="summary-layout" aria-label="Résumé du personnage">
                <CharacterOverview withBackground heading="h1" />
                <div className="summary-details">
                    <SavingThrows
                        abilities={summary.abilities}
                        scores={summary.scores}
                        proficient={summary.proficientSaves}
                    />
                    <DerivedStats />
                    <SummaryTabs summary={summary} />
                </div>
            </main>
            <SummaryNotices
                unsaved={summary.unsavedChoices}
                sessionExpired={sessionExpired}
                errors={errors}
            />
            <StepActions className="background-actions summary-actions">
                <button
                    type="button"
                    onClick={save}
                    disabled={saving}
                    aria-busy={saving}
                >
                    {saving ? 'Sauvegarde…' : 'Sauvegarder'}
                </button>
            </StepActions>
        </>
    );
}
