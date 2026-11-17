// A skill toggle: its ability and modifier, and a note when the background already grants it.
// `locked` keeps it unavailable for now (aria-disabled) while `granted` also disables it.
export default function SkillButton({
    label,
    ability,
    modifier,
    granted,
    active,
    locked,
    onToggle,
}) {
    return (
        <button
            type="button"
            aria-label={label}
            aria-pressed={active}
            aria-disabled={locked}
            disabled={granted}
            onClick={onToggle}
        >
            <strong>{label}</strong>
            {granted && <span>Accordée par l’historique</span>}
            <span className="skill-base-modifier">
                {ability} {modifier}
            </span>
        </button>
    );
}
