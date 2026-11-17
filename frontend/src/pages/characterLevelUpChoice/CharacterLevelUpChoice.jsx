import { useRef } from 'react';
import { Link, useParams } from 'react-router-dom';
import useApiData from '../../hooks/useApiData.js';
import { useCharacter } from '../../hooks/useCharacters.js';
import { characterSheetData } from '../../utils/characterSheetData.js';
import { fetchLevelUpOptions } from '../../api/characterLevelUp.js';
import { canLevelUp } from '../../utils/characterLevelUp.js';
import ContinueButton from '../../components/characterCreation/ContinueButton.jsx';
import useLevelUpForm from '../../hooks/useLevelUpForm.js';
import LevelUpLayout from '../../components/characterLevelUpChoice/LevelUpLayout.jsx';
import AbilityIncreaseChoice from '../../components/characterLevelUpChoice/AbilityIncreaseChoice.jsx';
import SubclassChoice from '../../components/characterLevelUpChoice/SubclassChoice.jsx';
import LevelUpSpellChoice from '../../components/characterLevelUpChoice/LevelUpSpellChoice.jsx';
import LevelUpFeatures from '../../components/characterLevelUpChoice/LevelUpFeatures.jsx';

export default function CharacterLevelUpChoice() {
    const { characterId } = useParams();
    const { character: data, error } = useCharacter(characterId);
    if (error)
        return (
            <LevelUpLayout>
                <p role="alert">{error}</p>
                <Link to="/">Retour</Link>
            </LevelUpLayout>
        );
    if (!data)
        return <LevelUpLoading>Chargement du personnage…</LevelUpLoading>;
    const character = characterSheetData(data);
    if (!canLevelUp(character)) {
        return (
            <LevelUpLayout>
                <main className="level-up-loading">
                    <section className="creation-options-status">
                        <h1>Niveau maximum atteint</h1>
                        <p>
                            {character.name} est déjà au niveau{' '}
                            {character.level}.
                        </p>
                        <Link to={'/character/' + character.id}>
                            Retour à la fiche
                        </Link>
                    </section>
                </main>
            </LevelUpLayout>
        );
    }
    return (
        <LevelUpOptions
            key={character.id + '-' + character.level}
            character={character}
        />
    );
}

function LevelUpOptions({ character }) {
    const { data: options, error } = useApiData(
        fetchLevelUpOptions,
        character.id,
    );
    if (error)
        return (
            <LevelUpLayout>
                <p role="alert">{error}</p>
                <Link to={'/character/' + character.id}>Retour</Link>
            </LevelUpLayout>
        );
    if (!options) return <LevelUpLoading>Chargement des choix…</LevelUpLoading>;
    return <LevelUpForm character={character} options={options} />;
}

function LevelUpLoading({ children }) {
    return (
        <LevelUpLayout>
            <main className="level-up-loading" aria-busy="true">
                <div className="creation-options-status">
                    <p role="status">{children}</p>
                </div>
            </main>
        </LevelUpLayout>
    );
}

