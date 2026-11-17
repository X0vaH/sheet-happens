import { useRef } from 'react';
import { createPortal } from 'react-dom';

export default function ContinueButton({
    missing = [],
    onClick,
    type = 'button',
    form,
    disabled = false,
    children = 'Continuer',
}) {
    const dialog = useRef(null);
    const incomplete = missing.length > 0;

    return (
        <>
            <button
                type={type}
                form={form}
                disabled={disabled}
                aria-disabled={incomplete}
                onClick={(event) => {
                    if (incomplete) {
                        event.preventDefault();
                        dialog.current.showModal();
                        return;
                    }
                    onClick?.(event);
                }}
            >
                {children}
            </button>
            {createPortal(
                <dialog
                    ref={dialog}
                    className="creation-missing-dialog"
                    aria-label="Choix manquants"
                >
                    <h2>Il manque des éléments</h2>
                    <p>Pour continuer, complétez les éléments suivants :</p>
                    <ul>
                        {missing.map((item) => (
                            <li key={item}>{item}</li>
                        ))}
                    </ul>
                    <button
                        type="button"
                        onClick={() => dialog.current.close()}
                    >
                        Fermer
                    </button>
                </dialog>,
                document.body,
            )}
        </>
    );
}
