import SelectionButton from '../global/SelectionButton.jsx';
import { useState } from 'react';
import DescriptionInfo from '../global/DescriptionInfo.jsx';
import SpellDescription from '../global/SpellDescription.jsx';
import divider from '../../assets/level-up-divider.svg';

export default function LevelUpSpellChoice({
    config,
    spells,
    cantrips,
    onSpellsChange,
    onCantripsChange,
}) {
    const [tab, setTab] = useState(config.spellCount ? 'spells' : 'cantrips');
    const isCantrip = tab === 'cantrips';
    const selected = isCantrip ? cantrips : spells;
    const limit = isCantrip ? config.cantripCount : config.spellCount;
    const onChange = isCantrip ? onCantripsChange : onSpellsChange;
    const options = config.spells.filter((spell) =>
        isCantrip ? spell.spell_level === 0 : spell.spell_level > 0,
    );
    return (
        <section
            className="level-up-panel level-up-spells"
            aria-label="Choix des sorts"
        >
            {!!config.spellCount && !!config.cantripCount && (
                <>
                    <div
                        className="level-up-spell-tabs"
                        role="group"
                        aria-label="Catégorie de sorts"
                    >
                        <button
                            type="button"
                            aria-pressed={!isCantrip}
                            onClick={() => setTab('spells')}
                        >
                            Nouveaux sorts
                        </button>
                        <button
                            type="button"
                            aria-pressed={isCantrip}
                            onClick={() => setTab('cantrips')}
                        >
                            Tours de magie
                        </button>
                    </div>
                    <div className="level-up-divider">
                        <img src={divider} alt="" />
                    </div>
                </>
            )}
            <h2>{isCantrip ? 'Nouveau tour de magie' : 'Nouveaux sorts'}</h2>
            <p>
                {isCantrip
                    ? `Choisissez ${limit} tour de magie`
                    : `Choisissez ${limit} sorts à ajouter au grimoire`}
            </p>
            <div className="level-up-spell-options">
                {options.map((spell) => (
                    <DescriptionInfo
                        key={spell.id}
                        label={`Informations sur ${spell.name}`}
                        description={<SpellDescription spell={spell} />}
                    >
                        <SelectionButton
                            value={spell.id}
                            selected={selected}
                            limit={limit}
                            onChange={onChange}
                        >
                            <strong>{spell.name}</strong>
                            <small>
                                {spell.spell_level
                                    ? `Niveau ${spell.spell_level}`
                                    : 'Tour de magie'}
                            </small>
                        </SelectionButton>
                    </DescriptionInfo>
                ))}
            </div>
            <p aria-live="polite">
                {selected.length} / {limit} sélectionnés
            </p>
        </section>
    );
}
