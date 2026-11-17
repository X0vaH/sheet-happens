import { formatSigned } from '../../utils/characterCreation/creationOptions.js';

function SheetSavePanel({ saveEntries }) {
    return (
        <div className="sheet-save-panel">
            <h2>Jets de sauvegardes</h2>
            <div className="save-rows">
                {saveEntries.map(({ label, value, proficient }) => (
                    <div
                        className={`save-row ${proficient ? 'is-proficient' : ''}`}
                        key={label}
                    >
                        <div className="save-label-block">
                            {proficient && (
                                <span
                                    className="save-bullet"
                                    aria-hidden="true"
                                />
                            )}
                            <span>{label}</span>
                        </div>
                        <div className="save-score-box">
                            {formatSigned(value, { compact: true })}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default SheetSavePanel;
