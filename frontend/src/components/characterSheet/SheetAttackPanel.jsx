function SheetAttackPanel({ character }) {
    return (
        <div className="sheet-right-panel">
            <h2 className="skills-title">Attaques/sorts</h2>

            <div className="attacks-header-row">
                <span>Nom</span>
                <span>Bonus ATK</span>
                <span>Dommages/Type</span>
            </div>

            <div className="attacks-list">
                {character.attacks.map(({ name, atk, damageType }) => (
                    <div className="attack-row" key={name}>
                        <span className="attack-name">{name}</span>
                        <span className="attack-atk">{atk}</span>
                        <span className="attack-damage">{damageType}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default SheetAttackPanel;
