import StepActions from '../../components/characterCreation/StepActions.jsx';
import SpellPanel from '../../components/characterCreation/SpellPanel.jsx';
import { useCreationOptions } from '../../hooks/useCreationOptions.js';
import {
    classCantrips,
    classLevelOne,
    classLevelOneSpells,
    names,
} from '../../utils/characterCreation/catalog.js';
import CreationStep from '../../components/characterCreation/CreationStep.jsx';
import {
    knownSpells,
    spellChoiceLimits,
} from '../../utils/characterCreation/spellOptions.js';
import CharacterSidebar from '../../components/characterCreation/CharacterSidebar.jsx';
import { useCreationDraft } from '../../hooks/useCreationDraft.js';
import { useClassDraftArray } from '../../hooks/useDraftState.js';

export default function ChooseSpells() {
    const options = useCreationOptions();
    const { characterClass } = useCreationDraft();
    const { cantripLimit, spellLimit } = spellChoiceLimits(
        options,
        characterClass,
    );
    const cantripOptions = classCantrips(options, characterClass);
    const spellOptions = classLevelOneSpells(options, characterClass);
    const [cantrips, setCantrips] = useClassDraftArray(
        'cantrips',
        characterClass,
        (saved) => knownSpells(saved, names(cantripOptions)),
    );
    const [spells, setSpells] = useClassDraftArray(
        'spells',
        characterClass,
        (saved) => knownSpells(saved, names(spellOptions)),
    );
    const granted = names(classLevelOne(options, characterClass).spells);
    const levelOneSubtitle =
        granted.length > 0
            ? 'Déjà accordés par la classe : ' + granted.join(', ') + '.'
            : undefined;
    return (
        <CreationStep step="spells">
            <main className="spells-layout" aria-label="Choix des sorts">
                <CharacterSidebar
                    className="skills-summary spells-summary"
                    identityClassName="skills-identity"
                    heading="h1"
                    withAbilities
                />
                <SpellPanel
                    id="cantrips"
                    title={'Choisissez ' + cantripLimit + ' sorts mineurs'}
                    spells={cantripOptions}
                    selected={cantrips}
                    limit={cantripLimit}
                    onChange={setCantrips}
                />
                <SpellPanel
                    id="spells"
                    title={'Choisissez ' + spellLimit + ' sorts de niveau 1'}
                    subtitle={levelOneSubtitle}
                    spells={spellOptions}
                    selected={spells}
                    limit={spellLimit}
                    onChange={setSpells}
                />
            </main>
            <StepActions className="skills-actions spells-actions" />
        </CreationStep>
    );
}
