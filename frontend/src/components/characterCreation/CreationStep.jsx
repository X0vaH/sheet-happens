import { Navigate } from 'react-router-dom';
import { CreationStepContext } from '../../context/CreationStepContext.js';
import { CreationStepFlowContext } from '../../context/CreationStepFlowContext.js';
import { useStepFlow } from '../../hooks/useStepFlow.js';
import CreationLayout from './CreationLayout.jsx';

// Frame every creation step renders: sends the user back to the first earlier step that is
// still incomplete, otherwise shows the step inside the wizard layout. Its children (the
// step content and <StepActions />) only render once the step is reachable. The computed
// flow is shared via context so <StepActions /> does not compute its own copy.
export default function CreationStep({ step, children }) {
    const flow = useStepFlow(step);
    if (flow.redirect) return <Navigate to={flow.redirect} replace />;
    return (
        <CreationStepContext.Provider value={step}>
            <CreationStepFlowContext.Provider value={flow}>
                <CreationLayout step={step}>{children}</CreationLayout>
            </CreationStepFlowContext.Provider>
        </CreationStepContext.Provider>
    );
}
