// Rows of a <dl> in the race and background detail panels.
export function DetailRow({ label, children }) {
    return (
        <div>
            <dt>{label}</dt> <dd>{children}</dd>
        </div>
    );
}

// A row whose value is a list of catalog entries ({ id, name, ... }).
// `describe` sets the tooltip of each entry and `renderItem` its content.
export default function DetailList({
    label,
    items,
    describe,
    renderItem = (item) => item.name,
}) {
    return (
        <div>
            <dt>{label}</dt>
            <dd>
                <ul>
                    {items.map((item) => (
                        <li key={item.id} title={describe?.(item)}>
                            {renderItem(item)}
                        </li>
                    ))}
                </ul>
            </dd>
        </div>
    );
}

// A titled list outside of a <dl> (class details). Entries are catalog objects
// or plain strings; `renderItem` overrides how one is displayed.
export function DetailBlock({ title, items, describe, renderItem }) {
    return (
        <div>
            <h3>{title}</h3>
            <ul>
                {items.map((item) => (
                    <li key={item.id ?? item} title={describe?.(item)}>
                        {renderItem ? renderItem(item) : item.name}
                    </li>
                ))}
            </ul>
        </div>
    );
}
