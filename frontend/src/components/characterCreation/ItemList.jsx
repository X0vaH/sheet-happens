import { itemLabel } from '../../utils/characterCreation/catalog.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';

// Starting equipment quantities and descriptions from the full item catalog.
export default function ItemList({ items, label, inlineDescription = false }) {
    const options = useCreationOptions();

    return (
        <ul className="equipment-item-list">
            {items.map((item) => {
                const description = options.items.find(
                    (entry) => entry.id === item.id,
                )?.description;

                return (
                    <li key={item.id}>
                        {!inlineDescription && item.name === label
                            ? item.quantity > 1 && `× ${item.quantity}`
                            : itemLabel(item)}
                        {description && (
                            <span className="equipment-item-description">
                                {inlineDescription && ' : '}
                                {description}
                            </span>
                        )}
                    </li>
                );
            })}
        </ul>
    );
}
