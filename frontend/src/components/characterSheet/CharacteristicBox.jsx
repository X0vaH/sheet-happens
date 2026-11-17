function CharacteristicBox({ label, value }) {
    return (
        <div className="characteristic-box">
            <div className="characteristic-value-box">
                <span className="characteristic-modifier">
                    +{Math.max(0, Math.floor((value - 10) / 2))}
                </span>
            </div>
            <div className="characteristic-score-box">
                <span>{value}</span>
            </div>
            <span className="characteristic-name">{label}</span>
        </div>
    );
}

export default CharacteristicBox;
