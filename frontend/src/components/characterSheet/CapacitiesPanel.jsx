function CapacitiesPanel({ character }) {
    return (
        <div className="capacities-panel">
            <h2>Capacités et traits</h2>

            <div className="capacities-rows">
                {character.masterySections.capacities.map((capacity, index) => (
                    <div key={index} className="capacity-row">
                        <p>{capacity}</p>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default CapacitiesPanel;
