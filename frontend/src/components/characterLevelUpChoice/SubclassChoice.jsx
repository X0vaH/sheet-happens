export default function SubclassChoice({ options }) {
    return (
        <section
            className="level-up-panel level-up-subclasses"
            aria-labelledby="subclass-title"
        >
            <h2 id="subclass-title">Vous obtenez maintenant la sous-classe</h2>
            <div className="level-up-subclass-options">
                {options.map(({ name, description, features }) => (
                    <div className="level-up-subclass-granted" key={name}>
                        <strong>{name}</strong>
                        <span className="level-up-subclass-description">
                            {description}
                        </span>
                        {!!features.length && (
                            <>
                                <span>Capacités</span>
                                <ul>
                                    {features.map((feature) => (
                                        <li key={feature.id}>
                                            <strong>{feature.name}</strong>
                                            <p>{feature.description}</p>
                                        </li>
                                    ))}
                                </ul>
                            </>
                        )}
                    </div>
                ))}
            </div>
        </section>
    );
}
