import { useLayoutEffect } from 'react';
import Navbar from '../global/Navbar.jsx';
import { hasSpellStep } from '../../utils/characterCreation/creationFlow.js';
import {
    activeSteps,
    stepRoute,
} from '../../utils/characterCreation/creationSteps.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import { useCreationDraft } from '../../hooks/useCreationDraft.js';
import { Link } from 'react-router-dom';
import CreationBackground from './CreationBackground.jsx';

export default function CreationLayout({ step = 'info', children }) {
    const mobileDesign = true;
    const options = useCreationOptions();
    const { characterClass } = useCreationDraft();
    const caster = hasSpellStep(options, characterClass);
    const steps = activeSteps(options, characterClass);
    const current = steps.findIndex(({ id }) => id === step);
    const page = step === 'info' ? '' : step;
    useLayoutEffect(() => {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    }, [step]);

    return (
        <div
            className={[
                'creation-page',
                'creation-compact-type',
                mobileDesign && 'creation-mobile-design',
                page && 'creation-' + page + '-page',
                caster && 'creation-wizard-page',
            ]
                .filter(Boolean)
                .join(' ')}
        >
            <CreationBackground />
            <Navbar />
            {mobileDesign && <Navbar creation />}
            <nav
                id="creation-step-menu"
                className="creation-steps"
                aria-label="Étapes de création du personnage"
            >
                <ol>
                    {steps.map(({ id, label, path }, index) => (
                        <li
                            key={id}
                            aria-current={
                                index === current ? 'step' : undefined
                            }
                        >
                            {index < current ? (
                                <Link to={stepRoute({ path })}>{label}</Link>
                            ) : (
                                <span>{label}</span>
                            )}
                        </li>
                    ))}
                </ol>
            </nav>
            {children}
        </div>
    );
}
