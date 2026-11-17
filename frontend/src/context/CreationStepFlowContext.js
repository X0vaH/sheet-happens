import { createContext } from 'react';

// The step-flow object <CreationStep> computed for the current step (see useStepFlow),
// shared with <StepActions> so it does not run the same computation again.
export const CreationStepFlowContext = createContext(null);
