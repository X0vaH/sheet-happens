import Navbar from '../global/Navbar.jsx';
import { creationSteps } from '../../utils/characterCreation/creationSteps.js';
import CreationBackground from './CreationBackground.jsx';

const skeletonSteps = creationSteps.filter((step) => !step.applies);

export default function CreationCatalogShell({ children }) {
    return (
        <div className="creation-page creation-compact-type creation-mobile-design">
            <CreationBackground />
            <Navbar />
            <Navbar creation />
            <nav
                id="creation-step-menu"
                className="creation-steps"
                aria-label="Étapes de création du personnage"
            >
                <ol>
                    {skeletonSteps.map(({ id, label }) => (
                        <li
                            key={id}
                            aria-current={id === 'info' ? 'step' : undefined}
                        >
                            <span>{label}</span>
                        </li>
                    ))}
                </ol>
            </nav>
            {children}
        </div>
    );
}
