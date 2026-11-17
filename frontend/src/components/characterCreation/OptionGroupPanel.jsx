// Titled panel of mutually exclusive choice buttons. Each option is
// { id, name, detail?, description?, ariaLabel? }: `detail` (any node) or else `description`
// (a catalog text) is rendered under the name, and `ariaLabel` overrides the accessible name
// when that text should not be part of it.
export default function OptionGroupPanel({
    id,
    className,
    title,
    description,
    options,
    isSelected,
    onSelect,
}) {
    const titleId = id + '-title';
    const descriptionId = description ? id + '-description' : undefined;
    return (
        <section
            className={['race-panel', className].filter(Boolean).join(' ')}
            aria-labelledby={titleId}
        >
            <h2 id={titleId}>{title}</h2>
            {description && <p id={descriptionId}>{description}</p>}
            <div
                className="domain-options"
                role="group"
                aria-labelledby={titleId}
                aria-describedby={descriptionId}
            >
                {options.map((option) => (
                    <button
                        type="button"
                        key={option.id}
                        aria-label={option.ariaLabel}
                        aria-pressed={isSelected(option)}
                        onClick={() => onSelect(option)}
                    >
                        <strong>{option.name}</strong>
                        {option.detail ??
                            (option.description && (
                                <span>{option.description}</span>
                            ))}
                    </button>
                ))}
            </div>
        </section>
    );
}
