import { useNavigate } from 'react-router-dom';
import PlusIcon from '../../assets/svg/PlusIcon.jsx';

function CarouselItemEmpty({ orderIndex }) {
    const navigate = useNavigate();

    return (
        <button
            type="button"
            className="character-card character-card-empty"
            aria-label="Créer un personnage"
            onClick={() =>
                navigate('/character/create', { state: { orderIndex } })
            }
        >
            <PlusIcon className="character-card-empty-icon" />
        </button>
    );
}

export default CarouselItemEmpty;
