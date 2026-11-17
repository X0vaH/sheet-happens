import { Link } from 'react-router-dom';

// What went wrong around saving: choices the catalog cannot store, an ended session, or the
// errors the save was refused with.
export default function SummaryNotices({ unsaved, sessionExpired, errors }) {
    return (
        <>
            {unsaved.length > 0 && (
                <section
                    className="race-notice summary-unsaved"
                    aria-labelledby="summary-unsaved-title"
                >
                    <h2 id="summary-unsaved-title">Choix non enregistrés</h2>
                    <p>
                        Ces choix ne font pas encore partie du catalogue et ne
                        seront pas sauvegardés avec le personnage :
                    </p>
                    <ul>
                        {unsaved.map((choice) => (
                            <li key={choice}>{choice}</li>
                        ))}
                    </ul>
                </section>
            )}
            {sessionExpired && (
                <p className="race-notice" role="alert">
                    Votre session a expiré.{' '}
                    <Link to="/login">Reconnectez-vous</Link> puis réessayez.
                </p>
            )}
            {errors.length > 0 && (
                <div className="race-notice" role="alert">
                    <p>La sauvegarde a échoué :</p>
                    <ul>
                        {errors.map((message) => (
                            <li key={message}>{message}</li>
                        ))}
                    </ul>
                </div>
            )}
        </>
    );
}
