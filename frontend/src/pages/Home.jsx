import Header from '../components/global/Header.jsx';
import Navbar from '../components/global/Navbar.jsx';
import StatusBox from '../components/global/StatusBox.jsx';
import CharacterCard from '../components/home/CharacterCard.jsx';
import background from '../assets/home_background.png';
import guerrier from '../assets/guerrier.png';
import magicien from '../assets/magicien.png';
import barbare from '../assets/barbare.png';
import clerc from '../assets/clerc.png';
import roublard from '../assets/roublard.png';
import { useEffect } from 'react';
import { useCharacters } from '../hooks/useCharacters.js';
import { useCharacterLimit } from '../hooks/useCharacterLimit.js';
import { formatClasse, getLevel } from '../utils/characterMapping.js';

const CLASS_IMAGES = {
    Guerrier: guerrier,
    Magicien: magicien,
    Barbare: barbare,
    Clerc: clerc,
    Roublard: roublard,
};

function mapCharacterForCard(character) {
    const level = getLevel(character);
    const className = character.classes?.[0]?.class_definition?.name;

    return {
        id: character.id,
        name: character.name,
        image: CLASS_IMAGES[className] ?? guerrier,
        race: character.origin?.specie?.name ?? 'Race inconnue',
        classe: formatClasse(character),
        fightingStyle: character.fighting_style,
        alignement: character.alignment ?? 'Alignement non défini',
        level,
        prochaineEvolution: character.next_level,
        orderIndex: character.order_index,
    };
}

function Home() {
    const { characters, loading, error } = useCharacters();
    const { setCharacters } = useCharacterLimit();
    const cards = characters.map(mapCharacterForCard);

    // Keep the creation limit and free slots in step with the list just loaded (new or deleted characters).
    useEffect(() => {
        if (!loading && !error) setCharacters(characters);
    }, [loading, error, characters, setCharacters]);

    return (
        <div className="home">
            <img className="home-background" src={background} alt="" />
            <div className="home-content">
                <Navbar />
                <Header />
                {loading && (
                    <StatusBox>Chargement des personnages...</StatusBox>
                )}
                {!loading && error && <StatusBox isError>{error}</StatusBox>}
                {!loading && !error && <CharacterCard characters={cards} />}
            </div>
        </div>
    );
}

export default Home;
