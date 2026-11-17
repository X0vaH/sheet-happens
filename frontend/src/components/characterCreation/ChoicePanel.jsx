export default function ChoicePanel({
    title,
    titleId,
    label,
    options,
    selected,
    onSelect,
}) {
    return (
        <section className="race-picker race-panel" aria-labelledby={titleId}>
            <h1 id={titleId}>{title}</h1>
            <div className="race-options" role="group" aria-label={label}>
                {options.map((value) => (
                    <button
                        type="button"
                        key={value}
                        aria-pressed={selected === value}
                        onClick={() => onSelect(value)}
                    >
                        {value}
                    </button>
                ))}
            </div>
        </section>
    );
}
