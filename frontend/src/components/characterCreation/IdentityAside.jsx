import IdentitySummary from './IdentitySummary.jsx';

// The identity card shown beside the early creation steps.
export default function IdentityAside({ className }) {
    return (
        <aside
            className={['race-summary race-panel', className]
                .filter(Boolean)
                .join(' ')}
            aria-label="Résumé du personnage"
        >
            <IdentitySummary />
        </aside>
    );
}
