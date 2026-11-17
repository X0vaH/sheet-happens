import SelectionButton from '../global/SelectionButton.jsx';
import DescriptionInfo from '../global/DescriptionInfo.jsx';

export default function LevelUpChoices({
    title,
    options,
    selected,
    limit,
    onChange,
}) {
    return (
        <section
            className="level-up-choices"
            aria-label={title ?? 'Choix de capacités'}
        >
            {title && <h2>{title}</h2>}
            <p>
                Choisissez {limit} option{limit > 1 ? 's' : ''}.
            </p>
            <div className="level-up-subclass-options">
                {options.map((option) => (
                    <DescriptionInfo
                        key={option.id}
                        label={`Informations sur ${option.name}`}
                        description={
                            <>
                                <strong>{option.name}</strong>
                                <p>{option.description}</p>
                            </>
                        }
                    >
                        <SelectionButton
                            value={option.id}
                            selected={selected}
                            limit={limit}
                            onChange={onChange}
                        >
                            <strong>{option.name}</strong>
                        </SelectionButton>
                    </DescriptionInfo>
                ))}
            </div>
            <p aria-live="polite">
                {selected.length} / {limit} sélectionnés
            </p>
        </section>
    );
}
