import StepActions from '../../components/characterCreation/StepActions.jsx';
import ChoicePanel from '../../components/characterCreation/ChoicePanel.jsx';
import {
    characterLevel,
    classProficiencies,
} from '../../utils/characterCreation/catalogExtras.js';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    choiceFeatures,
    classLevelOne,
    findByName,
    names,
} from '../../utils/characterCreation/catalog.js';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import IdentityAside from '../../components/characterCreation/IdentityAside.jsx';
import OptionGroupPanel from '../../components/characterCreation/OptionGroupPanel.jsx';
import DetailsPanel from '../../components/characterCreation/DetailsPanel.jsx';
import { DetailBlock } from '../../components/characterCreation/DetailList.jsx';
import {
    useDraftChoice,
    useDraftObject,
    useDraftText,
} from '../../hooks/useDraftState.js';

export default function ChooseClass() {
    const options = useCreationOptions();
    const classes = names(options.classes);
    const [characterClass, setCharacterClass] = useDraftChoice(
        'characterClass',
        classes,
    );
    const [subclass, setSubclass] = useDraftText('subclass');
    const [featureOptions, setFeatureOptions] =
        useDraftObject('featureOptions');
    const profile = findByName(options.classes, characterClass);
    const levelOne = classLevelOne(options, characterClass);
    const featuresWithChoices = choiceFeatures(options, characterClass);

    function selectClass(value) {
        setCharacterClass(value);
        if (value === characterClass) return;
        setSubclass('');
        setFeatureOptions({});
    }

    function selectFeatureOption(featureId, optionId) {
        setFeatureOptions({ ...featureOptions, [featureId]: optionId });
    }

    return (
        <CreationStep step="class">
            <main className="class-layout" aria-labelledby="class-title">
                <IdentityAside className="class-summary" />
                <div className="race-content class-content">
                    <ChoicePanel
                        title="Choix de la classe"
                        titleId="class-title"
                        label="Classe du personnage"
                        options={classes}
                        selected={characterClass}
                        onSelect={selectClass}
                    />
                    <DetailsPanel
                        id="class-description"
                        className="class-description"
                        title={characterClass}
                        emptyTitle="Choisissez une classe"
                        description={profile?.description}
                        emptyDescription="Sélectionnez une classe pour consulter ses caractéristiques."
                        selected={profile}
                    >
                        <div className="class-details">
                            <div>
                                <p>
                                    <strong>Dé de vie</strong> d
                                    {profile?.hit_die}
                                </p>
                                <DetailBlock
                                    title="Jets de sauvegarde"
                                    items={profile?.saving_throws ?? []}
                                />
                                <DetailBlock
                                    title={`Capacités niveau ${characterLevel(options)}`}
                                    items={levelOne.features}
                                    describe={(feature) => feature.description}
                                />
                            </div>
                            <DetailBlock
                                title="Maîtrises"
                                items={classProficiencies(
                                    options,
                                    characterClass,
                                )}
                                renderItem={(value) => value}
                            />
                        </div>
                    </DetailsPanel>
                </div>
                <div className="class-equipment-column">
                    {levelOne.subclasses.length > 0 && (
                        <OptionGroupPanel
                            id="subclass"
                            className="class-domain"
                            title="Sous-classe"
                            description="Choisissez votre sous-classe"
                            options={levelOne.subclasses}
                            isSelected={(option) => subclass === option.name}
                            onSelect={(option) => setSubclass(option.name)}
                        />
                    )}
                    {featuresWithChoices.map((feature) => (
                        <OptionGroupPanel
                            key={feature.id}
                            id={`feature-${feature.id}`}
                            className="class-domain"
                            title={feature.name}
                            description={feature.description}
                            options={feature.options}
                            isSelected={(option) =>
                                featureOptions[feature.id] === option.id
                            }
                            onSelect={(option) =>
                                selectFeatureOption(feature.id, option.id)
                            }
                        />
                    ))}
                </div>
            </main>
            <StepActions className="class-actions" />
        </CreationStep>
    );
}
