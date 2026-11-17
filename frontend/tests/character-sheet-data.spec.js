import { test, expect } from '@playwright/test';
import { characterSheetData } from '../src/utils/characterSheetData.js';

test('keeps only the fields used by the level up page', () => {
    const character = characterSheetData({
        id: 42,
        name: 'Test',
        max_health_points: 38,
        levels: [{ character_level: 4 }, { character_level: 1 }],
        next_level: 5,
        abilities: [
            { ability_id: 2, ability: { name: 'Dextérité' }, score: 14 },
        ],
    });
    expect(character).toEqual({
        id: '42',
        name: 'Test',
        level: 4,
        hp: 38,
        nextLevel: 5,
    });
});

test('a character without levels is level 1 and a missing next level is null', () => {
    const character = characterSheetData({
        id: 1,
        name: 'Test',
        max_health_points: 10,
    });
    expect(character.level).toBe(1);
    expect(character.nextLevel).toBeNull();
});
