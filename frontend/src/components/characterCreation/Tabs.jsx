// Accessible tab list with its panel. `children` is the content of the active tab.
export default function Tabs({
    idPrefix,
    label,
    tabs,
    activeTab,
    onChange,
    children,
}) {
    function handleKeyDown(event, index) {
        const keys = {
            ArrowRight: (index + 1) % tabs.length,
            ArrowLeft: (index + tabs.length - 1) % tabs.length,
            Home: 0,
            End: tabs.length - 1,
        };
        if (keys[event.key] === undefined) return;
        event.preventDefault();
        onChange(keys[event.key]);
        event.currentTarget.parentElement.children[keys[event.key]].focus();
    }
    return (
        <>
            <div role="tablist" aria-label={label}>
                {tabs.map((tab, index) => (
                    <button
                        type="button"
                        role="tab"
                        id={`${idPrefix}-${index}`}
                        key={tab}
                        aria-selected={activeTab === index}
                        aria-controls={`${idPrefix}-panel`}
                        tabIndex={activeTab === index ? 0 : -1}
                        onClick={() => onChange(index)}
                        onKeyDown={(event) => handleKeyDown(event, index)}
                    >
                        {tab}
                    </button>
                ))}
            </div>
            <div
                id={`${idPrefix}-panel`}
                role="tabpanel"
                aria-labelledby={`${idPrefix}-${activeTab}`}
                tabIndex={0}
            >
                {children}
            </div>
        </>
    );
}
