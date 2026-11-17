import '../../styles/StatusBox.css';

function StatusBox({ children, isError = false, actions = false }) {
    const className = [
        'status-box',
        isError ? 'status-box-error' : '',
        actions ? 'status-box-actions' : '',
    ]
        .filter(Boolean)
        .join(' ');

    return (
        <div className={className} role={isError ? 'alert' : 'status'}>
            {children}
        </div>
    );
}

export default StatusBox;
