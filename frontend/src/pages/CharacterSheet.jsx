import { useState } from 'react';
import { Navigate, useParams } from 'react-router-dom';
import '../styles/CharacterSheet.css';
import Navbar from '../components/global/Navbar.jsx';
import StatusBox from '../components/global/StatusBox.jsx';
import background from '../assets/home_background.png';
import StatCard from '../components/characterSheet/StatCard.jsx';
import SheetIdentityCard from '../components/characterSheet/SheetIdentityCard.jsx';
import SheetMasteryPanel from '../components/characterSheet/SheetMasteryPanel.jsx';
import SheetSavePanel from '../components/characterSheet/SheetSavePanel.jsx';
import SheetCharacteristicsPanel from '../components/characterSheet/SheetCharacteristicsPanel.jsx';
import SheetSkillPanel from '../components/characterSheet/SheetSkillPanel.jsx';
import SheetAttackPanel from '../components/characterSheet/SheetAttackPanel.jsx';
import CapacitiesPanel from '../components/characterSheet/CapacitiesPanel.jsx';
import { useCharacter } from '../hooks/useCharacters.js';
import { formatClasse, getLevel } from '../utils/characterMapping.js';
import { formatSigned } from '../utils/characterCreation/creationOptions.js';

const TAB_LABELS = ['Autre maitrises et langues', 'Équipement'];

const EMPTY_LABEL = 'Aucun';

function orEmpty(entries) {
    return entries.length > 0 ? entries : [EMPTY_LABEL];
}

function formatWeaponDamage(weapon) {
    let damage = `${weapon.damage_dice} ${weapon.damage_type}`;
    if (weapon.versatile_damage_dice)
        damage += ` (${weapon.versatile_damage_dice} à deux mains)`;
    if (weapon.range_normal)
        damage += ` (portée ${weapon.range_normal}/${weapon.range_long})`;
    return damage;
}

function formatSpellDetails(spell) {
    const level =
        spell.spell_level === 0 ? 'Tour de magie' : `niv. ${spell.spell_level}`;
    return `${level}, ${spell.range}`;
}

function mapAttacks(character) {
    const weaponAttacks = (character.inventory ?? [])
        .filter((entry) => entry.item?.weapon)
        .map(({ item }) => ({
            name: item.name,
            atk: item.weapon.attack_bonus,
            damageType: formatWeaponDamage(item.weapon),
        }));

    const spellAttacks = (character.spells ?? []).map(({ spell }) => ({
        name: spell.name,
        atk: '—',
        damageType: formatSpellDetails(spell),
    }));

    const attacks = [...weaponAttacks, ...spellAttacks];
    return attacks.length > 0
        ? attacks
        : [{ name: EMPTY_LABEL, atk: '', damageType: '' }];
}

function mapMasterySections(character) {
    const languages = (character.languages ?? []).map(
        (entry) => entry.language.name,
    );
    const tools = (character.tools ?? []).map((entry) => entry.tool.name);
    const equipment = (character.inventory ?? []).map(({ item, quantity }) => {
        const name = quantity > 1 ? `${item.name} (x${quantity})` : item.name;
        return `${name} : ${item.description}`;
    });
    const features = (character.features ?? []).map(
        ({ feature }) => `${feature.name} : ${feature.description}`,
    );
    const feats = (character.feats ?? [])
        .filter(({ feat }) => feat.name !== 'Ability Score Improvement')
        .map(({ feat }) => `${feat.name} : ${feat.description}`);

    return {
        'Autre maitrises et langues': orEmpty([
            ...(languages.length > 0
                ? [`Langues : ${languages.join(', ')}`]
                : []),
            ...tools,
        ]),
        Équipement: orEmpty(equipment),
        capacities: orEmpty([...features, ...feats]),
    };
}

function mapCharacterForSheet(character) {
    const abilities = [...(character.abilities ?? [])].sort(
        (a, b) => a.ability.id - b.ability.id,
    );
    const skills = character.skills ?? [];

    return {
        id: character.id,
        nextLevel: character.next_level,
        name: character.name,
        race: character.origin?.specie?.name ?? 'Race inconnue',
        classe: formatClasse(character),
        fightingStyle: character.fighting_style,
        background: character.origin?.background?.name ?? 'Historique inconnu',
        level: getLevel(character),
        hp: character.max_health_points,
        speed: `${character.origin?.specie?.base_speed ?? 0} ft`,
        armourClass: character.armor_class,
        initiative: character.initiative,
        bonus: character.proficiency_bonus,
        stats: Object.fromEntries(
            abilities.map((entry) => [
                entry.ability.name,
                entry.effective_score,
            ]),
        ),
        saveEntries: (character.saving_throws ?? []).map(
            ({ ability, bonus, proficient }) => ({
                label: ability,
                value: bonus,
                proficient,
            }),
        ),
        skills: skills.map(({ name, bonus, proficient }) => ({
            label: name,
            value: bonus,
            active: proficient,
        })),
        skillModifiers: Object.fromEntries(
            skills.map(({ name, ability }) => [name, ability]),
        ),
        attacks: mapAttacks(character),
        masterySections: mapMasterySections(character),
    };
}

function CharacterSheet() {
    const { characterId } = useParams();
    const {
        character: apiCharacter,
        loading,
        error,
    } = useCharacter(characterId);
    const [activeTab, setActiveTab] = useState(TAB_LABELS[0]);

    if (loading) {
        return (
            <div className="sheet-page">
                <img className="sheet-background" src={background} alt="" />
                <Navbar />
                <StatusBox>Chargement du personnage...</StatusBox>
            </div>
        );
    }

    if (error || !apiCharacter) {
        return <Navigate to="/" replace />;
    }

    const character = mapCharacterForSheet(apiCharacter);
    const statEntries = Object.entries(character.stats);
    const tabContent = character.masterySections;

    return (
        <div className="sheet-page">
            <img className="sheet-background" src={background} alt="" />
            <Navbar />
            <main className="character-sheet-layout">
                <aside
                    className="sheet-left-column"
                    aria-label="Statistiques du personnage"
                >
                    <StatCard
                        name="Classe"
                        data={character.armourClass}
                        sub="Armure"
                    />
                    <StatCard
                        name="Initiative"
                        data={formatSigned(character.initiative, {
                            compact: true,
                        })}
                        sub="Bonus"
                    />
                    <StatCard
                        name="Bonus"
                        data={`+${character.bonus}`}
                        sub="Maîtrise"
                    />
                    <StatCard
                        name="Max points"
                        data={character.hp}
                        sub="de vie"
                    />
                    <StatCard
                        name="Vitesse"
                        data={character.speed}
                        sub="déplacement"
                    />
                </aside>
                <section className="sheet-center-column">
                    <SheetIdentityCard character={character} />
                    <SheetCharacteristicsPanel statEntries={statEntries} />
                    <SheetSavePanel saveEntries={character.saveEntries} />
                    <SheetMasteryPanel
                        TAB_LABELS={TAB_LABELS}
                        setActiveTab={setActiveTab}
                        activeTab={activeTab}
                        tabContent={tabContent}
                    />
                </section>
                <aside className="sheet-right-column">
                    <SheetSkillPanel
                        character={character}
                        skillModifiers={character.skillModifiers}
                    />
                    <SheetAttackPanel character={character} />
                    <CapacitiesPanel character={character} />
                </aside>
            </main>
        </div>
    );
}

export default CharacterSheet;
