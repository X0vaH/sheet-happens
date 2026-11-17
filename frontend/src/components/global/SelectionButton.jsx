export default function SelectionButton({
    value,
    selected,
    limit,
    onChange,
    accessibleDisabled = false,
    children,
    ...props
}) {
    const pressed = selected.includes(value);
    const full = !pressed && selected.length >= limit;
    return (
        <button
            {...props}
            type="button"
            aria-pressed={pressed}
            aria-disabled={accessibleDisabled ? full : undefined}
            disabled={accessibleDisabled ? undefined : full}
            onClick={() => {
                if (full) return;
                onChange(
                    pressed
                        ? selected.filter((entry) => entry !== value)
                        : [...selected, value],
                );
            }}
        >
            {children}
        </button>
    );
}
