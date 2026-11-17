import ButtonSelect from './ButtonSelect.jsx';
import { isFighter } from '../../utils/characterMapping.js';

function CharacterCardInfo({ character }) {
    return (
        <div className="character-card-info">
            <h2>Informations générales</h2>
            <p>Race : {character.race}</p>
            <p>Classe : {character.classe}</p>
            {isFighter(character.classe) && (
                <p title={character.fightingStyle?.description}>
                    Style de combat : {character.fightingStyle?.name ?? '—'}
                </p>
            )}
            <p>Alignement : {character.alignement}</p>
            <p>
                Prochaine évolution :{' '}
                {character.prochaineEvolution === null
                    ? 'Niveau maximum atteint'
                    : `Niveau ${character.prochaineEvolution}`}
            </p>
            <div className="character-actions">
                <ButtonSelect character={character} />
            </div>
        </div>
    );
}

export default CharacterCardInfo;