function LevelUpForm({ character, options }) {
    const confirmation = useRef(null);
    const {
        level,
        config,
        feat,
        setFeat,
        selectedFeat,
        abilityPoints,
        abilities,
        subclass,
        increases,
        setIncreases,
        featureChoices,
        setFeatureChoices,
        spells,
        setSpells,
        cantrips,
        setCantrips,
        saving,
        error,
        nextHp,
        constitutionAdjustment,
        totalPoints,
        missing,
        confirmLevelUp,
    } = useLevelUpForm(character, options);
    return (
        <LevelUpLayout>
            <main className="level-up-layout" aria-labelledby="level-up-title">
                <div className="level-up-column">
                    <section className="level-up-panel level-up-heading">
                        <h1 id="level-up-title">Niveau Supérieur</h1>
                        <p className="level-up-character-name">
                            {character.name}
                        </p>
                        <p className="level-up-number">Niveau {level}</p>
                        <p className="level-up-hit-points" aria-live="polite">
                            Augmentation des points de vie : {character.hp} →{' '}
                            {nextHp}
                        </p>
                        {config.notes.length > 0 && (
                            <div className="level-up-notes">
                                {config.notes.map((note) => (
                                    <p key={note}>{note}</p>
                                ))}
                            </div>
                        )}
                    </section>
                    {!!config.subclasses.length && (
                        <SubclassChoice options={config.subclasses} />
                    )}
                    {level === 4 && (
                        <section className="level-up-panel level-up-feat-choice">
                            <h2>Amélioration ou don</h2>
                            <label>
                                Choix du niveau 4
                                <select
                                    value={feat ?? ''}
                                    onChange={(event) => {
                                        setFeat(Number(event.target.value));
                                        setIncreases({});
                                    }}
                                >
                                    <option value="" disabled>
                                        Choisissez une option
                                    </option>
                                    {config.feats.map((entry) => (
                                        <option key={entry.id} value={entry.id}>
                                            {entry.name ===
                                            'Ability Score Improvement'
                                                ? 'Amélioration des caractéristiques'
                                                : entry.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <p>{selectedFeat?.description}</p>
                            <p>
                                Un don remplace les deux points de
                                caractéristiques.
                            </p>
                        </section>
                    )}
                    {!!abilityPoints && (
                        <AbilityIncreaseChoice
                            abilities={abilities}
                            points={abilityPoints}
                            total={totalPoints}
                            increases={increases}
                            onChange={setIncreases}
                        />
                    )}
                    {!!config.slots.length && !!abilityPoints && (
                        <SpellSlots slots={config.slots} />
                    )}
                </div>
                <div className="level-up-column">
                    <LevelUpFeatures
                        features={config.features}
                        selected={featureChoices}
                        onChange={setFeatureChoices}
                    />
                    {(!!config.spellCount || !!config.cantripCount) && (
                        <LevelUpSpellChoice
                            config={config}
                            spells={spells}
                            cantrips={cantrips}
                            onSpellsChange={setSpells}
                            onCantripsChange={setCantrips}
                        />
                    )}
                    {!!config.grantedSpells.length && (
                        <section className="level-up-panel level-up-features">
                            <h2>Sorts accessibles automatiquement</h2>
                            <p>
                                Ces sorts rejoignent votre liste. Les règles
                                habituelles de préparation s’appliquent ; les
                                sorts de domaine restent toujours préparés.
                            </p>
                            {config.grantedSpells.map((spell) => (
                                <details key={spell.id}>
                                    <summary>{spell.name}</summary>
                                    <p>{spell.description}</p>
                                </details>
                            ))}
                        </section>
                    )}
                    {!!config.slots.length && !abilityPoints && (
                        <SpellSlots slots={config.slots} />
                    )}
                </div>
            </main>
            <footer className="level-up-footer">
                <div className="level-up-actions">
                    <Link to={'/character/' + character.id}>Retour</Link>
                    <ContinueButton
                        disabled={saving}
                        missing={missing}
                        onClick={() => confirmation.current.showModal()}
                    >
                        {level === 5 ? 'Sauvegarder' : 'Continuer'}
                    </ContinueButton>
                </div>
            </footer>
            <dialog
                ref={confirmation}
                className="level-up-confirmation"
                aria-labelledby="confirmation-title"
                onClick={(event) => {
                    if (event.target === event.currentTarget)
                        confirmation.current.close();
                }}
            >
                {error && <p role="alert">{error}</p>}
                <h2 id="confirmation-title">Confirmer la montée de niveau</h2>
                <p>
                    {character.name} : niveau {character.level} → {level}
                </p>
                <p>
                    Points de vie : {character.hp} → {nextHp}
                </p>
                {constitutionAdjustment > 0 && (
                    <p>
                        Constitution : +{constitutionAdjustment} PV pour les
                        niveaux précédents.
                    </p>
                )}
                {subclass && <p>Sous-classe : {subclass.name}</p>}
                {feat !== null && (
                    <p>
                        Don :{' '}
                        {config.feats.find((entry) => entry.id === feat)?.name}
                    </p>
                )}
                {config.features
                    .filter((feature) => feature.choice_count > 0)
                    .map((feature) => (
                        <p key={feature.id}>
                            {feature.name} :{' '}
                            {feature.options
                                .filter((option) =>
                                    (featureChoices[feature.id] ?? []).includes(
                                        option.id,
                                    ),
                                )
                                .map((option) => option.name)
                                .join(', ')}
                        </p>
                    ))}
                {!!totalPoints && (
                    <p>
                        {Object.entries(increases)
                            .filter(([, value]) => value)
                            .map(
                                ([id, value]) =>
                                    `${abilities.find((ability) => String(ability.id) === id)?.name} +${value}`,
                            )
                            .join(', ')}
                    </p>
                )}
                {!!spells.length && (
                    <p>
                        Sorts :{' '}
                        {config.spells
                            .filter((spell) => spells.includes(spell.id))
                            .map((spell) => spell.name)
                            .join(', ')}
                    </p>
                )}
                {!!cantrips.length && (
                    <p>
                        Tour de magie :{' '}
                        {config.spells
                            .filter((spell) => cantrips.includes(spell.id))
                            .map((spell) => spell.name)
                            .join(', ')}
                    </p>
                )}
                <div className="level-up-actions">
                    <button
                        type="button"
                        autoFocus
                        onClick={() => confirmation.current.close()}
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        disabled={saving}
                        onClick={confirmLevelUp}
                    >
                        {saving ? 'Enregistrement…' : 'Confirmer'}
                    </button>
                </div>
            </dialog>
        </LevelUpLayout>
    );
}

function SpellSlots({ slots }) {
    return (
        <section
            className="level-up-panel level-up-slots"
            aria-label="Emplacements de sorts"
        >
            <h2>Emplacement de sorts</h2>
            {slots.map((count, index) => (
                <p key={index}>
                    Niveau {index + 1} : {count}
                </p>
            ))}
        </section>
    );
}
