// "Label : value" line of the summary tabs.
export default function Field({ label, children }) {
    return (
        <p>
            <strong>{label} :</strong> {children}
        </p>
    );
}
