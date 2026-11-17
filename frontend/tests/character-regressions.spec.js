import { test, expect } from './helpers/test.js';
import { calculateArmorClass } from '../src/utils/characterCreation/armorClass.js';
import { creationOptions } from './fixtures/creationOptions.js';

const items = (names) =>
    names.map((name) =>
        creationOptions.items.find((item) => item.name === name),
    );
const armorClass = (characterClass, itemNames, scores, style = '') =>
    calculateArmorClass(
        creationOptions,
        characterClass,
        items(itemNames),
        scores,
        style,
    );

const armorCases = [
    ['Guerrier', ['Cotte de mailles', 'Bouclier'], 16, 13, 18],
    ['Guerrier', ['Cotte de mailles'], 8, 13, 16],
    ['Guerrier', ['Cuir', 'Bouclier'], 16, 13, 16],
    ['Clerc', ['Cotte de mailles', 'Bouclier'], 16, 13, 18],
    ['Clerc', ['Cotte de mailles'], 8, 13, 16],
    ['Clerc', ['Chemise de mailles', 'Bouclier'], 16, 13, 17],
    ['Clerc', ['Chemise de mailles'], 8, 13, 12],
    ['Roublard', [], 16, 13, 13],
    ['Magicien', [], 16, 13, 13],
    ['Barbare', [], 16, 16, 16],
];

for (const [
    index,
    [characterClass, itemNames, dexterity, constitution, expected],
] of armorCases.entries()) {
    test(`armor calculation ${index + 1}: ${characterClass}`, () => {
        expect(
            armorClass(characterClass, itemNames, [
                13,
                dexterity,
                constitution,
                13,
                13,
                13,
            ]),
        ).toBe(expected);
    });
}

test('unknown scores do not become numeric armor bonuses', () => {
    expect(armorClass('Clerc', ['Chemise de mailles'], [])).toBeNull();
    expect(armorClass('Barbare', [], [13, 16, ''])).toBeNull();
    expect(armorClass('Guerrier', ['Cotte de mailles', 'Bouclier'], [])).toBe(
        18,
    );
    expect(armorClass('', [], [])).toBeNull();
});

test('armor class follows the ability positions given by the catalog', () => {
    // Catalog ordered CON, DEX, ... instead of the usual FOR, DEX, CON.
    const [strength, dexterity, constitution, ...rest] =
        creationOptions.abilities;
    const reordered = {
        ...creationOptions,
        abilities: [constitution, dexterity, strength, ...rest],
    };
    expect(calculateArmorClass(reordered, 'Barbare', [], [16, 14, 13])).toBe(
        10 + 2 + 3,
    );
});
