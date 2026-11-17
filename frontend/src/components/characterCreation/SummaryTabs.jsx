import { useState } from 'react';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import { names } from '../../utils/characterCreation/catalog.js';
import {
    characterLevel,
    classProficiencies,
} from '../../utils/characterCreation/catalogExtras.js';
import Field from './Field.jsx';
import Tabs from './Tabs.jsx';
import ItemList from './ItemList.jsx';

// The tabbed details of the summary step; `summary` comes from `useCharacterSummary`.
export default function SummaryTabs({ summary }) {
    const options = useCreationOptions();
    const [activeTab, setActiveTab] = useState(1);
    const {
        tabs,
        skills,
        characterClass,
        subclass,
        featureOptions,
        background,
        backgroundProfile,
        species,
        levelOne,
        languages,
        spells,
        grantedSpells,
        equipmentOptions,
    } = summary;
    return (
        <section className="race-panel summary-tabs">
            <Tabs
                idPrefix="summary-tab"
                label="Détails du personnage"
                tabs={tabs}
                activeTab={activeTab}
                onChange={setActiveTab}
            >
                <h2>{tabs[activeTab]}</h2>
                {activeTab === 1 && (
                    <ItemList
                        items={equipmentOptions.flatMap(
                            (option) => option.items,
                        )}
                        inlineDescription
                    />
                )}
                {activeTab === 0 && (
                    <>
                        <Field label="Compétences maîtrisées">
                            {skills.join(', ')}
                        </Field>
                        <Field label="Outils maîtrisés">
                            {names(backgroundProfile.tools).join(', ') ||
                                'Aucun'}
                        </Field>
                        <Field label="Maîtrises de classe">
                            {classProficiencies(options, characterClass).join(
                                ', ',
                            ) || 'Aucune'}
                        </Field>
                    </>
                )}
                {activeTab === 2 && (
                    <>
                        {subclass && (
                            <Field label="Sous-classe">{subclass}</Field>
                        )}
                        <Field label="Traits raciaux">
                            {names(species.features).join(', ')}
                        </Field>
                        <Field label="Vitesse">{species.base_speed} m</Field>
                        <Field
                            label={`Capacités de classe (niveau ${characterLevel(options)})`}
                        >
                            {levelOne.features
                                .map((feature) => {
                                    const option = feature.options?.find(
                                        ({ id }) =>
                                            id === featureOptions[feature.id],
                                    );
                                    return option
                                        ? `${feature.name} (${option.name} — ${option.description})`
                                        : feature.name;
                                })
                                .join(', ')}
                        </Field>
                    </>
                )}
                {activeTab === 3 && (
                    <>
                        <Field label="Historique">{background}</Field>
                        <Field label="Description">
                            {backgroundProfile.description}
                        </Field>
                        <Field label="Compétences maîtrisées">
                            {names(backgroundProfile.skills).join(', ')}
                        </Field>
                        <Field label="Outils maîtrisés">
                            {names(backgroundProfile.tools).join(', ') ||
                                'Aucun'}
                        </Field>
                        <Field label="Don d’origine">
                            {names(backgroundProfile.feats).join(', ') ||
                                'Aucun'}
                        </Field>
                    </>
                )}
                {activeTab === 4 && (
                    <Field label="Langues parlées">
                        {languages.spoken.join(', ')}
                    </Field>
                )}
                {activeTab === 5 && (
                    <>
                        {grantedSpells.length > 0 && (
                            <Field label="Sorts accordés par la classe">
                                {grantedSpells.join(', ')}
                            </Field>
                        )}
                        {spells.cantrips.length > 0 && (
                            <Field label="Sorts mineurs choisis">
                                {spells.cantrips.join(', ')}
                            </Field>
                        )}
                        {spells.levelOne.length > 0 && (
                            <Field label="Sorts de niveau 1">
                                {spells.levelOne.join(', ')}
                            </Field>
                        )}
                    </>
                )}
            </Tabs>
        </section>
    );
}
