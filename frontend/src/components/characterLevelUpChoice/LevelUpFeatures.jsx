import LevelUpChoices from './LevelUpChoices.jsx';

export default function LevelUpFeatures({ features, selected, onChange }) {
    const automatic = features.filter((feature) => !(feature.choice_count > 0));
    const choices = features.filter((feature) => feature.choice_count > 0);

    return (
        <>
            {!!automatic.length && (
                <section className="level-up-panel level-up-features">
                    <h2>
                        {automatic.length > 1
                            ? 'Nouvelles Capacités'
                            : 'Nouvelle Capacité'}
                    </h2>
                    {automatic.map((feature) => (
                        <div key={feature.id}>
                            <h3>{feature.name}</h3>
                            <p>{feature.description}</p>
                        </div>
                    ))}
                </section>
            )}
            {choices.map((feature) => (
                <section
                    className="level-up-panel level-up-features"
                    key={feature.id}
                    aria-label={feature.name}
                >
                    <h2>Nouvelle Capacité</h2>
                    <h3>{feature.name}</h3>
                    <p>{feature.description}</p>
                    <LevelUpChoices
                        options={feature.options}
                        limit={feature.choice_count}
                        selected={selected[feature.id] ?? []}
                        onChange={(values) =>
                            onChange({ ...selected, [feature.id]: values })
                        }
                    />
                </section>
            ))}
        </>
    );
}
