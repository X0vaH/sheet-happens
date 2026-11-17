import CharacteristicBox from './CharacteristicBox.jsx';

function SheetCharacteristicsPanel({ statEntries }) {
    return (
        <div className="sheet-characteristics-panel">
            <h2>Caractéristiques</h2>
            <div className="characteristics-grid">
                {statEntries.map(([label, value], index) => (
                    <CharacteristicBox
                        label={label}
                        value={value}
                        key={index}
                    />
                ))}
            </div>
        </div>
    );
}

export default SheetCharacteristicsPanel;
