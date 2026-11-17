import SelectField from './SelectField.jsx';

// Language choices of the background step; `slots` comes from `useLanguageSlots`.
export default function LanguagePicker({ slots }) {
    return (
        <section
            className="race-panel background-picker language-picker"
            aria-labelledby="language-title"
        >
            <h2 id="language-title">Langues</h2>
            <h3>Langues connues : {slots.known.join(', ') || 'Aucune'}</h3>

            {Array.from({ length: slots.limit }, (_, index) => (
                <div className="language-choice" key={index}>
                    <h3>Langue {index + 1}</h3>
                    <SelectField
                        id={`language-${index}`}
                        aria-label={`Choisir une langue ${index + 1}`}
                        placeholder="Choisir une langue"
                        options={slots.optionsFor(index)}
                        value={slots.chosen[index] ?? ''}
                        aria-describedby={`language-description-${index}`}
                        onChange={(event) =>
                            slots.choose(index, event.target.value)
                        }
                    />
                    <p
                        id={`language-description-${index}`}
                        className="background-description"
                    >
                        {slots.describe(index) ||
                            'Choisissez une langue pour consulter sa description.'}
                    </p>
                </div>
            ))}
        </section>
    );
}
