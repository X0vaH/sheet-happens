import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import characterClient from '../../api/characterClient.js';
import { apiError } from '../../api/errors.js';
import { isFighter } from '../../utils/characterMapping.js';
import { canLevelUp } from '../../utils/characterLevelUp.js';

function SheetIdentityCard({ character }) {
    const navigate = useNavigate();
    const levelUpAvailable = canLevelUp(character);
    const [deleting, setDeleting] = useState(false);
    const [deleteError, setDeleteError] = useState('');

    async function handleDelete() {
        if (
            !window.confirm(
                `Supprimer définitivement ${character.name} ? Cette action est irréversible.`,
            )
        ) {
            return;
        }

        setDeleting(true);
        setDeleteError('');
        try {
            await characterClient.delete(character.id);
            navigate('/', { replace: true });
        } catch (err) {
            setDeleteError(apiError(err));
            setDeleting(false);
        }
    }

    return (
        <div className="sheet-identity-card">
            <div className="sheet-identity-text">
                <h1>{character.name}</h1>
                <p>Race : {character.race}</p>
                <p>Classe : {character.classe}</p>
                {isFighter(character.classe) && (
                    <p title={character.fightingStyle?.description}>
                        Style de combat : {character.fightingStyle?.name ?? '—'}
                    </p>
                )}
                <p>Historique : {character.background}</p>
                {deleteError && (
                    <p className="sheet-delete-error" role="alert">
                        {deleteError}
                    </p>
                )}
            </div>

            <div className="sheet-level-badge">
                <span>Level</span>
                <strong>{character.level}</strong>
            </div>

            <div className="sheet-identity-actions">
                <button
                    type="button"
                    className="sheet-delete-button"
                    disabled={deleting}
                    onClick={handleDelete}
                >
                    {deleting ? 'Suppression…' : 'Supprimer'}
                </button>
                <button
                    type="button"
                    className="sheet-upgrade-button"
                    disabled={!levelUpAvailable}
                    onClick={() =>
                        navigate(`/character/${character.id}/level-up`)
                    }
                >
                    {levelUpAvailable ? 'Améliorer' : 'Niveau maximum atteint'}
                </button>
            </div>
        </div>
    );
}

export default SheetIdentityCard;
