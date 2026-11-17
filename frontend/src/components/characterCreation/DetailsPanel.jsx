// Read-only description of the currently selected race or class. `children` are
// shown only when something is selected.
export default function DetailsPanel({
    id,
    className,
    title,
    emptyTitle,
    description,
    emptyDescription,
    selected,
    children,
}) {
    return (
        <section
            className={['race-panel', className].filter(Boolean).join(' ')}
            aria-labelledby={id + '-title'}
            aria-live="polite"
        >
            <h2 id={id + '-title'}>{title || emptyTitle}</h2>
            <p>{description || emptyDescription}</p>
            {selected && children}
        </section>
    );
}
