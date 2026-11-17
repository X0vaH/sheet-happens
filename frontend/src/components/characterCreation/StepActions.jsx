import { useContext } from 'react';
import { Link } from 'react-router-dom';
import { CreationStepFlowContext } from '../../context/CreationStepFlowContext.js';
import ContinueButton from './ContinueButton.jsx';

// Back / continue bar shown under every creation step, wired to the wizard: "Retour" goes to
// the previous step (`back` replaces it with any node) and "Continuer" is blocked by the
// step's missing choices (`missing` overrides them) before moving to the next step.
// `onContinue` runs first and can return `false` to stay; `type="submit"` leaves the
// advancing to the form; `children` replace the continue button (e.g. the final save).
export default function StepActions({
    className,
    back,
    onContinue,
    missing,
    type,
    form,
    children,
}) {
    const flow = useContext(CreationStepFlowContext);
    return (
        <div className={['race-actions', className].filter(Boolean).join(' ')}>
            {back ?? <Link to={flow.backTo}>Retour</Link>}
            {children ?? (
                <ContinueButton
                    type={type}
                    form={form}
                    missing={missing ?? flow.missing}
                    onClick={
                        type === 'submit'
                            ? undefined
                            : () => {
                                  if (onContinue?.() !== false) flow.goNext();
                              }
                    }
                />
            )}
        </div>
    );
}
