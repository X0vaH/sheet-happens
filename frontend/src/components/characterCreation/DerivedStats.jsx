import { proficiencyBonus } from '../../utils/characterCreation/catalogExtras.js';
import { calculateArmorClass } from '../../utils/characterCreation/armorClass.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    chosenFeatureOptionName,
    derivedAbilityIndexes,
    findByName,
    grantedEquipmentItems,
} from '../../utils/characterCreation/catalog.js';
import { validScore } from '../../utils/characterCreation/creationGuard.js';
import { useCreationDraft } from '../../hooks/useCreationDraft.js';

export default function DerivedStats() {
    const options = useCreationOptions();
    const { scores, equipment, characterClass, featureOptions } =
        useCreationDraft();
    const mod = (score) =>
        validScore(score) ? Math.floor((score - 10) / 2) : null;
    const indexes = derivedAbilityIndexes(options);
    const dexterity = mod(scores[indexes.dexterity]);
    const constitution = mod(scores[indexes.constitution]);
    const hitDie = findByName(options.classes, characterClass)?.hit_die;
    const fightingStyle = chosenFeatureOptionName(
        options,
        characterClass,
        featureOptions,
        'Style de combat',
    );
    const items = grantedEquipmentItems(options, characterClass, equipment);
    const armorClass = calculateArmorClass(
        options,
        characterClass,
        items,
        scores,
        fightingStyle,
    );
    const summary = [
        [
            'Max points',
            constitution === null || !hitDie
                ? '—'
                : Math.max(1, hitDie + constitution),
            'de vie',
        ],
        ['Maîtrise', '+' + proficiencyBonus(options), 'Bonus'],
        [
            'Initiative',
            dexterity === null
                ? '—'
                : dexterity >= 0
                  ? '+' + dexterity
                  : dexterity,
            'Bonus',
        ],
        ['Classe', armorClass ?? '—', 'Armure'],
    ];
    return (
        <div className="abilities-derived" aria-live="polite">
            {summary.map(([label, value, suffix]) => (
                <div className="race-panel" key={label}>
                    <span>{label}</span>
                    <strong>{value}</strong>
                    <span>{suffix}</span>
                </div>
            ))}
        </div>
    );
}
