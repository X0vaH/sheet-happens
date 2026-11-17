function StatCard({ name, data, sub }) {
    return (
        <div className="stat-card stat-card-small">
            <span className="stat-label">{name}</span>
            <strong>{data}</strong>
            <span className="stat-sub">{sub}</span>
        </div>
    );
}

export default StatCard;
