import { draftFields } from '../../configs/characterStorage.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    choiceFeatures,
    classLevelOne,
} from '../../utils/characterCreation/catalog.js';
import {
    readDraft,
    readDraftObject,
} from '../../utils/characterCreation/creationDraft.js';

// Summarizes the level 1 choices a class needs — a subclass (Clerc's Domaine de la
// Vie) and/or an option on a class feature (Guerrier's Style de combat) — both driven
// generically by the catalog, so a new class with either kind of choice needs no changes here.
export default function SubclassSummary({ characterClass }) {
    const options = useCreationOptions();
    const levelOne = classLevelOne(options, characterClass);
    const featuresWithChoices = choiceFeatures(options, characterClass);
    if (levelOne.subclasses.length === 0 && featuresWithChoices.length === 0)
        return null;

    const featureOptions = readDraftObject(draftFields.featureOptions);
    return (
        <>
            {levelOne.subclasses.length > 0 && (
                <p>Sous-classe : {readDraft(draftFields.subclass) || '—'}</p>
            )}
            {featuresWithChoices.map((feature) => {
                const option = feature.options.find(
                    ({ id }) => id === featureOptions[feature.id],
                );
                return (
                    <p key={feature.id} title={option?.description}>
                        {feature.name} : {option?.name ?? '—'}
                    </p>
                );
            })}
        </>
    );
}
