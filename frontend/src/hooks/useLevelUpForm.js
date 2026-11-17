import { useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiError } from '../api/errors.js';
import { applyLevelUp } from '../api/characterLevelUp.js';
import {
    levelUpConfig,
    buildLevelUpPayload,
} from '../utils/characterLevelUp.js';
import { modifier } from '../utils/characterCreation/creationOptions.js';

export default function useLevelUpForm(character, options) {
    const navigate = useNavigate();
    const [featureChoices, setFeatureChoices] = useState({});
    const [increases, setIncreases] = useState({});
    const [spells, setSpells] = useState([]);
    const [cantrips, setCantrips] = useState([]);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');
    const submitting = useRef(false);
    const level = options.next_level;
    const config = levelUpConfig(options);
    const [feat, setFeat] = useState(() =>
        level === 4
            ? (config.feats.find(
                  (entry) => entry.name === 'Ability Score Improvement',
              )?.id ?? null)
            : null,
    );
    const selectedFeat = config.feats.find((entry) => entry.id === feat);
    const abilityPoints =
        level === 4
            ? (selectedFeat?.ability_increase_count ??
              (selectedFeat?.name === 'Ability Score Improvement' ? 2 : 0))
            : config.abilityPoints;
    const subclass = config.subclasses[0];
    const abilities = options.available_options.abilities;
    const constitutionAbility = abilities.find(
        (ability) =>
            ability.abbreviation === 'CON' || ability.name === 'Constitution',
    );
    const oldConstitution = modifier(constitutionAbility?.score ?? 10);
    const constitution = modifier(
        (constitutionAbility?.score ?? 10) +
            (increases[constitutionAbility?.id] ?? 0),
    );
    const baseHp = config.hitDie / 2 + 1;
    const hpGain = Math.max(1, baseHp + constitution);
    const constitutionAdjustment =
        Math.max(1, config.hitDie + constitution) -
        Math.max(1, config.hitDie + oldConstitution) +
        (character.level - 1) *
            (Math.max(1, baseHp + constitution) -
                Math.max(1, baseHp + oldConstitution));
    const nextHp = character.hp + hpGain + constitutionAdjustment;
    const totalPoints = Object.values(increases).reduce(
        (sum, value) => sum + value,
        0,
    );
    const missing = [
        level === 4 &&
            !config.feats.some((entry) => entry.id === feat) &&
            'Choisissez une amélioration des caractéristiques ou un don.',
        ...config.features
            .filter((feature) => feature.choice_count > 0)
            .map((feature) => {
                const selected = featureChoices[feature.id] ?? [];
                return (
                    (selected.length !== feature.choice_count ||
                        selected.some(
                            (id) =>
                                !feature.options.some(
                                    (option) => option.id === id,
                                ),
                        )) &&
                    `Choisissez ${feature.choice_count} options pour ${feature.name}.`
                );
            }),
        abilityPoints &&
            totalPoints !== abilityPoints &&
            `Attribuez les ${abilityPoints} points de caractéristiques.`,
        spells.length !== config.spellCount &&
            `Choisissez ${config.spellCount} nouveaux sorts.`,
        cantrips.length !== config.cantripCount &&
            `Choisissez ${config.cantripCount} nouveau tour de magie.`,
    ].filter(Boolean);

    async function confirmLevelUp() {
        if (missing.length || submitting.current) return;
        submitting.current = true;
        setSaving(true);
        setError('');
        try {
            await applyLevelUp(
                character.id,
                buildLevelUpPayload(options, {
                    feat,
                    featureChoices,
                    increases,
                    spells,
                    cantrips,
                }),
            );
            navigate('/character/' + character.id, { replace: true });
        } catch (err) {
            setError(apiError(err));
        } finally {
            submitting.current = false;
            setSaving(false);
        }
    }

    return {
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
    };
}
