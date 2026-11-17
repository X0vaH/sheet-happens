import { writeFile } from 'node:fs/promises';

// Reproducible import of the French SRD 2014 spell catalog, restricted to level 0–3.
const revision = await fetch('https://api.github.com/repos/5e-bits/5e-database/commits/main').then((r) => r.json());
const base = `https://raw.githubusercontent.com/5e-bits/5e-database/${revision.sha}/`;
const spells = await fetch(base + 'src/2014/fr-FR/5e-SRD-Spells.json').then((r) => r.json());
const aliases = {
    'fire-bolt': 'Trait de feu', light: 'Lumière', prestidigitation: 'Prestidigitation',
    'mage-hand': 'Main du mage', thaumaturgy: 'Thaumaturgie', 'magic-missile': 'Projectile magique',
    shield: 'Bouclier', 'cure-wounds': 'Soins', bless: 'Bénédiction', 'detect-magic': 'Détection de la magie',
    guidance: 'Assistance', resistance: 'Résistance', 'spare-the-dying': 'Stabilisation', command: 'Injonction',
    'ray-of-frost': 'Rayon de givre', message: 'Message', identify: 'Identification', 'feather-fall': 'Chute de plumes',
    'sacred-flame': 'Flamme sacrée', sanctuary: 'Sanctuaire', sleep: 'Sommeil', 'mage-armor': 'Armure du mage',
    'charm-person': 'Charme-personne', 'mirror-image': 'Image miroir', 'misty-step': 'Pas brumeux',
    'scorching-ray': 'Rayon ardent', invisibility: 'Invisibilité', web: 'Toile d’araignée',
    'hold-person': 'Immobilisation de personne', fireball: 'Boule de feu', fly: 'Vol', counterspell: 'Contresort',
    'dispel-magic': 'Dissipation de la magie', haste: 'Hâte', 'lightning-bolt': 'Éclair',
    'lesser-restoration': 'Restauration partielle', 'spiritual-weapon': 'Arme spirituelle',
    'beacon-of-hope': 'Lueur d’espoir', revivify: 'Revigorer',
};
const catalog = spells.filter((spell) => spell.level <= 3 && spell.classes.some((c) => ['cleric', 'wizard'].includes(c.index)))
    .map((spell) => ({
        index: spell.index,
        name: aliases[spell.index] ?? spell.name,
        spell_level: spell.level,
        school: spell.school.index,
        casting_time: spell.casting_time,
        range: spell.range,
        duration: spell.duration,
        concentration: spell.concentration,
        ritual: spell.ritual,
        verbal: spell.components.includes('V'),
        somatic: spell.components.includes('S'),
        material: spell.components.includes('M'),
        material_description: spell.material ?? null,
        description: [...spell.desc, ...(spell.higher_level?.length ? ['Aux niveaux supérieurs', ...spell.higher_level] : [])].join('\n\n'),
        classes: spell.classes.filter((c) => ['cleric', 'wizard'].includes(c.index)).map((c) => c.index === 'cleric' ? 'Clerc' : 'Magicien'),
    }));
if (catalog.length < 50 || catalog.some((spell) => !spell.description)) throw new Error('Incomplete SRD catalog');
await writeFile(new URL('./spells-2014.json', import.meta.url), JSON.stringify(catalog, null, 2) + '\n');
const license = await fetch(base + 'LICENSE.md').then((r) => r.text());
await writeFile(new URL('./SRD-LICENSE.md', import.meta.url), `# Sources du catalogue de sorts 2014\n\nRévision : ${revision.sha}\n\nTraduction française : ${base}src/2014/fr-FR/5e-SRD-Spells.json\n\nAdaptations : sélection des sorts du Clerc et du Magicien de niveaux 0 à 3, noms harmonisés avec le projet, paragraphes regroupés sans supprimer les règles de lancement à un niveau supérieur.\n\nThis work includes material taken from the System Reference Document 5.1 (“SRD 5.1”) by Wizards of the Coast LLC and available at https://media.dndbeyond.com/compendium-images/srd/5.1/SRD_CC_v5.1.pdf. The SRD 5.1 is licensed under the Creative Commons Attribution 4.0 International License available at https://creativecommons.org/licenses/by/4.0/legalcode.\n\n## Licence du jeu de données 5e-bits\n\n${license}`);
console.log(`Imported ${catalog.length} spells at ${revision.sha}`);
const featureAliases = {
    'reckless-attack': 'Attaque téméraire', 'danger-sense': 'Sens du danger',
    'fast-movement': 'Déplacement rapide', 'barbarian-extra-attack': 'Attaque supplémentaire',
    'action-surge-1-use': "Sursaut d'action", 'cunning-action': 'Ruse',
    'uncanny-dodge': 'Esquive instinctive', 'frenzy': 'Frénésie',
    'evocation-savant': 'Savant en évocation', 'sculpt-spells': 'Sculpture des sorts',
    'fast-hands': 'Mains agiles', 'second-story-work': 'Monte-en-l’air',
    'channel-divinity-1-rest': 'Canalisation d’énergie divine',
    'channel-divinity-turn-undead': 'Renvoi des morts-vivants',
    'channel-divinity-preserve-life': 'Préservation de la vie',
    'destroy-undead-cr-1-2-or-below': 'Destruction des morts-vivants',
};
const features = await fetch(base + 'src/2014/fr-FR/5e-SRD-Features.json').then((r) => r.json());
const selectedFeatures = features.filter((f) => featureAliases[f.index]).map((f) => ({
    index: f.index, name: featureAliases[f.index], description: f.desc.join('\n\n'),
}));
if (selectedFeatures.length !== Object.keys(featureAliases).length) {
    throw new Error('Missing feature indices: ' + Object.keys(featureAliases).filter((key) => !features.some((f) => f.index === key)).join(', '));
}
await writeFile(new URL('./features-2014.json', import.meta.url), JSON.stringify(selectedFeatures, null, 2) + '\n');
