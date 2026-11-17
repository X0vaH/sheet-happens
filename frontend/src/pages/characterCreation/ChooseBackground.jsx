import StepActions from '../../components/characterCreation/StepActions.jsx';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import { findByName, names } from '../../utils/characterCreation/catalog.js';
import { availableClassSkills } from '../../utils/characterCreation/characterSkills.js';
import { useStepFlow } from '../../hooks/useStepFlow.js';
import { useState } from 'react';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import CharacterOverview from '../../components/characterCreation/CharacterOverview.jsx';
import BackgroundDetails from '../../components/characterCreation/BackgroundDetails.jsx';
import LanguagePicker from '../../components/characterCreation/LanguagePicker.jsx';
import SelectField from '../../components/characterCreation/SelectField.jsx';
import { useCreationDraft } from '../../hooks/useCreationDraft.js';
import { useDraftArray, useDraftChoice } from '../../hooks/useDraftState.js';
import { useLanguageSlots } from '../../hooks/useLanguageSlots.js';

export default function ChooseBackground() {
    const options = useCreationOptions();
    const backgrounds = names(options.backgrounds);
    const { skills: selected } = useCreationDraft();
    const [background, setBackground] = useDraftChoice(
        'background',
        backgrounds,
    );
    const [, setSkills] = useDraftArray('skills');
    const languageSlots = useLanguageSlots(background);
    const [invalid, setInvalid] = useState(false);
    const { missing, goNext } = useStepFlow('background');

    function selectBackground(value) {
        setBackground(value);
        languageSlots.resetFor(value);
        setSkills(availableClassSkills(options, value, selected));
        setInvalid(false);
    }

    function handleContinue(event) {
        event.preventDefault();
        if (missing.length) {
            setInvalid(true);
            return;
        }
        goNext();
    }

    return (
        <CreationStep step="background">
            <main
                className="background-layout background-layout-with-language"
                aria-labelledby="background-title"
            >
                <CharacterOverview />
                <section className="race-panel background-picker">
                    <h1 id="background-title">Historique</h1>
                    <form
                        id="background-form"
                        onSubmit={handleContinue}
                        noValidate
                    >
                        <SelectField
                            aria-label="Choisir un historique"
                            placeholder="Choisir un historique"
                            options={backgrounds}
                            value={background}
                            required
                            aria-invalid={invalid}
                            aria-describedby={
                                invalid ? 'background-error' : undefined
                            }
                            onChange={(event) =>
                                selectBackground(event.target.value)
                            }
                        />
                        {invalid && (
                            <p
                                id="background-error"
                                className="background-error"
                                role="alert"
                            >
                                Choisissez un historique et toutes vos langues
                                avant de continuer.
                            </p>
                        )}
                    </form>
                    <BackgroundDetails
                        name={background}
                        profile={findByName(options.backgrounds, background)}
                    />
                </section>
                <LanguagePicker slots={languageSlots} />
            </main>
            <StepActions
                className="background-actions"
                type="submit"
                form="background-form"
            />
        </CreationStep>
    );
}
