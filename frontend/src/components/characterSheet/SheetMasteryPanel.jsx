function SheetMasteryPanel({
    TAB_LABELS,
    activeTab,
    setActiveTab,
    tabContent,
}) {
    return (
        <div className="sheet-mastery-panel">
            <div className="mastery-tabs">
                {TAB_LABELS.map((label) => (
                    <button
                        type="button"
                        key={label}
                        className={`mastery-tab ${activeTab === label ? 'is-active' : ''}`}
                        onClick={() => setActiveTab(label)}
                    >
                        {label}
                    </button>
                ))}
            </div>

            <div className="mastery-content">
                <h3>{activeTab}</h3>
                {tabContent[activeTab].map((item) => (
                    <p key={item}>{item}</p>
                ))}
            </div>
        </div>
    );
}

export default SheetMasteryPanel;
