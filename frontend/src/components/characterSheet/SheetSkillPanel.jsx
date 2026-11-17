import { formatSigned } from '../../utils/characterCreation/creationOptions.js';

function SheetSkillPanel({ character, skillModifiers }) {
    return (
        <div className="sheet-right-panel">
            <h2 className="skills-title">Compétences</h2>

            <div className="skills-header-row">
                <span>Maitrise</span>
                <span>Modificateur</span>
                <span>Compétence</span>
                <span>Bonus</span>
            </div>

            <div className="skills-list">
                {character.skills.map(({ label, value, active }) => (
                    <div className="skill-row" key={label}>
                        <span
                            className={`skill-dot ${active ? 'active' : ''}`}
                            aria-hidden="true"
                        />
                        <span className="skill-modifier">
                            {skillModifiers[label] ?? '—'}
                        </span>
                        <span className="skill-label">{label}</span>
                        <span className="skill-bonus">
                            {formatSigned(value, { compact: true })}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default SheetSkillPanel;
