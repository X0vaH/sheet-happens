import { useContext } from 'react';
import { CreationStepContext } from '../../context/CreationStepContext.js';
import { characterLevel } from '../../utils/characterCreation/catalogExtras.js';
import { findByName } from '../../utils/characterCreation/catalog.js';
import { useCreationDraft } from '../../hooks/useCreationDraft.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import { hasReachedStep } from '../../utils/characterCreation/creationSteps.js';
import AlignmentSummary from './AlignmentSummary.jsx';
import SubclassSummary from './SubclassSummary.jsx';

// Identity lines shared by every creation step, read from the draft. A line only shows once
// the wizard reached the step that chooses it, so early steps show what has been chosen so
// far; the background is only listed when `withBackground` is set (the final overview).
// Race/class are checked against the current catalog: a stale draft naming one the catalog
// no longer has shows the '—' placeholder instead of a dangling name.
export default function IdentitySummary({
    heading: Heading = 'h2',
    withBackground = false,
}) {
    const step = useContext(CreationStepContext);
    const options = useCreationOptions();
    const { name, race, characterClass, background } = useCreationDraft();
    const validRace = findByName(options.species, race) ? race : '';
    const validClass = findByName(options.classes, characterClass)
        ? characterClass
        : '';
    return (
        <>
            <Heading>{name}</Heading>
            <p>Niveau {characterLevel(options)}</p>
            <AlignmentSummary />
            {hasReachedStep(step, 'race') && <p>Race : {validRace || '—'}</p>}
            {hasReachedStep(step, 'class') && (
                <>
                    <p>Classe : {validClass || '—'}</p>
                    <SubclassSummary characterClass={validClass} />
                </>
            )}
            {withBackground && background && (
                <p className="summary-background">Historique : {background}</p>
            )}
        </>
    );
}
