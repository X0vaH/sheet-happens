import { expect } from '@playwright/test';

export async function openClass(
    page,
    characterClass = 'Guerrier',
    name = 'Arthur',
    completeChoices = true,
) {
    await page.goto('/character/create');
    await page.getByLabel('Nom du personnage').fill(name);
    await page
        .getByLabel('Alignement', { exact: true })
        .selectOption('Neutre bon');
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(
        page.getByRole('button', { name: 'Continuer' }),
    ).toBeDisabled();
    await expect(page.locator('[aria-pressed="true"]')).toHaveCount(0);
    await page.getByRole('button', { name: 'Humain', exact: true }).click();
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(
        page.getByRole('heading', { name: 'Choix de la classe' }),
    ).toBeVisible();
    await expect(
        page.getByRole('button', { name: 'Continuer' }),
    ).toBeDisabled();
    await expect(page.locator('[aria-pressed="true"]')).toHaveCount(0);
    if (completeChoices) {
        await pickClass(page, characterClass);
        await expect(
            page.getByRole('button', { name: 'Continuer' }),
        ).toBeEnabled();
    } else {
        await page
            .getByRole('button', { name: characterClass, exact: true })
            .click();
    }
}

// Classes with a level 1 subclass (e.g. Clerc) or a feature with options (Guerrier's
// Style de combat) need one chosen before the class step can continue.
export async function pickClass(page, characterClass) {
    await page
        .getByRole('button', { name: characterClass, exact: true })
        .click();
    const subclasses = page.getByRole('region', {
        name: 'Sous-classe',
        exact: true,
    });
    if (await subclasses.count())
        await subclasses.getByRole('button').first().click();
    const fightingStyle = page.getByRole('region', {
        name: 'Style de combat',
        exact: true,
    });
    if (await fightingStyle.count())
        await fightingStyle.getByRole('button').first().click();
}

// Picks the first option in every equipment choice group (automatically-granted groups have
// no buttons to click, just a static item list, so they're skipped). Waits for the page to
// settle and self-verifies, retrying once, since clicking options right as the page mounts
// can otherwise miss a group.
export async function selectEquipment(page) {
    if (page.url().endsWith('/class'))
        await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/equipment$/);
    const sections = page.locator('.equipment-content .equipment-section');
    await expect(sections.first()).toBeVisible();
    const groupCount = await sections.count();
    for (let attempt = 0; attempt < 2; attempt++) {
        for (let index = 0; index < groupCount; index++) {
            const firstOption = sections
                .nth(index)
                .locator('.domain-options button')
                .first();
            if (
                (await firstOption.count()) &&
                (await firstOption.getAttribute('aria-pressed')) !== 'true'
            )
                await firstOption.click();
        }
        if (
            (await page
                .getByRole('button', { name: 'Continuer' })
                .getAttribute('aria-disabled')) !== 'true'
        )
            break;
    }
}

export async function openAbilities(
    page,
    characterClass = 'Guerrier',
    name = 'Arthur',
) {
    await openClass(page, characterClass, name);
    await selectEquipment(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page).toHaveURL(/abilities$/);
}

export async function fillAbilityScores(page) {
    await page.getByRole('button', { name: 'Standard', exact: true }).click();
    for (const button of await page
        .getByRole('button', { name: 'Valeur suivante pour' })
        .all()) {
        await button.click();
    }
}

export async function openSkills(
    page,
    characterClass = 'Guerrier',
    name = 'Arthur',
) {
    await openAbilities(page, characterClass, name);
    await fillAbilityScores(page);
    await page.getByRole('button', { name: 'Continuer' }).click();
    await completeBackground(page);
    await expect(page).toHaveURL(/skills$/);
}

export async function selectSkills(page) {
    const choices = page.locator('.skills-choices button:not(:disabled)');
    await choices.nth(0).click();
    await choices.nth(1).click();
    await page.getByRole('button', { name: 'Continuer' }).click();
}

export async function completeBackground(page, background = 'Soldat') {
    await page
        .getByRole('combobox', { name: 'Choisir un historique' })
        .selectOption(background);
    for (const select of await page.locator('.language-choice select').all()) {
        if (!(await select.inputValue()))
            await select.selectOption({ index: 1 });
    }
    await page.getByRole('button', { name: 'Continuer' }).click();
}
