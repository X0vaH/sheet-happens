import { Link } from 'react-router-dom';
import Navbar from './global/Navbar.jsx';
import StatusBox from './global/StatusBox.jsx';
import CreationCatalogShell from './characterCreation/CreationCatalogShell.jsx';
import { useCharacterLimit } from '../hooks/useCharacterLimit.js';
import { CHARACTER_LIMIT_MESSAGE } from '../configs/characterLimit.js';

// Keeps the creation wizard closed once the user owns the maximum number of characters.
export default function CharacterLimitGuard({ children }) {
    const { limitReached, loading } = useCharacterLimit();

    if (loading) {
        return (
            <CreationCatalogShell>
                <StatusBox>Chargement des éléments de création...</StatusBox>
            </CreationCatalogShell>
        );
    }
    if (!limitReached) return children;

    return (
        <>
            <Navbar />
            <StatusBox isError>
                {CHARACTER_LIMIT_MESSAGE}. Supprimez-en un avant d’en créer un
                nouveau. <Link to="/">Retour à l’accueil</Link>
            </StatusBox>
        </>
    );
}
