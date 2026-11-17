import { useCallback, useContext, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { CreationStepContext } from '../context/CreationStepContext.js';
import {
    incompleteStepPath,
    missingFor,
    nextStepPath,
    previousStepPath,
} from '../utils/characterCreation/creationSteps.js';
import { readCreationDraft } from '../utils/characterCreation/creationSnapshot.js';
import { useCreationOptions } from './useCreationOptions.js';

// What a creation step needs to know about the wizard, from the draft saved so far:
// `redirect` is the route of an earlier incomplete step, `missing` the labels of this
// step's choices still to make, `backTo`/`nextTo` the neighbouring steps' routes (null at
// either end) and `goNext()` moves to the next one. Inside <CreationStep> the step id
// comes from context; a page rendering <CreationStep> itself passes it. Memoized on the
// draft snapshot (stable between writes, see creationSnapshot.js) and the catalog, so it
// is only recomputed when either actually changes.
export function useStepFlow(step) {
    const contextStep = useContext(CreationStepContext);
    const id = step ?? contextStep;
    const options = useCreationOptions();
    const navigate = useNavigate();
    const draft = readCreationDraft();
    const flow = useMemo(() => {
        const nextTo = nextStepPath(options, id, draft.characterClass);
        return {
            redirect: incompleteStepPath(options, id, draft),
            missing: missingFor(id, options, draft),
            backTo: previousStepPath(options, id, draft.characterClass),
            nextTo,
        };
    }, [id, options, draft]);
    const goNext = useCallback(
        () => navigate(flow.nextTo),
        [navigate, flow.nextTo],
    );
    return { ...flow, goNext };
}
