import { useNavigate } from 'react-router-dom';

function ButtonSelect({ character }) {
    const navigate = useNavigate();

    return (
        <button
            type="button"
            className="character-sheet-button"
            onClick={() => navigate(`/character/${character.id}`)}
        >
            Voir la fiche
        </button>
    );
}

export default ButtonSelect;
