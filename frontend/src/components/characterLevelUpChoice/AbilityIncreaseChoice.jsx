import {
    modifier,
    signed,
} from '../../utils/characterCreation/creationOptions.js';

export default function AbilityIncreaseChoice({
    abilities,
    increases,
    onChange,
    points,
    total,
}) {
    return (
        <section
            className="level-up-panel level-up-abilities"
            aria-labelledby="increase-title"
        >
            <div className="level-up-ability-art" aria-hidden="true" />
            <h2 id="increase-title">Amélioration des caractéristiques</h2>
            <p>
                Attribuez {points} points de caractéristiques, sans dépasser 20.
            </p>
            <div className="level-up-stat-list">
                {abilities.map(({ id, name, score }) => (
                    <label key={name}>
                        <span>
                            {name} {score + (increases[id] ?? 0)} (
                            {signed(modifier(score + (increases[id] ?? 0)))})
                        </span>
                        <select
                            aria-label={'Augmentation de ' + name}
                            value={increases[id] ?? 0}
                            onChange={(event) =>
                                onChange({
                                    ...increases,
                                    [id]: Number(event.target.value),
                                })
                            }
                        >
                            {Array.from(
                                { length: Math.min(2, points) + 1 },
                                (_, value) => value,
                            ).map((value) => (
                                <option
                                    key={value}
                                    value={value}
                                    disabled={
                                        total - (increases[id] ?? 0) + value >
                                            points || score + value > 20
                                    }
                                >
                                    +{value}
                                </option>
                            ))}
                        </select>
                    </label>
                ))}
            </div>
            <p aria-live="polite">
                Choix fait :{' '}
                {Object.entries(increases)
                    .filter(([, value]) => value)
                    .map(
                        ([id, value]) =>
                            `${abilities.find((ability) => String(ability.id) === id)?.name} +${value}`,
                    )
                    .join(', ') || 'Aucun'}{' '}
                ({total} / {points})
            </p>
        </section>
    );
}
