import CharacterCardInfo from './CharacterCardInfo.jsx';

function CarouselItemPopulated({ character }) {
    return (
        <div className="character-card">
            <div className="character-card-portrait">
                <img src={character.image} alt={character.name} />
                <p className="character-name">{character.name}</p>
                <div className="character-level">
                    <span>Level</span>
                    <span>{character.level}</span>
                </div>
            </div>
            <CharacterCardInfo character={character} />
        </div>
    );
}

export default CarouselItemPopulated;
