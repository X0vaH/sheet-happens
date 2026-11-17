import { test, expect } from './helpers/test.js';
import { characterLanguages } from '../src/utils/characterCreation/characterLanguages.js';
import {
    alignmentNames,
    classProficiencies,
    languageDescription,
} from '../src/utils/characterCreation/catalogExtras.js';
import { creationOptions as options } from './fixtures/creationOptions.js';

test('racial and chosen languages are grouped without duplicates', () => {
    expect(characterLanguages(options, 'Elfe', 'Soldat', 'Elfique')).toEqual({
        racial: ['Commun', 'Elfique'],
        spoken: ['Commun', 'Elfique'],
    });
});

test('background choices are preserved without being listed as spoken languages', () => {
    expect(characterLanguages(options, 'Humain', 'Sage', 'Abyssal')).toEqual({
        racial: ['Commun'],
        spoken: ['Commun', 'Abyssal'],
    });
    expect(characterLanguages(options, 'Nain', 'Guide', '').racial).toEqual([
        'Commun',
        'Nain',
    ]);
});

test('a language description comes from the catalog, empty when unknown', () => {
    expect(languageDescription(options, 'Elfique')).toBe(
        'Langue des Elfes, fluide et mélodieuse, possédant une riche tradition littéraire.',
    );
    expect(languageDescription(options, 'Klingon')).toBe('');
});

test('alignments are empty when the API omits them', () => {
    expect(alignmentNames({})).toEqual([]);
});

test('class proficiencies accept labels or typed objects', () => {
    expect(classProficiencies(options, 'Magicien')).toContain('Dagues');
    expect(
        classProficiencies(
            {
                classes: [
                    {
                        name: 'X',
                        proficiencies: [{ type: 'armor', label: 'Boucliers' }],
                    },
                ],
            },
            'X',
        ),
    ).toEqual(['Boucliers']);
    expect(classProficiencies({}, 'X')).toEqual([]);
});
