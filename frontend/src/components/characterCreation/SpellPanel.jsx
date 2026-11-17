import SelectionButton from '../global/SelectionButton.jsx';
import DescriptionInfo from '../global/DescriptionInfo.jsx';
import SpellDescription from '../global/SpellDescription.jsx';

export default function SpellPanel({
    id,
    title,
    subtitle,
    spells,
    selected,
    limit,
    onChange,
}) {
    return (
        <section
            className={
                'race-panel spells-picker ' +
                (id === 'cantrips' ? 'spells-cantrips' : 'spells-level-one')
            }
            aria-labelledby={id + '-title'}
        >
            <h2 id={id + '-title'}>{title}</h2>
            {subtitle && <p className="spells-subtitle">{subtitle}</p>}
            <div className="skills-choices" role="group" aria-label={title}>
                {spells.map((spell) => (
                    <DescriptionInfo
                        key={spell.id}
                        label={`Informations sur ${spell.name}`}
                        description={<SpellDescription spell={spell} />}
                    >
                        <SelectionButton
                            type="button"
                            aria-label={spell.name}
                            value={spell.name}
                            selected={selected}
                            limit={limit}
                            onChange={onChange}
                            accessibleDisabled
                        >
                            <strong>{spell.name}</strong>
                        </SelectionButton>
                    </DescriptionInfo>
                ))}
            </div>
            <p className="skills-count" aria-live="polite">
                {selected.length} / {limit} sélectionnés
            </p>
        </section>
    );
}
